<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EcommerceClient\ProductReview;
use Carbon\Carbon;

class ReviewManagementController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module.
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
            ];
        }

        // Check RBAC for standard employees
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
                    'can_create' => (bool) $access->can_create,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
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

        // Get permissions and check RBAC Read Access using 'ec_reviews'
        $permissions = $this->getPermissions($user, 'ec_reviews');
        
        if (!$permissions['can_view']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view reviews.'], 403);
        }

        $distributorId = $this->getDistributorId();

        // Fetch reviews where the product belongs to this distributor
        $reviews = ProductReview::whereHas('product', function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            })
            ->with(['client', 'product', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedReviews = $reviews->map(function ($review) {
            $clientName = 'Customer';
            if ($review->client) {
                $clientName = trim(($review->client->first_name ?? '') . ' ' . ($review->client->last_name ?? ''));
                if (empty($clientName)) $clientName = $review->client->name ?? 'Customer';
            }

            return [
                'id' => $review->id,
                'client' => $clientName,
                'clientInitials' => strtoupper(substr($clientName, 0, 1)),
                'product' => $review->product->name ?? 'Unknown Product',
                'rating' => (int) $review->rating,
                'date' => Carbon::parse($review->created_at)->format('Y-m-d'),
                'comment' => $review->comment,
                'status' => $review->status ?? 'pending',
                'response' => $review->response,
                'responseDate' => $review->response_date,
                'orderId' => $review->order->order_number ?? 'Unknown'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedReviews,
            'permissions' => $permissions
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        // Hard backend RBAC check for updating
        if (!$this->checkRbacAccess($request->user(), 'ec_reviews', 'can_update')) {
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

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function respond(Request $request, $id)
    {
        // Hard backend RBAC check for responding to reviews
        if (!$this->checkRbacAccess($request->user(), 'ec_reviews', 'can_update')) {
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
        $review->status = 'published'; // Auto publish if responded
        $review->save();

        return response()->json(['success' => true, 'message' => 'Response saved successfully']);
    }
}