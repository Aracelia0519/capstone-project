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
            'data' => $formattedReviews
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
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