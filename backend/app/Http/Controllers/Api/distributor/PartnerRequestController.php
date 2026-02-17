<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\DistributorSupplier;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import the User model

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
                ->get();

            // Transform data 
            $formattedRequests = $requests->map(function ($item) {
                $supplierReq = $item->supplier->supplierRequirements;
                
                return [
                    'id' => $item->id,
                    'supplier_name' => $supplierReq ? $supplierReq->company_name : $item->supplier->full_name,
                    'supplier_email' => $item->supplier->email,
                    'requested_by' => $item->creator ? $item->creator->full_name : 'Unknown',
                    'requested_by_role' => $item->creator ? ucfirst(str_replace('_', ' ', $item->creator->role)) : 'N/A',
                    'message' => $item->request_message ?? 'No message provided',
                    'date' => $item->created_at->format('M d, Y h:i A'),
                    'status' => $item->status,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRequests
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch requests: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve an internal request.
     */
    public function approve($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $request = DistributorSupplier::where('id', $id)
                ->where('distributor_id', $user->id)
                ->where('status', 'pending_internal')
                ->first();

            if (!$request) {
                return response()->json(['message' => 'Request not found or already processed.'], 404);
            }

            $request->update([
                'status' => 'pending_supplier',
                'distributor_approved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request approved. It has been sent to the supplier for confirmation.',
                'data' => $request
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