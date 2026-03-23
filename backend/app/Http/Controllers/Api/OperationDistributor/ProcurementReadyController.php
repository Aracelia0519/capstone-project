<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;

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
            if ($hrManager && $hrManager->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        } 
        
        // Operational Distributor
        elseif ($user->role === 'operational_distributor') {
            $opDist = $user->operationalDistributor; 
            if ($opDist && $opDist->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $opDist->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        }
        
        // Employee with specific RBAC
        elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'ec_process_procurement') // Permission key for this module
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
     * Fetch requests that are Approved by Finance, or further along in the pipeline.
     */
    public function index()
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to view procurement fulfillments.'], 403);
        }

        $query = ProcurementRequest::with(['requester', 'product'])
            ->whereIn('status', [
                'approved', 
                'op-approved', 
                'd-approved', 
                'ready', 
                'processing', 
                'shipped', 
                'delivered', 
                'rejected'
            ])
            ->orderBy('updated_at', 'desc');

        if ($user->role !== 'admin') {
            $query->where('distributor_id', $accessData['distributor_id']);
        }

        $requests = $query->get();

        $formatted = $requests->map(function ($req) {
            $department = 'General';
            if ($req->requester && method_exists($req->requester, 'employee') && $req->requester->employee) {
                $department = $req->requester->employee->department;
            }

            return [
                'id' => $req->request_code ?? 'REQ-' . $req->id,
                'db_id' => $req->id,
                'department' => $department, 
                'requester' => $req->requester ? $req->requester->full_name : 'Unknown',
                'date' => $req->request_date->format('Y-m-d'),
                'location' => $req->delivery_address ?? 'Main Office',
                'status' => $req->status, // Will output exactly what is in DB
                'priority' => ucfirst($req->priority),
                'totalAmount' => (float) $req->total_cost,
                'courier' => $req->shipping_method,
                'items' => [
                    [
                        'name' => $req->product_name,
                        'quantity' => $req->quantity,
                        'unitPrice' => (float) $req->unit_price
                    ]
                ]
            ];
        });

        return response()->json([
            'data' => $formatted,
            'permissions' => $accessData['permissions']
        ]);
    }

    /**
     * Mark a request as Operationally Approved (Stored as 'op-approved' in DB)
     */
    public function markAsOpApproved(Request $request, $id)
    {
        $user = Auth::user();
        // Changed to explicit approve level
        $accessData = $this->checkAccess($user, 'can_approve');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to operationally approve procurement requests.'], 403);
        }

        $procurement = ProcurementRequest::findOrFail($id);

        if ($user->role !== 'admin' && $procurement->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['message' => 'Unauthorized to update this request'], 403);
        }

        if ($procurement->status !== 'approved') {
            return response()->json(['message' => 'Only finance approved requests can be operationally approved.'], 400);
        }

        // Updating directly to 'op-approved'
        $procurement->updateStatus('op-approved');
        
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
        // Changed to explicit approve level
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

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}