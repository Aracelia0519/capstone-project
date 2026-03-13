<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ProductReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * Fetch details for order pick-up tracking
     */
    public function getPickUpDetails(Request $request, $id)
    {
        $user = $request->user();
        $order = ClientOrder::where('id', $id)->where('client_id', $user->id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $delivery = DB::table('ec_order_deliveries')->where('order_id', $id)->first();
        
        // Get distributor ID from first item to locate their shop address
        $firstItem = DB::table('client_order_items')->where('order_id', $id)->first();
        $distributorId = $firstItem ? $firstItem->distributor_id : null;
        
        $address = null;
        if ($distributorId) {
            $req = DB::table('distributor_requirements')->where('user_id', $distributorId)->first();
            if ($req) {
                $address = DB::table('distributor_addresses')->where('distributor_requirements_id', $req->id)->first();
            }
        }

        return response()->json([
            'success' => true,
            'preparation_proof' => $delivery && $delivery->preparation_proof_path ? asset($delivery->preparation_proof_path) : null,
            'distributor_lat' => $address ? $address->latitude : null,
            'distributor_lng' => $address ? $address->longitude : null,
            'distributor_address' => $address ? ($address->block_address . ', ' . $address->barangay . ', ' . $address->city . ', ' . $address->province) : null
        ]);
    }

    /**
     * Handle the submission of pick-up and payment proofs
     */
    public function submitPickUp(Request $request, $id)
    {
        $request->validate([
            'proof_of_pickup' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'proof_of_payment' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = $request->user();
        $order = ClientOrder::where('id', $id)->where('client_id', $user->id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Upload images
        $pickupPath = $request->file('proof_of_pickup')->store('ec_deliveries_proof', 'public');
        $paymentPath = $request->file('proof_of_payment')->store('ec_payment_proofs', 'public');

        DB::beginTransaction();
        try {
            // Update ec_order_deliveries
            DB::table('ec_order_deliveries')
                ->where('order_id', $id)
                ->update([
                    'status' => 'completed',
                    'delivery_proof_path' => 'storage/' . $pickupPath,
                    'payment_received_proof_path' => 'storage/' . $paymentPath,
                    'updated_at' => now()
                ]);

            // Update order status
            $order->update(['status' => 'delivered']);

            // Fetch all items to distribute finances properly across potentially multiple sellers
            $orderItems = DB::table('client_order_items')->where('order_id', $id)->get();
            $distributorTotals = [];
            
            foreach ($orderItems as $item) {
                $itemTotal = $item->price * $item->quantity; // Pick-Up has 0 shipping fee per distributor
                if (!isset($distributorTotals[$item->distributor_id])) {
                    $distributorTotals[$item->distributor_id] = 0;
                }
                $distributorTotals[$item->distributor_id] += $itemTotal;
            }

            // Loop and process financial entries per distributor
            foreach ($distributorTotals as $distributorId => $distGrandTotal) {
                $distVatableSales = round($distGrandTotal / 1.12, 2);
                $distVatAmount = round($distGrandTotal - $distVatableSales, 2);

                // 1. Insert Remittance
                DB::table('ec_delivery_remittances')->insert([
                    'distributor_id' => $distributorId,
                    'delivery_personnel_id' => null, // Null since the client picked it up directly
                    'order_id' => $id,
                    'amount' => $distGrandTotal,
                    'remittance_proof_path' => 'storage/' . $paymentPath,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 2. Insert Order Financials specific to this distributor
                DB::table('ec_order_financials')->insert([
                    'order_id' => $id,
                    'distributor_id' => $distributorId,
                    'amount' => $distGrandTotal,
                    'vat_deduction' => $distVatAmount,
                    'total_sales' => $distVatableSales,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Update Overall Cumulative Sales
                $overallSales = DB::table('distributor_overall_sales')
                    ->where('distributor_id', $distributorId)
                    ->first();

                if ($overallSales) {
                    DB::table('distributor_overall_sales')
                        ->where('distributor_id', $distributorId)
                        ->update([
                            'total_revenue' => $overallSales->total_revenue + $distVatableSales,
                            'total_sales_count' => $overallSales->total_sales_count + 1,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('distributor_overall_sales')->insert([
                        'distributor_id' => $distributorId,
                        'total_revenue' => $distVatableSales,
                        'total_sales_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pick-up confirmed successfully!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to confirm pick-up: ' . $e->getMessage()], 500);
        }
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