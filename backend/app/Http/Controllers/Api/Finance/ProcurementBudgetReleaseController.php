<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Finance\BudgetDeductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementBudgetReleaseController extends Controller
{
    /**
     * Resolve the distributor ID based on the logged-in user's role.
     */
    private function getDistributorId($user)
    {
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $employee ? $employee->parent_distributor_id : null;
        } elseif (in_array($user->role, ['finance_manager', 'operational_distributor', 'hr_manager'])) {
            $tableName = $user->role . 's';
            $staff = DB::table($tableName)->where('user_id', $user->id)->first();
            return $staff ? $staff->parent_distributor_id : null;
        } elseif ($user->role === 'distributor') {
            return $user->id;
        }
        
        return null;
    }

    /**
     * Fetch RBAC permissions for the logged-in user.
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_budget_release')
            ->first();

        // Check if access row exists and is generally granted
        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_create' => (bool)$access->can_create,
            'can_update' => (bool)$access->can_update,
            'can_delete' => (bool)$access->can_delete,
        ];
    }

    /**
     * Fetch procurement requests that are 'd-approved' (Distributor Approved) or 'ready'
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_view']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to view budget releases.'
            ], 403);
        }

        $distributorId = $this->getDistributorId($user);

        // Fetches requests mapped to the authorized distributor
        $requests = ProcurementRequest::with(['requester', 'distributor'])
            ->where('distributor_id', $distributorId)
            ->whereIn('status', ['d-approved', 'ready'])
            ->orderBy('updated_at', 'desc')
            ->get();

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
                'distributor' => $req->distributor ? $req->distributor->full_name : 'Unknown',
                'date' => $req->request_date->format('Y-m-d'),
                'location' => $req->delivery_address ?? 'Main Office',
                'status' => $req->status,
                'priority' => ucfirst($req->priority),
                'totalAmount' => (float) $req->total_cost,
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
            'success' => true,
            'data' => $formatted,
            'permissions' => $permissions
        ]);
    }

    /**
     * Approve and release budget (Marks status as 'ready' and logs deduction)
     */
    public function approve(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            if (!$permissions['can_update']) {
                return response()->json([
                    'message' => 'Access Denied: You do not have permission to approve budget releases.'
                ], 403);
            }

            DB::beginTransaction();

            $procurement = ProcurementRequest::findOrFail($id);

            if ($procurement->status !== 'd-approved') {
                return response()->json(['message' => 'Only Distributor Approved (d-approved) requests can have their budget released.'], 400);
            }

            // Update status to 'ready'
            $procurement->updateStatus('ready');

            // Log the budget deduction based on the total cost and distributor id
            BudgetDeductionLog::create([
                'distributor_id' => $procurement->distributor_id,
                'procurement_request_id' => $procurement->id,
                'amount' => $procurement->total_cost,
                'description' => 'Budget released and deducted for procurement request ' . ($procurement->request_code ?? $procurement->id)
            ]);
            
            DB::commit();

            return response()->json([
                'message' => 'Budget successfully released. Status is now Ready.',
                'data' => $procurement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to release budget: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to reject budget releases.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $procurement = ProcurementRequest::findOrFail($id);

        $procurement->updateStatus('rejected', $request->reason);

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}