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
     * Get all procurement requests for finance manager's distributor
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user is finance manager by role
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Finance manager only.'
                ], 403);
            }

            // Get finance manager's parent distributor ID from finance_managers table
            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

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
            
            // Get recently processed requests (approved/rejected in last 7 days)
            $recentlyProcessed = ProcurementRequest::with(['requester', 'product'])
                ->where('distributor_id', $distributorId)
                ->whereIn('status', ['approved', 'rejected'])
                ->where(function($q) {
                    $q->whereNotNull('finance_approved_at')
                      ->orWhereNotNull('finance_rejected_at');
                })
                ->where('updated_at', '>=', now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            // Get statistics for the current month
            $currentMonth = now()->format('Y-m');
            $statistics = ProcurementRequest::select(
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN total_cost ELSE 0 END) as approved_amount'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN total_cost ELSE 0 END) as rejected_amount'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN total_cost ELSE 0 END) as pending_amount')
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
                ]
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
            
            // Check if user is finance manager by role
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Finance manager only.'
                ], 403);
            }

            // Get finance manager's parent distributor ID from finance_managers table
            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

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

            // Get department budget information (you can replace this with actual budget logic)
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
            
            // Check if user is finance manager by role
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Finance manager only.'
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

            // Get finance manager's parent distributor ID
            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

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
            $procurementRequest->status = 'approved';
            $procurementRequest->finance_approved_by = $user->id;
            $procurementRequest->finance_approved_at = now();
            $procurementRequest->approved_at = now();
            
            // Save comments if provided
            if ($request->has('comments') && $request->comments) {
                // Store finance comments
                $procurementRequest->instructions = ($procurementRequest->instructions ? $procurementRequest->instructions . "\n\n" : '') . 
                                                    "Finance Comments: " . $request->comments;
            }
            
            $procurementRequest->save();

            // Here you can add additional logic like:
            // - Update inventory
            // - Send notifications
            // - Create purchase order
            // - Update budget

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
            
            // Check if user is finance manager by role
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Finance manager only.'
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

            // Get finance manager's parent distributor ID
            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

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

            // Here you can add additional logic like:
            // - Send rejection notification
            // - Log the rejection

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
            
            // Check if user is finance manager by role
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Finance manager only.'
                ], 403);
            }

            // Get finance manager's parent distributor ID
            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

            // Get monthly statistics for the current year
            $monthlyStats = ProcurementRequest::select(
                DB::raw('DATE_FORMAT(request_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(total_cost) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN total_cost ELSE 0 END) as approved_amount'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN total_cost ELSE 0 END) as rejected_amount')
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

            // Get today's requests
            $todayStats = ProcurementRequest::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
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
     * Helper function to get department budget information
     * Replace this with your actual budget logic
     */
    private function getDepartmentBudgetInfo($procurementRequest)
    {
        // This is a placeholder. Replace with actual budget logic from your system
        // For now, we'll return dummy data
        return [
            'department_budget' => 45000.00,
            'remaining_balance' => 32250.00,
            'budget_utilization' => '28.33%',
            'can_afford' => $procurementRequest->total_cost <= 32250.00
        ];
    }

    /**
     * Get request counts by status for quick overview
     */
    public function requestCounts()
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'finance_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $financeManager = $user->financeManager;
            if (!$financeManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Finance manager record not found.'
                ], 404);
            }

            $distributorId = $financeManager->parent_distributor_id;

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