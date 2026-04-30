<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\ProcurementRequestCreated;
use App\Events\ProcurementRequestUpdated;

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
     * Fetch RBAC permissions for the logged-in user (Level-Based).
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        // Only for employees: strict position-based checks
        $distributorId = $this->getDistributorId($user);
        if (!$distributorId) {
            return ['can_view' => false, 'can_manage' => false, 'can_approve' => false];
        }

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) {
            return ['can_view' => false, 'can_manage' => false, 'can_approve' => false];
        }

        $position = DB::table('positions')
            ->where('distributor_id', $distributorId)
            ->where('title', $employee->position)
            ->first();

        if (!$position) {
            return ['can_view' => false, 'can_manage' => false, 'can_approve' => false];
        }

        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_procurement') 
            ->first();

        if ($access) {
            return [
                'can_view' => (bool)$access->can_view,
                'can_manage' => (bool)$access->can_manage,
                'can_approve' => (bool)$access->can_approve,
            ];
        }

        return ['can_view' => false, 'can_manage' => false, 'can_approve' => false];
    }

    /**
     * Get paginated procurement requests with finance statistics
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
                'permissions' => $permissions,
                'distributor_id' => $distributorId
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

            if (!$permissions['can_view']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to view finance requests.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $request = ProcurementRequest::with(['requester', 'product', 'selectedSupplier'])
                ->where('distributor_id', $distributorId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $request
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve the procurement request details.',
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

            if (!$permissions['can_approve']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to approve finance requests.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $procurementRequest = ProcurementRequest::where('distributor_id', $distributorId)
                ->findOrFail($id);

            if ($procurementRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be approved.'
                ], 400);
            }

            DB::beginTransaction();

            // <--- EXACT FIX: Corrected to match the actual DB column names --->
            $procurementRequest->finance_approved_at = now();
            $procurementRequest->finance_approved_by = $user->id; 
            
            // Advance status internally
            $procurementRequest->updateStatus('approved', null);

            DB::commit();

            // Notify Logistics/Operations immediately
            event(new ProcurementRequestUpdated($distributorId));

            return response()->json([
                'success' => true,
                'message' => 'Procurement request has been approved successfully.',
                'data' => $procurementRequest->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a procurement request
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);

            if (!$permissions['can_approve']) { 
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You do not have permission to reject finance requests.'
                ], 403);
            }

            $distributorId = $this->getDistributorId($user);

            $procurementRequest = ProcurementRequest::where('distributor_id', $distributorId)
                ->findOrFail($id);

            if ($procurementRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be rejected.'
                ], 400);
            }

            DB::beginTransaction();

            // <--- EXACT FIX: Corrected to match the actual DB column names --->
            $procurementRequest->finance_rejected_at = now();
            $procurementRequest->finance_rejected_by = $user->id;
            
            $procurementRequest->updateStatus('rejected', $request->reason);

            DB::commit();

            // Notify Logistics/Operations immediately
            event(new ProcurementRequestUpdated($distributorId));

            return response()->json([
                'success' => true,
                'message' => 'Procurement request has been rejected.',
                'data' => $procurementRequest->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary counts for the dashboard
     */
    public function dashboardCounts()
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