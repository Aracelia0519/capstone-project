<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use App\Models\User; // Import the User model

class DistributorRequestController extends Controller
{
    /**
     * Fetch all pending requests for the logged-in Supplier.
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // validation check
            if (!$user || !$user->isSupplier()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            // Fetch requests where supplier_id = current user AND status = pending_supplier
            $requests = DistributorSupplier::where('supplier_id', $user->id)
                ->where('status', 'pending_supplier')
                ->with([
                    'distributor', // User model of distributor
                    'distributor.distributorRequirement', // Business info
                    'distributor.distributorRequirement.address' // Location info
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Transform data for frontend
            $formattedData = $requests->map(function ($req) {
                $distReq = $req->distributor->distributorRequirement;
                $address = $distReq ? $distReq->address : null;

                // Format Address
                $locString = 'Location not set';
                if ($address) {
                    $locString = "{$address->city}, {$address->province}";
                }

                // Format Name (Company or Person)
                $companyName = $distReq ? $distReq->company_name : $req->distributor->full_name;
                $contactPerson = $req->distributor->full_name;

                return [
                    'id' => $req->id,
                    'distributor_id' => $req->distributor_id,
                    'company_name' => $companyName,
                    'contact_person' => $contactPerson,
                    'email' => $req->distributor->email,
                    'phone' => $req->distributor->phone ?? 'N/A',
                    'reg_number' => $distReq ? $distReq->business_registration_number : 'N/A',
                    'city' => $address ? $address->city : 'N/A',
                    'province' => $address ? $address->province : 'N/A',
                    'full_address' => $locString,
                    'date_requested' => $req->created_at->format('M d, Y'),
                    'message' => $req->request_message ?? 'No message provided.',
                    'status' => $req->status,
                    'distributor_req_id' => $distReq ? $distReq->id : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a partnership request.
     */
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // 1. Find the request
            $request = DistributorSupplier::where('id', $id)
                ->where('supplier_id', $user->id)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$request) {
                return response()->json(['message' => 'Request not found or already processed.'], 404);
            }

            // 2. Update Request Status to 'active'
            $request->update([
                'status' => 'active',
                'supplier_approved_at' => now(),
            ]);

            // 3. Create Official Partnership Record in new table
            SupplierPartner::create([
                'supplier_id' => $user->id,
                'distributor_id' => $request->distributor_id,
                'request_id' => $request->id,
                'status' => 'active',
                'partnership_start_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Partnership approved successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a partnership request.
     */
    public function reject(Request $request, $id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $distRequest = DistributorSupplier::where('id', $id)
                ->where('supplier_id', $user->id)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Request not found.'], 404);
            }

            // Update status to rejected
            $distRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->input('reason', 'Rejected by Supplier'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Partnership request declined.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rejection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}