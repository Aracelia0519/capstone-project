<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;
use App\Events\ProcurementRequestUpdated;
use App\Models\Supplier\ProcurementFulfillment;

class ProcurementReadyController extends Controller
{
    /**
     * Check RBAC Permissions for Procurement Processing Module (Level-Based)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        // Admin
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        // Distributor
        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        // HR Manager
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        }

        // Operational Distributor
        if ($user->role === 'operational_distributor') {
            $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            if ($opDist) {
                return [
                    'has_access' => true,
                    'distributor_id' => $opDist->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        }

        // Employee Level-Based Verification
        if ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'procurement_ready') // Module-specific permission key
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_manage' => (bool)$access->can_manage,
                                    'can_approve' => (bool)$access->can_approve,
                                ]
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'has_access' => false,
            'distributor_id' => null,
            'permissions' => ['can_view' => false, 'can_manage' => false, 'can_approve' => false]
        ];
    }

    /**
     * Get all procurements ready for processing
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Removed the invalid 'fulfillment' from the eloquent builder relationship array.
        $query = ProcurementRequest::with(['requester', 'distributor', 'selectedSupplier']);

        if ($user->role !== 'admin') {
            $query->where('distributor_id', $accessData['distributor_id']);
        }

        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'op-approved', 'shipped', 'delivered', 'completed']);
        }

        $procurements = $query->orderBy('updated_at', 'desc')->paginate($request->get('per_page', 15));

        // Fetch and append the fulfillment data safely
        $fulfillments = ProcurementFulfillment::whereIn('procurement_request_id', $procurements->pluck('id'))->get()->keyBy('procurement_request_id');
        
        $procurements->getCollection()->transform(function ($req) use ($fulfillments) {
            $req->fulfillment = $fulfillments->get($req->id);
            return $req;
        });

        return response()->json([
            'data' => $procurements,
            'permissions' => $accessData['permissions'],
            'distributor_id' => $accessData['distributor_id'] 
        ]);
    }

    /**
     * Get specific procurement details
     */
    public function show($id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $procurement = ProcurementRequest::with([
            'requester', 
            'distributor', 
            'selectedSupplier'
        ])->findOrFail($id);

        if ($user->role !== 'admin' && $procurement->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['message' => 'Unauthorized to view this request'], 403);
        }

        // Manually append the fulfillment with attachments
        $procurement->fulfillment = ProcurementFulfillment::with('attachments')->where('procurement_request_id', $procurement->id)->first();

        return response()->json(['data' => $procurement]);
    }

    /**
     * Mark request as operationally approved (Accepted for logistics)
     */
    public function markAsOpApproved(Request $request, $id) 
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_approve');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to approve procurement requests.'], 403);
        }

        $procurement = ProcurementRequest::findOrFail($id);

        if ($user->role !== 'admin' && $procurement->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['message' => 'Unauthorized to update this request'], 403);
        }

        if ($procurement->status !== 'approved') {
            return response()->json(['message' => 'Only finance approved requests can be operationally approved.'], 400);
        }

        $procurement->updateStatus('op-approved');
        
        event(new ProcurementRequestUpdated($procurement->distributor_id));

        return response()->json([
            'message' => 'Order marked as Op. Approved',
            'data' => $procurement
        ]);
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_approve');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to reject procurement requests.'], 403);
        }

        $request->validate([
            'reason' => 'required|string'
        ]);

        $procurement = ProcurementRequest::findOrFail($id);

        if ($user->role !== 'admin' && $procurement->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['message' => 'Unauthorized to update this request'], 403);
        }

        $procurement->updateStatus('rejected', $request->reason);

        event(new ProcurementRequestUpdated($procurement->distributor_id));

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}