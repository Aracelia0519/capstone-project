<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use App\Models\User; 
use App\Models\UserReport; // <--- ADDED IMPORT
use App\Events\Partnership\SupplierRequestUpdated;
use App\Models\PartnershipMessage;
use App\Events\PartnershipMessageSent;

class DistributorRequestController extends Controller
{
    private function resolveSupplierId($user)
    {
        $supplierId = $user->id;
        $personnelOfficer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
        if ($personnelOfficer) {
            $supplierId = $personnelOfficer->supplier_id;
        }
        return $supplierId;
    }

    private function formatRequestForBroadcast($id)
    {
        $req = DistributorSupplier::with([
            'distributor' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'email', 'phone');
            }
        ])->find($id);

        if (!$req) return null;

        $payload = $req->toArray();
        $payload['agreement_url'] = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
        $payload['distributor_signature_url'] = $req->distributor_signature_path ? url('storage/' . $req->distributor_signature_path) : null;
        $payload['supplier_signature_url'] = $req->supplier_signature_path ? url('storage/' . $req->supplier_signature_path) : null;
        
        $terms = $req->negotiated_terms;
        if (is_string($terms)) { $terms = json_decode($terms, true); }
        if (is_string($terms)) { $terms = json_decode($terms, true); }
        $payload['negotiated_terms'] = is_array($terms) ? $terms : [];

        $distributorReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
        if ($distributorReq && isset($payload['distributor'])) {
            $payload['distributor']['company_name'] = $distributorReq->company_name;
        }

        return json_decode(json_encode($payload));
    }

    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthorized access.'], 403);

            $supplierId = $this->resolveSupplierId($user);

            $requests = DistributorSupplier::where('supplier_id', $supplierId)
                ->whereIn('status', ['pending_supplier', 'active', 'terminated', 'disconnected'])
                ->with([
                    'distributor' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone'); }
                ])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $req->agreement_url = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
                    $req->distributor_signature_url = $req->distributor_signature_path ? url('storage/' . $req->distributor_signature_path) : null;
                    $req->supplier_signature_url = $req->supplier_signature_path ? url('storage/' . $req->supplier_signature_path) : null;
                    
                    $terms = $req->negotiated_terms;
                    if (is_string($terms)) { $terms = json_decode($terms, true); }
                    if (is_string($terms)) { $terms = json_decode($terms, true); }
                    $req->negotiated_terms = is_array($terms) ? $terms : [];

                    $distributorReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
                    if ($distributorReq && $req->distributor) {
                        $req->distributor->company_name = $distributorReq->company_name;
                    }

                    return $req;
                });

            return response()->json([
                'success' => true,
                'supplier_id' => $supplierId, 
                'data' => $requests
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch requests: ' . $e->getMessage()], 500);
        }
    }

    public function proposeDate(Request $request, int $id)
    {
        $request->validate([
            'proposed_date' => 'required|date|after:+1 month',
            'negotiated_terms' => 'nullable|array'
        ]);

        try {
            if (!Schema::hasColumn('distributor_suppliers', 'negotiated_terms')) {
                Schema::table('distributor_suppliers', function ($table) {
                    $table->json('negotiated_terms')->nullable();
                });
            }

            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->firstOrFail();

            $distRequest->proposed_end_date = $request->proposed_date;
            $distRequest->negotiated_terms = json_encode($request->negotiated_terms ?? []);
            $distRequest->last_proposed_by = 'supplier';
            $distRequest->save();

            $this->injectEndDateAndTermsIntoAgreement($distRequest->agreement_path, $request->proposed_date, $request->negotiated_terms ?? []);

            broadcast(new SupplierRequestUpdated($this->formatRequestForBroadcast($distRequest->id)))->toOthers();

            return response()->json(['success' => true, 'message' => 'Changes proposed.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Proposal failed: ' . $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['signature_image' => 'required|string']);

        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthorized access.'], 403);

            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with(['distributor', 'creator'])
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) return response()->json(['message' => 'Request not found or already processed.'], 404);

            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'agreements/signatures/supplier_' . $supplierId . '_distributor_' . $distRequest->distributor_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            if ($distRequest->proposed_end_date) {
                $distRequest->contract_end_date = $distRequest->proposed_end_date;
                $distRequest->proposed_end_date = null;
            }

            $distRequest->status = 'active';
            $distRequest->supplier_approved_at = now();
            $distRequest->supplier_signed_at = now();
            $distRequest->supplier_signature_path = $fileName;
            $distRequest->save();

            $terms = $distRequest->negotiated_terms;
            if (is_string($terms)) { $terms = json_decode($terms, true); }
            if (is_string($terms)) { $terms = json_decode($terms, true); }
            $termsArray = is_array($terms) ? $terms : [];
            
            $this->injectEndDateAndTermsIntoAgreement($distRequest->agreement_path, $distRequest->contract_end_date, $termsArray);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'active']);
            } else {
                SupplierPartner::create([
                    'distributor_id' => $distRequest->distributor_id,
                    'supplier_id' => $supplierId,
                    'status' => 'active',
                    'partnership_start_date' => now(),
                    'total_orders' => 0,
                    'total_sales' => 0,
                ]);
            }

            broadcast(new SupplierRequestUpdated($this->formatRequestForBroadcast($distRequest->id)))->toOthers();

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $creatorEmail = $distRequest->creator->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px;'>Partnership Request Approved</h2>
                    <p>Hello,</p>
                    <p>Great news! Your partnership proposal to <strong>{$supplierName}</strong> has been officially approved and signed.</p>
                    <p>You can now access their full product catalog and initiate procurement requests directly through the CaviteGoPaint system.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $creatorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    if ($creatorEmail && $creatorEmail !== $distributorEmail) $message->cc($creatorEmail);
                    $message->subject("Partnership Request Approved - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request approved, signed, and activated. Email sent.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with(['distributor', 'creator'])
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) return response()->json(['message' => 'Request not found.'], 404);

            $reason = $request->input('reason', 'No specific reason provided by the supplier.');

            $distRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();
                
            if ($supplierPartner) $supplierPartner->update(['status' => 'rejected']);

            broadcast(new SupplierRequestUpdated($this->formatRequestForBroadcast($distRequest->id)))->toOthers();

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $creatorEmail = $distRequest->creator->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px;'>Partnership Request Declined</h2>
                    <p>Hello,</p>
                    <p>We regret to inform you that your partnership proposal to <strong>{$supplierName}</strong> has been officially declined.</p>
                    <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Reason provided by the supplier:</strong><br><br> <em>{$reason}</em></p>
                    </div>
                    <p>You can review this status update in your internal Partnership Approvals dashboard on the CaviteGoPaint system.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $creatorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    if ($creatorEmail && $creatorEmail !== $distributorEmail) $message->cc($creatorEmail);
                    $message->subject("Partnership Request Declined - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request declined and email notification sent.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }

    private function getSupplierName($user, $supplierId)
    {
        $supplierReqs = DB::table('supplier_requirements')->where('user_id', $supplierId)->first();
        return $supplierReqs ? $supplierReqs->company_name : ($user->full_name ?? 'The Supplier');
    }

    private function setupSMTPConfig()
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.mailers.smtp.username' => 'IspyMILK@gmail.com',
            'mail.mailers.smtp.password' => 'tdgtfzwlnrntrkmi',
            'mail.from.address' => 'IspyMILK@gmail.com',
            'mail.from.name' => 'CaviteGoPaint',
        ]);
    }

    public function getChatMessages($distributorId)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

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

    public function sendChatMessage(Request $request, $distributorId)
    {
        $request->validate(['message' => 'required|string']);

        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $message = PartnershipMessage::create([
                'sender_id' => $supplierId,
                'receiver_id' => $distributorId,
                'message' => $request->message,
                'is_read' => false
            ]);

            broadcast(new PartnershipMessageSent($message))->toOthers();

            return response()->json(['success' => true, 'data' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function injectEndDateAndTermsIntoAgreement($agreementPath, $endDate, $termsArray) 
    {
        if (!$agreementPath) return;
        
        if (Storage::disk('public')->exists($agreementPath)) {
            $html = Storage::disk('public')->get($agreementPath);
            
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

    // --- NEW: Submit a Report Against a Distributor ---
    public function submitReport(Request $request, $userId)
    {
        try {
            $user = Auth::user();

            if (!in_array($user->role, ['supplier', 'supplier_employee', 'personnel_officer'])) {
                return response()->json(['message' => 'Unauthorized. Only supplier personnel can submit this report.'], 403);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
                'description' => 'required|string',
                'incident_date' => 'required|date',
                'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120'
            ]);

            $supplierId = $this->resolveSupplierId($user);

            // Enforce limit: Maximum 3 reports per day for this supplier
            $reportsToday = UserReport::where('reported_by_id', $supplierId)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($reportsToday >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached the maximum limit of 3 reports per day.'
                ], 429);
            }

            $reportedUser = \App\Models\User::find($userId);
            if (!$reportedUser || !in_array($reportedUser->role, ['distributor', 'operational_distributor'])) {
                return response()->json(['success' => false, 'message' => 'Distributor not found.'], 404);
            }

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('reports/evidence', 'public');
            }

            UserReport::create([
                'reported_user_id' => $reportedUser->id,
                'reported_by_id' => $supplierId,
                'reporter_role' => 'supplier',
                'reported_user_role' => 'distributor',
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