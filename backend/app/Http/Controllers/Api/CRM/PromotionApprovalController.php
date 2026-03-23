<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromotionApprovalController extends Controller
{
    /**
     * Resolve the distributor ID based on the logged-in user's role.
     */
    private function getDistributorId($user)
    {
        $distributorId = $user->id;

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if ($employee && $employee->parent_distributor_id) {
                $distributorId = $employee->parent_distributor_id;
            }
        } elseif (in_array($user->role, ['operational_distributor', 'hr_manager', 'finance_manager'])) {
            $tableName = $user->role . 's';
            $staff = DB::table($tableName)->where('user_id', $user->id)->first();
            if ($staff && $staff->parent_distributor_id) {
                $distributorId = $staff->parent_distributor_id;
            }
        }

        return $distributorId;
    }

    /**
     * Fetch RBAC permissions for the logged-in user.
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees (like distributors, admins) bypass this specific RBAC check
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
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
            ->where('permission_key', 'ec_promo_approval')
            ->first();

        // Check if access row exists and is generally granted
        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_manage' => (bool)$access->can_manage,
            'can_approve' => (bool)$access->can_approve,
        ];
    }

    /**
     * Fetch all pending promotions and dashboard stats for the current distributor.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            if (!$permissions['can_view']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: You do not have permission to view promotions.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $query = Promotion::where('distributor_id', $distributorId);

            $stats = [
                'total_pending' => (clone $query)->where('status', 'pending')->count(),
                'total_active' => (clone $query)->where('status', 'active')->count(),
                'total_rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];

            $promotions = Promotion::with(['creator', 'product'])
                ->where('distributor_id', $distributorId)
                ->where('status', 'pending')
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $promotions,
                'stats' => $stats,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Using new structure `can_approve`
            if (!$permissions['can_approve']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: You do not have permission to approve promotions.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $promotion = Promotion::where('id', $id)
                ->where('distributor_id', $distributorId)
                ->firstOrFail();

            $promotion->update(['status' => 'active']);

            return response()->json([
                'status' => 'success',
                'message' => 'Promotion approved and is now active.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Approval failed.'], 500);
        }
    }

    public function reject($id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Using new structure `can_approve`
            if (!$permissions['can_approve']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: You do not have permission to reject promotions.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $promotion = Promotion::where('id', $id)
                ->where('distributor_id', $distributorId)
                ->firstOrFail();

            $promotion->update(['status' => 'rejected']);

            return response()->json([
                'status' => 'success',
                'message' => 'Promotion has been rejected.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Rejection failed.'], 500);
        }
    }
}