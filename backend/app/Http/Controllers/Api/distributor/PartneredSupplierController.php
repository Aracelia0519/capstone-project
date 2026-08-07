<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Supplier\SupplierPartner;
use App\Models\User; 
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\UserReport; // <--- ADDED IMPORT
use App\Events\Partnership\PartnershipRequestUpdated;
use App\Models\PartnershipMessage;
use App\Events\PartnershipMessageSent;

class PartneredSupplierController extends Controller
{
    private function formatRequestForBroadcast(int $distributorId, int $supplierId)
    {
        $req = DistributorSupplier::with([
            'supplier' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone'); },
            'supplier.supplierRequirements',
            'distributor' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone', 'role'); },
            'creator' => function ($q) { $q->select('id', 'first_name', 'last_name', 'role'); }
        ])->where('distributor_id', $distributorId)->where('supplier_id', $supplierId)->first();
        
        if (!$req) return null;
        
        $payload = $req->toArray();
        $payload['agreement_url'] = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
        $payload['distributor_signature_url'] = $req->distributor_signature_path ? url('storage/' . $req->distributor_signature_path) : null;
        $payload['supplier_signature_url'] = $req->supplier_signature_path ? url('storage/' . $req->supplier_signature_path) : null;
        $payload['negotiated_terms'] = $req->negotiated_terms ? json_decode($req->negotiated_terms) : [];

        if ($req->distributor) {
            $distReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
            if ($distReq) $payload['distributor']['company_name'] = $distReq->company_name;
        }

        return json_decode(json_encode($payload));
    }

    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'distributor') {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $partnerships = SupplierPartner::where('distributor_id', $user->id)
                ->whereIn('status', ['pending_supplier', 'active'])
                ->with([
                    'supplier', 
                    'supplier.supplierRequirements',
                    'supplier.supplierRequirements.address'
                ])
                ->get();

            $data = $partnerships->map(function ($partner) use ($user) {
                $supplier = $partner->supplier;
                $req = $supplier->supplierRequirements;
                $address = $req ? $req->address : null;

                $fullAddress = 'Address not available';
                $locationShort = 'N/A';
                
                if ($address) {
                    $fullAddress = "{$address->block_address}, {$address->barangay}, {$address->city}, {$address->province}";
                    $locationShort = "{$address->city}, {$address->province}";
                }

                $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                    ->where('supplier_id', $supplier->id)
                    ->first();
                
                $agreementUrl = $distSupplier && $distSupplier->agreement_path 
                    ? url('storage/' . $distSupplier->agreement_path) 
                    : null;

                $distributorSignatureUrl = $distSupplier && $distSupplier->distributor_signature_path 
                    ? url('storage/' . $distSupplier->distributor_signature_path) 
                    : null;
                
                $supplierSignatureUrl = $distSupplier && $distSupplier->supplier_signature_path 
                    ? url('storage/' . $distSupplier->supplier_signature_path) 
                    : null;
                    
                $isSigned = $distSupplier && $distSupplier->distributor_signed_at !== null;
                $isSupplierSigned = $distSupplier && $distSupplier->supplier_signed_at !== null;
                
                $signedAt = $distSupplier && $distSupplier->distributor_signed_at 
                    ? $distSupplier->distributor_signed_at->format('M d, Y h:i A') 
                    : null;

                $supplierSignedAt = $distSupplier && $distSupplier->supplier_signed_at 
                    ? $distSupplier->supplier_signed_at->format('M d, Y h:i A') 
                    : null;

                return [
                    'id' => $partner->id, 
                    'supplier_user_id' => $supplier->id,
                    'company_name' => $req ? $req->company_name : $supplier->full_name,
                    'contact_person' => $supplier->full_name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone ?? 'N/A',
                    'category' => 'General Supply', 
                    'rating' => 5.0, 
                    'fulfillment_rate' => 100, 
                    'location' => $locationShort,
                    'address' => $fullAddress,
                    'status' => $partner->status, 
                    'total_orders' => $partner->total_orders,
                    'total_spend' => $partner->total_sales, 
                    'on_time_rate' => 95, 
                    'id_number' => $req ? $req->id_number : 'N/A',
                    'partnership_date' => $partner->partnership_start_date->format('M d, Y'),
                    
                    'agreement_url' => $agreementUrl,
                    'is_signed' => $isSigned,
                    'is_supplier_signed' => $isSupplierSigned,
                    'signed_at' => $signedAt,
                    'supplier_signed_at' => $supplierSignedAt,
                    'distributor_signature_url' => $distributorSignatureUrl,
                    'supplier_signature_url' => $supplierSignatureUrl,
                    'distributor_supplier_id' => $distSupplier ? $distSupplier->id : null,

                    'contract_end_date' => $distSupplier ? $distSupplier->contract_end_date : null,
                    'proposed_end_date' => $distSupplier ? $distSupplier->proposed_end_date : null,
                    'last_proposed_by' => $distSupplier ? $distSupplier->last_proposed_by : null,
                    'negotiated_terms' => ($distSupplier && $distSupplier->negotiated_terms) ? json_decode($distSupplier->negotiated_terms) : [],
                ];
            });

            return response()->json([
                'success' => true,
                'distributor_id' => $user->id,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch partners: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAgreementRaw(int $id)
    {
        try {
            $user = Auth::user();
            
            $partner = SupplierPartner::where('id', $id)
                ->where('distributor_id', $user->id)
                ->firstOrFail();
            
            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            if (!$distSupplier || !$distSupplier->agreement_path) {
                return response()->json(['success' => false, 'message' => 'Agreement document not found.'], 404);
            }

            $htmlContent = Storage::disk('public')->get($distSupplier->agreement_path);

            return response()->json([
                'success' => true,
                'html' => $htmlContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agreement content: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proposeDate(Request $request, int $id)
    {
        // This endpoint was kept as /propose-date but now handles Date + Terms
        $request->validate([
            'proposed_date' => 'required|date|after:+1 month',
            'negotiated_terms' => 'nullable|array'
        ]);

        try {
            // Check schema to gracefully add column if it hits here first
            if (!Schema::hasColumn('distributor_suppliers', 'negotiated_terms')) {
                Schema::table('distributor_suppliers', function ($table) {
                    $table->json('negotiated_terms')->nullable();
                });
            }

            $partner = SupplierPartner::where('id', $id)->where('distributor_id', Auth::id())->firstOrFail();
            $distSupplier = DistributorSupplier::where('distributor_id', Auth::id())->where('supplier_id', $partner->supplier_id)->firstOrFail();
            
            $distSupplier->proposed_end_date = $request->proposed_date;
            $distSupplier->negotiated_terms = json_encode($request->negotiated_terms ?? []);
            $distSupplier->last_proposed_by = 'distributor';
            $distSupplier->save();

            // Inject the new date and terms into the agreement immediately so both can view it
            $this->injectEndDateAndTermsIntoAgreement($distSupplier->agreement_path, $request->proposed_date, $request->negotiated_terms ?? []);

            broadcast(new PartnershipRequestUpdated($this->formatRequestForBroadcast(Auth::id(), $partner->supplier_id)))->toOthers();

            return response()->json(['success' => true, 'message' => 'Proposal sent.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function acceptProposedDate(Request $request, int $id)
    {
        try {
            $partner = SupplierPartner::where('id', $id)->where('distributor_id', Auth::id())->firstOrFail();
            $distSupplier = DistributorSupplier::where('distributor_id', Auth::id())->where('supplier_id', $partner->supplier_id)->firstOrFail();
            
            $distSupplier->contract_end_date = $distSupplier->proposed_end_date;
            $distSupplier->proposed_end_date = null;
            $distSupplier->last_proposed_by = 'distributor';
            $distSupplier->save();

            // Inject the Contract End Date & Terms permanently into the HTML file
            $termsArray = $distSupplier->negotiated_terms ? json_decode($distSupplier->negotiated_terms, true) : [];
            $this->injectEndDateAndTermsIntoAgreement($distSupplier->agreement_path, $distSupplier->contract_end_date, $termsArray);

            broadcast(new PartnershipRequestUpdated($this->formatRequestForBroadcast(Auth::id(), $partner->supplier_id)))->toOthers();

            return response()->json(['success' => true, 'message' => 'Date and terms accepted. Waiting for supplier signature.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function renewContract(Request $request, int $id)
    {
        $request->validate(['contract_end_date' => 'required|date|after:+1 month']);

        try {
            $partner = SupplierPartner::where('id', $id)->where('distributor_id', Auth::id())->firstOrFail();
            $distSupplier = DistributorSupplier::where('distributor_id', Auth::id())->where('supplier_id', $partner->supplier_id)->firstOrFail();
            
            $distSupplier->contract_end_date = $request->contract_end_date;
            $distSupplier->proposed_end_date = null;
            $distSupplier->last_proposed_by = 'distributor';
            
            // Require supplier to sign again for renewal
            $distSupplier->supplier_signature_path = null;
            $distSupplier->supplier_signed_at = null;
            $distSupplier->status = 'pending_supplier';
            $distSupplier->save();

            $partner->status = 'pending_supplier';
            $partner->save();

            // Inject the Contract End Date permanently into the HTML file
            $termsArray = $distSupplier->negotiated_terms ? json_decode($distSupplier->negotiated_terms, true) : [];
            $this->injectEndDateAndTermsIntoAgreement($distSupplier->agreement_path, $distSupplier->contract_end_date, $termsArray);

            broadcast(new PartnershipRequestUpdated($this->formatRequestForBroadcast(Auth::id(), $partner->supplier_id)))->toOthers();

            return response()->json(['success' => true, 'message' => 'Contract renewal initiated. Pending supplier review and signature.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function getChatMessages(int $supplierId)
    {
        try {
            $user = Auth::user();
            $distributorId = $user->id;

            $messages = PartnershipMessage::where(function($q) use ($distributorId, $supplierId) {
                $q->where('sender_id', $distributorId)->where('receiver_id', $supplierId);
            })->orWhere(function($q) use ($distributorId, $supplierId) {
                $q->where('sender_id', $supplierId)->where('receiver_id', $distributorId);
            })->orderBy('created_at', 'asc')->get();

            return response()->json(['success' => true, 'data' => $messages]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendChatMessage(Request $request, int $supplierId)
    {
        $request->validate(['message' => 'required|string']);

        try {
            $user = Auth::user();
            $distributorId = $user->id;

            $message = PartnershipMessage::create([
                'sender_id' => $distributorId,
                'receiver_id' => $supplierId,
                'message' => $request->message,
                'is_read' => false
            ]);

            broadcast(new PartnershipMessageSent($message))->toOthers();

            return response()->json(['success' => true, 'data' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Inject Contract End Date and Terms into the physical HTML document.
     */
    private function injectEndDateAndTermsIntoAgreement($agreementPath, $endDate, $termsArray) 
    {
        if (!$agreementPath) return;
        
        if (Storage::disk('public')->exists($agreementPath)) {
            $html = Storage::disk('public')->get($agreementPath);
            
            // Handle Date Block
            if ($endDate) {
                $formattedDate = \Carbon\Carbon::parse($endDate)->format('F d, Y');
                $html = preg_replace('/<div id=[\'"]contract-end-date-block[\'"].*?<\/div>/is', '', $html);
                $endDateHtml = "<div id='contract-end-date-block' style='margin-top: 30px; padding: 15px; background-color: #f8fafc; border-left: 4px solid #3b82f6; font-family: Arial, sans-serif; border-radius: 6px;'><p style='margin: 0; font-size: 16px; color: #1e293b;'><strong>Agreed Contract Expiration Date:</strong> {$formattedDate}</p></div>";
                if (strpos($html, '</body>') !== false) {
                    $html = str_replace('</body>', $endDateHtml . '</body>', $html);
                } else {
                    $html .= $endDateHtml;
                }
            }
            
            // Handle Negotiated Terms Block
            $html = preg_replace('/<div id=[\'"]negotiated-terms-block[\'"].*?<\/div>/is', '', $html);
            if (!empty($termsArray)) {
                $termsList = '';
                foreach($termsArray as $term) {
                    $termsList .= "<li style='margin-bottom: 8px;'>{$term}</li>";
                }
                $termsHtml = "<div id='negotiated-terms-block' style='margin-top: 20px; padding: 15px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-family: Arial, sans-serif; border-radius: 6px;'><h3 style='margin-top: 0; color: #0f172a; font-size: 16px;'>Additional Terms & Conditions</h3><ul style='color: #334155; font-size: 14px; padding-left: 20px; margin-bottom: 0;'>{$termsList}</ul></div>";
                
                if (strpos($html, '</body>') !== false) {
                    $html = str_replace('</body>', $termsHtml . '</body>', $html);
                } else {
                    $html .= $termsHtml;
                }
            }
            
            Storage::disk('public')->put($agreementPath, $html);
        }
    }

    // --- NEW: Submit a Report Against a Supplier ---
    public function submitReport(Request $request, $userId)
    {
        try {
            $user = Auth::user();
            if ($user->role !== 'distributor') {
                return response()->json(['message' => 'Unauthorized. Only distributors can submit this report.'], 403);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
                'description' => 'required|string',
                'incident_date' => 'required|date',
                'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120'
            ]);

            // Enforce limit: Maximum 3 reports per day for this distributor
            $reportsToday = UserReport::where('reported_by_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($reportsToday >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached the maximum limit of 3 reports per day.'
                ], 429);
            }

            $reportedUser = \App\Models\User::find($userId);
            if (!$reportedUser || $reportedUser->role !== 'supplier') {
                return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
            }

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('reports/evidence', 'public');
            }

            UserReport::create([
                'reported_user_id' => $reportedUser->id,
                'reported_by_id' => $user->id,
                'reporter_role' => 'distributor',
                'reported_user_role' => 'supplier',
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'incident_date' => $validated['incident_date'],
                'evidence_path' => $evidencePath,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Report submitted successfully. Administrators will review the incident.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to submit report', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}