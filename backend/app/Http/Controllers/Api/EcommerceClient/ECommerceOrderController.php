<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ProductReview;
use Illuminate\Support\Facades\Auth;

class ECommerceOrderController extends Controller
{
    /**
     * Fetch all orders for the authenticated client.
     * Safely returns an empty array if the user is unauthenticated (guest).
     */
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        // If not logged in, return an empty list gracefully
        if (!$user) {
            return response()->json([]);
        }

        // Eager load items and the related products
        $orders = ClientOrder::with(['items.product', 'items.distributor'])
            ->where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch all reviews by this user for the fetched orders
        $orderIds = $orders->pluck('id');
        $reviews = ProductReview::where('client_id', $user->id)
            ->whereIn('order_id', $orderIds)
            ->get();

        // Transform the image URLs to full paths and attach review data
        $orders->each(function ($order) use ($reviews) {
            $order->items->each(function ($item) use ($reviews, $order) {
                
                // Attach review status and details to the order item dynamically
                $review = $reviews->where('order_id', $order->id)->where('product_id', $item->product_id)->first();
                $item->is_reviewed = $review ? true : false;
                $item->review_rating = $review ? $review->rating : null;
                $item->review_comment = $review ? $review->comment : null;

                if ($item->product && $item->product->image_url) {
                    if (!filter_var($item->product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($item->product->image_url, 'data:')) {
                        $item->product->image_url = asset('storage/' . ltrim($item->product->image_url, '/'));
                    }
                }
            });
        });

        return response()->json($orders);
    }

    /**
     * Submit or update a product review
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:client_orders,id',
            'product_id' => 'required|exists:distributor_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $user = $request->user();

        // Ensure order belongs to user and is delivered before they can review
        $order = ClientOrder::where('id', $request->order_id)
            ->where('client_id', $user->id)
            ->where('status', 'delivered')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found or not eligible for review.'], 403);
        }

        // Update or create the review
        $review = ProductReview::updateOrCreate(
            [
                'client_id' => $user->id,
                'order_id' => $request->order_id,
                'product_id' => $request->product_id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Review submitted successfully!', 
            'data' => $review
        ]);
    }
}