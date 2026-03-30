<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User; 

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

            // validation check
            if (!$user || !$user->isDistributor()) {
                return response()->json(['message' => 'Unauthorized. Only Distributors can manage internal requests.'], 403);
            }

            // Fetch requests where status is 'pending_internal' for this distributor
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
                    // Send the agreement document URL to the frontend so it can be viewed before signing
                    $req->agreement_url = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
                    return $req;
                });

            return response()->json([
                'success' => true,
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

            // Process Base64 Signature Image
            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            
            if (count($imageParts) !== 2) {
                return response()->json(['success' => false, 'message' => 'Invalid signature format.'], 400);
            }

            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            // Save the image to storage
            $fileName = 'agreements/signatures/distributor_' . $user->id . '_supplier_' . $partnershipRequest->supplier_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            // Update status and attach the signature to the document
            $partnershipRequest->update([
                'status' => 'pending_supplier',
                'distributor_approved_at' => now(),
                'distributor_signed_at' => now(),
                'distributor_signature_path' => $fileName,
            ]);

            // Ensure the SupplierPartner record exists so it reflects in the system as 'Waiting on Supplier'
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

            // If it already existed but had a different status, update it to sync
            if (!$supplierPartner->wasRecentlyCreated && $supplierPartner->status !== 'pending_supplier') {
                $supplierPartner->update(['status' => 'pending_supplier']);
            }

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

            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }
}