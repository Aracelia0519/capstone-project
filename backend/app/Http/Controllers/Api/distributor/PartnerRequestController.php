<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User; 
use App\Events\Partnership\PartnershipRequestUpdated;

class PartnerRequestController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'distributor') {
                return response()->json(['message' => 'Unauthorized. Only Distributors can manage internal requests.'], 403);
            }

            $requests = DistributorSupplier::where('distributor_id', $user->id)
                ->where('status', 'pending_internal')
                ->with([
                    'supplier' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'email', 'phone');
                    },
                    'supplier.supplierRequirements', 
                    'creator' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'role');
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $req->agreement_url = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
                    return $req;
                });

            return response()->json([
                'success' => true,
                'distributor_id' => $user->id, 
                'data' => $requests
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch requests: ' . $e->getMessage()], 500);
        }
    }

    public function approve(Request $request, int $id)
    {
        $request->validate([
            'signature_image' => 'required|string',
            'contract_end_date' => 'required|date|after:+1 month'
        ]);

        try {
            $user = Auth::user();

            if (!Schema::hasColumn('distributor_suppliers', 'contract_end_date')) {
                Schema::table('distributor_suppliers', function ($table) {
                    $table->date('contract_end_date')->nullable();
                    $table->date('proposed_end_date')->nullable();
                    $table->string('last_proposed_by')->nullable();
                });
            }

            $partnershipRequest = DistributorSupplier::where('id', $id)
                ->where('distributor_id', $user->id)
                ->where('status', 'pending_internal')
                ->first();

            if (!$partnershipRequest) {
                return response()->json(['message' => 'Request not found or already processed.'], 404);
            }

            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            
            if (count($imageParts) !== 2) {
                return response()->json(['success' => false, 'message' => 'Invalid signature format.'], 400);
            }

            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'agreements/signatures/distributor_' . $user->id . '_supplier_' . $partnershipRequest->supplier_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            $partnershipRequest->status = 'pending_supplier';
            $partnershipRequest->distributor_approved_at = now();
            $partnershipRequest->distributor_signed_at = now();
            $partnershipRequest->distributor_signature_path = $fileName;
            $partnershipRequest->contract_end_date = $request->contract_end_date;
            $partnershipRequest->last_proposed_by = 'distributor';
            $partnershipRequest->save();

            $this->injectEndDateIntoAgreement($partnershipRequest->agreement_path, $partnershipRequest->contract_end_date);

            $supplierPartner = SupplierPartner::firstOrCreate(
                [
                    'distributor_id' => $user->id,
                    'supplier_id' => $partnershipRequest->supplier_id
                ],
                [
                    'status' => 'pending_supplier',
                    'partnership_start_date' => now(),
                    'total_orders' => 0,
                    'total_sales' => 0,
                ]
            );

            if (!$supplierPartner->wasRecentlyCreated && $supplierPartner->status !== 'pending_supplier') {
                $supplierPartner->update(['status' => 'pending_supplier']);
            }

            $broadcastRequest = DistributorSupplier::with([
                'supplier' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone'); },
                'supplier.supplierRequirements',
                'distributor' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone', 'role'); },
                'creator' => function ($q) { $q->select('id', 'first_name', 'last_name', 'role'); }
            ])->find($partnershipRequest->id);

            $payload = $broadcastRequest->toArray();
            $payload['agreement_url'] = $broadcastRequest->agreement_path ? url('storage/' . $broadcastRequest->agreement_path) : null;
            $payload['distributor_signature_url'] = $broadcastRequest->distributor_signature_path ? url('storage/' . $broadcastRequest->distributor_signature_path) : null;

            if ($broadcastRequest->distributor) {
                $distReq = DB::table('distributor_requirements')->where('user_id', $broadcastRequest->distributor_id)->first();
                if ($distReq) {
                    $payload['distributor']['company_name'] = $distReq->company_name;
                }
            }

            $eventPayload = json_decode(json_encode($payload));
            broadcast(new PartnershipRequestUpdated($eventPayload))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Request approved, duration set, and sent to the supplier.',
                'data' => $partnershipRequest
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, int $id)
    {
        try {
            $user = Auth::user();

            $partnershipRequest = DistributorSupplier::where('id', $id)
                ->where('distributor_id', $user->id)
                ->where('status', 'pending_internal')
                ->first();

            if (!$partnershipRequest) {
                return response()->json(['message' => 'Request not found or already processed.'], 404);
            }

            $partnershipRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->input('reason', 'Rejected by Distributor Owner'),
            ]);

            $broadcastRequest = DistributorSupplier::with([
                'supplier' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone'); },
                'supplier.supplierRequirements',
                'distributor' => function ($q) { $q->select('id', 'first_name', 'last_name', 'email', 'phone', 'role'); },
                'creator' => function ($q) { $q->select('id', 'first_name', 'last_name', 'role'); }
            ])->find($partnershipRequest->id);

            $payload = $broadcastRequest->toArray();
            
            if ($broadcastRequest->distributor) {
                $distReq = DB::table('distributor_requirements')->where('user_id', $broadcastRequest->distributor_id)->first();
                if ($distReq) {
                    $payload['distributor']['company_name'] = $distReq->company_name;
                }
            }

            $eventPayload = json_decode(json_encode($payload));
            broadcast(new PartnershipRequestUpdated($eventPayload))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }

    private function injectEndDateIntoAgreement($agreementPath, $endDate) 
    {
        if (!$agreementPath || !$endDate) return;
        
        if (Storage::disk('public')->exists($agreementPath)) {
            $html = Storage::disk('public')->get($agreementPath);
            $formattedDate = \Carbon\Carbon::parse($endDate)->format('F d, Y');
            
            $html = preg_replace('/<div id=[\'"]contract-end-date-block[\'"].*?<\/div>/is', '', $html);
            
            $endDateHtml = "<div id='contract-end-date-block' style='margin-top: 30px; padding: 15px; background-color: #f8fafc; border-left: 4px solid #3b82f6; font-family: Arial, sans-serif; border-radius: 6px;'><p style='margin: 0; font-size: 16px; color: #1e293b;'><strong>Agreed Contract Expiration Date:</strong> {$formattedDate}</p></div>";
            
            if (strpos($html, '</body>') !== false) {
                $html = str_replace('</body>', $endDateHtml . '</body>', $html);
            } else {
                $html .= $endDateHtml;
            }
            Storage::disk('public')->put($agreementPath, $html);
        }
    }
}