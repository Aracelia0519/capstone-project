<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EcommerceClient\ProductReview;
use App\Models\UserReport;
use Carbon\Carbon;
use App\Events\Ecommerce\ReviewUpdated;
use App\Events\Ecommerce\OrderUpdated;

class ReviewManagementController extends Controller
{
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_manage' => true,
                'can_approve' => true
            ];
        }

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_manage' => (bool) $access->can_manage,
                    'can_approve' => (bool) $access->can_approve,
                ];
            }
        }

        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action)
    {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    private function getDistributorId()
    {
        $user = Auth::user();
        $distributorId = $user->id;

        if ($user->role === 'operational_distributor') {
            $distributorId = DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'employee') {
            $distributorId = DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'hr_manager') {
            $distributorId = DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'finance_manager') {
            $distributorId = DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        }

        return $distributorId;
    }

    public function index()
    {
        $user = Auth::user();

        $permissions = $this->getPermissions($user, 'ec_reviews');
        
        if (!$permissions['can_view']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view reviews.'], 403);
        }

        $distributorId = $this->getDistributorId();

        $reviews = ProductReview::whereHas('product', function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            })
            ->with(['client', 'product', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        $spIds = $reviews->pluck('service_provider_id')->filter()->unique();
        $spUsers = \App\Models\User::whereIn('id', $spIds)->get()->keyBy('id');

        $spOrderIds = $reviews->pluck('sp_order_id')->filter()->unique();
        $spOrders = \App\Models\ServiceProvider\SpOrder::whereIn('id', $spOrderIds)->get()->keyBy('id');

        $formattedReviews = $reviews->map(function ($review) use ($spUsers, $spOrders) {
            $clientName = 'Customer';
            $reviewerType = 'Customer';
            $orderNumber = 'Unknown';

            if ($review->service_provider_id && isset($spUsers[$review->service_provider_id])) {
                $sp = $spUsers[$review->service_provider_id];
                $clientName = trim(($sp->first_name ?? '') . ' ' . ($sp->last_name ?? ''));
                if (empty($clientName)) $clientName = $sp->name ?? 'Service Provider';
                
                $reviewerType = 'Service Provider';
                
                if ($review->sp_order_id && isset($spOrders[$review->sp_order_id])) {
                    $orderNumber = $spOrders[$review->sp_order_id]->order_number;
                }
            } 
            elseif ($review->client) {
                $clientName = trim(($review->client->first_name ?? '') . ' ' . ($review->client->last_name ?? ''));
                if (empty($clientName)) $clientName = $review->client->name ?? 'Customer';
                $orderNumber = $review->order->order_number ?? 'Unknown';
            }

            return [
                'id' => $review->id,
                'reviewer_id' => $review->client_id ?? $review->service_provider_id,
                'client' => $clientName,
                'clientInitials' => strtoupper(substr($clientName, 0, 1)),
                'reviewerType' => $reviewerType, 
                'product' => $review->product->name ?? 'Unknown Product',
                'rating' => (int) $review->rating,
                'date' => Carbon::parse($review->created_at)->format('Y-m-d'),
                'comment' => $review->comment,
                'status' => $review->status ?? 'pending',
                'response' => $review->response,
                'responseDate' => $review->response_date,
                'orderId' => $orderNumber
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedReviews,
            'permissions' => $permissions,
            'distributor_id' => $distributorId,
            'is_admin' => $user->role === 'admin'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_reviews', 'can_manage')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to update review statuses.'], 403);
        }

        $request->validate([
            'status' => 'required|in:published,hidden,pending'
        ]);

        $distributorId = $this->getDistributorId();

        $review = ProductReview::whereHas('product', function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            })->find($id);

        if (!$review) return response()->json(['success' => false, 'message' => 'Review not found'], 404);

        $review->status = $request->status;
        $review->save();

        event(new ReviewUpdated($distributorId));

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function respond(Request $request, $id)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_reviews', 'can_manage')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to respond to reviews.'], 403);
        }

        $request->validate([
            'response' => 'required|string'
        ]);

        $distributorId = $this->getDistributorId();

        $review = ProductReview::whereHas('product', function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            })->find($id);

        if (!$review) return response()->json(['success' => false, 'message' => 'Review not found'], 404);

        $review->response = $request->response;
        $review->response_date = now()->toDateString();
        $review->status = 'published'; 
        $review->save();

        event(new ReviewUpdated($distributorId));

        if ($review->client_id) {
            event(new OrderUpdated($review->client_id, null));
        } elseif ($review->service_provider_id) {
            event(new OrderUpdated(null, $review->service_provider_id));
        }

        return response()->json(['success' => true, 'message' => 'Response saved successfully']);
    }

    public function submitReport(Request $request, $userId)
    {
        try {
            $user = Auth::user();
            if (!$this->checkRbacAccess($user, 'ec_reviews', 'can_view')) {
                return response()->json(['message' => 'Access Denied.'], 403);
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:255',
                'description' => 'required|string',
                'incident_date' => 'required|date',
                'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120'
            ]);

            $distId = $this->getDistributorId();

            $reportsToday = UserReport::where('reported_by_id', $distId)
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($reportsToday >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your distributor account has reached the maximum limit of 3 reports per day.'
                ], 429);
            }

            $reportedUser = \App\Models\User::find($userId);
            if (!$reportedUser) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            $evidencePath = null;
            if ($request->hasFile('evidence')) {
                $evidencePath = $request->file('evidence')->store('reports/evidence', 'public');
            }

            UserReport::create([
                'reported_user_id' => $reportedUser->id,
                'reported_by_id' => $distId,
                'reporter_role' => 'distributor',
                'reported_user_role' => $reportedUser->role,
                'reason' => $validated['reason'],
                'description' => $validated['description'],
                'incident_date' => $validated['incident_date'],
                'evidence_path' => $evidencePath,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Report submitted successfully. Administrators will review the incident.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to submit report', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}