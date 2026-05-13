<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User; 
use App\Events\Partnership\PartnershipRequestUpdated;

class PartnerRequestController extends Controller
{
    /**
     * Get all pending internal partnership requests for the distributor.
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user || !$user->isDistributor()) {
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

    /**
     * Approve an internal request, apply signature, and forward to supplier.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

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

            $partnershipRequest->update([
                'status' => 'pending_supplier',
                'distributor_approved_at' => now(),
                'distributor_signed_at' => now(),
                'distributor_signature_path' => $fileName,
            ]);

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

            // FIX: Convert to array to bypass Eloquent queue stripping custom variables
            $payload = $broadcastRequest->toArray();
            $payload['agreement_url'] = $broadcastRequest->agreement_path ? url('storage/' . $broadcastRequest->agreement_path) : null;
            $payload['distributor_signature_url'] = $broadcastRequest->distributor_signature_path ? url('storage/' . $broadcastRequest->distributor_signature_path) : null;

            if ($broadcastRequest->distributor) {
                $distReq = DB::table('distributor_requirements')->where('user_id', $broadcastRequest->distributor_id)->first();
                if ($distReq) {
                    $payload['distributor']['company_name'] = $distReq->company_name;
                }
            }

            // Convert back to standard object for the Event
            $eventPayload = json_decode(json_encode($payload));
            broadcast(new PartnershipRequestUpdated($eventPayload))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Request approved and signed. It has been sent to the supplier.',
                'data' => $partnershipRequest
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject an internal request.
     */
    public function reject(Request $request, $id)
    {
        try {
            /** @var \App\Models\User $user */
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

            // FIX: Convert to array to bypass Eloquent queue stripping custom variables
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
}