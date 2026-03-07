<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\FinancePayroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollRequestController extends Controller
{
    
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
            ->where('permission_key', 'finance_payroll_request')
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

    // List all payroll requests
    public function index(Request $request)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_view']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to view payroll requests.'
            ], 403);
        }

        $status = $request->query('status', 'pending');
        $search = $request->query('search');

        $query = FinancePayroll::with(['user', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by Status
        if ($status !== 'All') {
            $query->where('status', strtolower($status));
        }

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('payroll_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $payrolls = $query->paginate(15);
        
        // Append RBAC permissions to the paginator array response
        $responseData = $payrolls->toArray();
        $responseData['permissions'] = $permissions;

        return response()->json($responseData);
    }

    // Approve a payroll request
    public function approve($id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to approve payroll requests.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $payroll = FinancePayroll::findOrFail($id);

            if ($payroll->status !== 'pending') {
                return response()->json(['message' => 'Only pending requests can be approved.'], 400);
            }

            $payroll->status = 'approved';
            $payroll->approved_by = Auth::id();
            $payroll->pay_date = Carbon::now(); // Set pay date to approval date
            $payroll->save();

            DB::commit();

            return response()->json([
                'message' => 'Payroll request approved successfully.',
                'data' => $payroll
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error approving request.', 'error' => $e->getMessage()], 500);
        }
    }

    // Reject (Cancel) a payroll request
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to reject payroll requests.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $payroll = FinancePayroll::findOrFail($id);

            if ($payroll->status !== 'pending') {
                return response()->json(['message' => 'Only pending requests can be rejected.'], 400);
            }

            // You can log the rejection reason if you add a 'remarks' column to DB
            $payroll->status = 'rejected'; 
            $payroll->approved_by = Auth::id(); // Track who rejected it
            $payroll->save();

            DB::commit();

            return response()->json([
                'message' => 'Payroll request rejected.',
                'data' => $payroll
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error rejecting request.', 'error' => $e->getMessage()], 500);
        }
    }
    
    // Get single details
    public function show($id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_view']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to view payroll details.'
            ], 403);
        }

        $payroll = FinancePayroll::with(['user', 'creator', 'approver'])->findOrFail($id);
        
        return response()->json([
            'data' => $payroll,
            'permissions' => $permissions
        ]);
    }
}