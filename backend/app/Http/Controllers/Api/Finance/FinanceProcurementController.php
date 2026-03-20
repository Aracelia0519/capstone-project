<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinanceProcurementController extends Controller
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
            ->where('permission_key', 'finance_procurement')
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
     * Get all procurement requests for finance manager's distributor
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Check if user has permission to view
            if (!$permissions['can_view']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to view finance requests.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            // Get status filter if provided
            $status = $request->get('status', 'pending');
            $perPage = $request->get('per_page', 10);

            // Build query
            $query = ProcurementRequest::with(['requester', 'product'])
                ->where('distributor_id', $distributorId)
                ->orderBy('created_at', 'desc');

            // Apply status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Get pending requests for listing
            $pendingRequests = $query->where('status', 'pending')->paginate($perPage);
            
            // Get recently processed requests based on Finance timestamps
            $recentlyProcessed = ProcurementRequest::with(['requester', 'product'])
                ->where('distributor_id', $distributorId)
                ->where(function($q) {
                    $q->whereNotNull('finance_approved_at')
                      ->orWhereNotNull('finance_rejected_at');
                })
                ->where('updated_at', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            // Get statistics based on Finance actions for the current month
            $currentMonth = now()->format('Y-m');
            $statistics = ProcurementRequest::select(
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NOT NULL THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN finance_rejected_at IS NOT NULL THEN 1 ELSE 0 END) as rejected_count'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NULL AND finance_rejected_at IS NULL THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NOT NULL THEN total_cost ELSE 0 END) as approved_amount'),
                DB::raw('SUM(CASE WHEN finance_rejected_at IS NOT NULL THEN total_cost ELSE 0 END) as rejected_amount'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NULL AND finance_rejected_at IS NULL THEN total_cost ELSE 0 END) as pending_amount')
            )
            ->where('distributor_id', $distributorId)
            ->where('request_date', 'like', $currentMonth . '%')
            ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_requests' => $pendingRequests,
                    'recently_processed' => $recentlyProcessed,
                    'statistics' => [
                        'approved' => $statistics->approved_count ?? 0,
                        'rejected' => $statistics->rejected_count ?? 0,
                        'pending' => $statistics->pending_count ?? 0,
                        'approved_amount' => floatval($statistics->approved_amount ?? 0),
                        'rejected_amount' => floatval($statistics->rejected_amount ?? 0),
                        'pending_amount' => floatval($statistics->pending_amount ?? 0),
                    ]
                ],
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch procurement requests.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get details of a specific procurement request
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Check if user has permission to view
            if (!$permissions['can_view']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to view finance requests.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            // Find the request
            $request = ProcurementRequest::with(['requester', 'product'])
                ->where('distributor_id', $distributorId)
                ->where('id', $id)
                ->first();

            if (!$request) {
                return response()->json([
                    'success' => false,
                    'message' => 'Procurement request not found.'
                ], 404);
            }

            // Get budget information dynamically
            $budgetInfo = $this->getDepartmentBudgetInfo($request);

            return response()->json([
                'success' => true,
                'data' => [
                    'request' => $request,
                    'budget_info' => $budgetInfo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch procurement request details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a procurement request
     */
    public function approve(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Check if user has permission to update/approve
            if (!$permissions['can_update']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to approve finance requests.'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'comments' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            // Find the request
            $procurementRequest = ProcurementRequest::where('distributor_id', $distributorId)
                ->where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$procurementRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Procurement request not found or already processed.'
                ], 404);
            }

            // Verify Budget strictly before approving
            $budgetInfo = $this->getDepartmentBudgetInfo($procurementRequest);
            if (!$budgetInfo['can_afford']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action Denied: The request total cost exceeds the available budget.'
                ], 400);
            }

            // Start transaction
            DB::beginTransaction();

            // Update request status (NO DEDUCTION FROM SALES TABLE HAPPENS HERE)
            $procurementRequest->status = 'approved';
            $procurementRequest->finance_approved_by = $user->id;
            $procurementRequest->finance_approved_at = now();
            $procurementRequest->approved_at = now();
            
            // Save comments if provided
            if ($request->has('comments') && $request->comments) {
                $procurementRequest->instructions = ($procurementRequest->instructions ? $procurementRequest->instructions . "\n\n" : '') . 
                                                    "Finance Comments: " . $request->comments;
            }
            
            $procurementRequest->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procurement request has been approved successfully.',
                'data' => $procurementRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve procurement request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a procurement request
     */
    public function reject(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            // Check if user has permission to update/reject
            if (!$permissions['can_update']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to reject finance requests.'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'comments' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            // Find the request
            $procurementRequest = ProcurementRequest::where('distributor_id', $distributorId)
                ->where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$procurementRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Procurement request not found or already processed.'
                ], 404);
            }

            // Start transaction
            DB::beginTransaction();

            // Update request status
            $procurementRequest->status = 'rejected';
            $procurementRequest->finance_rejected_by = $user->id;
            $procurementRequest->finance_rejected_at = now();
            $procurementRequest->rejection_reason = $request->comments;
            $procurementRequest->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procurement request has been rejected successfully.',
                'data' => $procurementRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject procurement request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for finance dashboard
     */
    public function statistics()
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            if (!$permissions['can_view']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            // Get monthly statistics based on timestamps
            $monthlyStats = ProcurementRequest::select(
                DB::raw('DATE_FORMAT(request_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NOT NULL THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN finance_rejected_at IS NOT NULL THEN 1 ELSE 0 END) as rejected_count'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NULL AND finance_rejected_at IS NULL THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(total_cost) as total_amount'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NOT NULL THEN total_cost ELSE 0 END) as approved_amount'),
                DB::raw('SUM(CASE WHEN finance_rejected_at IS NOT NULL THEN total_cost ELSE 0 END) as rejected_amount')
            )
            ->where('distributor_id', $distributorId)
            ->whereYear('request_date', now()->year)
            ->groupBy(DB::raw('DATE_FORMAT(request_date, "%Y-%m")'))
            ->orderBy('month')
            ->get();

            // Get category-wise statistics
            $categoryStats = ProcurementRequest::select(
                'category',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(total_cost) as total_amount'),
                DB::raw('AVG(total_cost) as average_amount')
            )
            ->where('distributor_id', $distributorId)
            ->groupBy('category')
            ->get();

            // Get today's stats based on timestamps
            $todayStats = ProcurementRequest::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NOT NULL THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN finance_rejected_at IS NOT NULL THEN 1 ELSE 0 END) as rejected'),
                DB::raw('SUM(CASE WHEN finance_approved_at IS NULL AND finance_rejected_at IS NULL THEN 1 ELSE 0 END) as pending')
            )
            ->where('distributor_id', $distributorId)
            ->whereDate('request_date', today())
            ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_stats' => $monthlyStats,
                    'category_stats' => $categoryStats,
                    'today_stats' => $todayStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to get budget information dynamically from distributor_overall_sales
     */
    private function getDepartmentBudgetInfo($procurementRequest)
    {
        // Query the distributor_overall_sales table for this specific distributor
        $salesRecord = DB::table('distributor_overall_sales')
            ->where('distributor_id', $procurementRequest->distributor_id)
            ->first();

        // Used 'total_revenue' based on the provided capstone001.sql dump
        $budget = $salesRecord ? ($salesRecord->total_revenue ?? 0) : 0;

        return [
            'budget' => (float) $budget,
            'can_afford' => $procurementRequest->total_cost <= $budget
        ];
    }

    /**
     * Get request counts by status for quick overview
     */
    public function requestCounts()
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            if (!$permissions['can_view']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);
            if (!$distributorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor record not found.'
                ], 404);
            }

            $counts = ProcurementRequest::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing'),
                DB::raw('SUM(CASE WHEN status = "shipped" THEN 1 ELSE 0 END) as shipped'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered')
            )
            ->where('distributor_id', $distributorId)
            ->first();

            return response()->json([
                'success' => true,
                'data' => $counts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch request counts.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}