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
     * Fetch all pending promotions and dashboard stats for the current distributor.
     */
    public function index()
    {
        try {
            $user = Auth::user();
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
                'stats' => $stats
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