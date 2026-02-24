<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\Client\ClientRequirement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Fetch all available products from distributor inventories.
     */
    public function getProducts()
    {
        // Fetch deployed inventories and group by product to calculate total quantity
        // Removed 'distributor.distributorRequirements.addresses' from with() to fix the 500 error
        $inventories = DistributorInventory::with(['product'])
            ->where('ecommerce_status', 'deployed')
            ->get()
            ->groupBy('product_id');

        $products = [];

        foreach ($inventories as $productId => $items) {
            $totalQuantity = $items->sum('quantity');
            $firstItem = $items->first();
            $productModel = $firstItem->product;

            // Only map active products
            if (!$productModel || !$productModel->is_active) {
                continue;
            }

            // Get distributor coordinates manually since the relationship isn't defined on the User model
            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $firstItem->distributor_id)
                ->select('latitude', 'longitude')
                ->first();

            $products[] = [
                'id' => $productModel->id,
                'distributor_id' => $firstItem->distributor_id,
                'name' => $productModel->name,
                'brand' => 'Distributor Brand', // You can map this to a real brand if added in DB
                'type' => $productModel->type,
                'category' => $productModel->category,
                'finish' => 'Standard', // Default mapping
                'price' => (float) $productModel->price,
                'stock' => $totalQuantity, // If 0, frontend will mark it Out of Stock
                'rating' => 4.5, // Mock rating or calculate from reviews
                'color' => $productModel->color_code ?? '#ffffff',
                'image_url' => $productModel->image_url ? asset('storage/' . ltrim($productModel->image_url, '/')) : null,
                'distributor_lat' => $distAddress->latitude ?? null,
                'distributor_lng' => $distAddress->longitude ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Calculate Shipping Fee based on Distance, Quantity, and Total Amount
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.distributor_lat' => 'required|numeric',
            'cart_items.*.distributor_lng' => 'required|numeric',
        ]);

        $user = Auth::user();
        
        // Get Client coordinates
        $clientAddress = DB::table('client_addresses')
            ->join('client_requirements', 'client_addresses.client_requirements_id', '=', 'client_requirements.id')
            ->where('client_requirements.user_id', $user->id)
            ->select('latitude', 'longitude')
            ->first();

        if (!$clientAddress || !$clientAddress->latitude || !$clientAddress->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Client address coordinates not found. Please update your profile address.'
            ], 400);
        }

        $shippingRule = ShippingRule::first();
        if (!$shippingRule) {
            $shippingRule = new ShippingRule([
                'base_rate_per_km' => 15.00,
                'rate_per_item' => 5.00,
                'free_shipping_threshold' => 5000.00
            ]);
        }

        $totalShippingFee = 0;
        $totalOrderAmount = 0;
        $totalQuantity = 0;

        foreach ($request->cart_items as $item) {
            $totalOrderAmount += ($item['price'] * $item['quantity']);
            $totalQuantity += $item['quantity'];

            // Calculate Distance (Haversine formula)
            $distance = $this->calculateDistance(
                $clientAddress->latitude,
                $clientAddress->longitude,
                $item['distributor_lat'],
                $item['distributor_lng']
            );

            // 1. Fee based on Distance
            $distanceFee = $distance * $shippingRule->base_rate_per_km;
            
            // 2. Fee based on Quantity
            $quantityFee = $item['quantity'] * $shippingRule->rate_per_item;

            $totalShippingFee += ($distanceFee + $quantityFee);
        }

        // 3. Fee based on Total Order Amount (Free shipping threshold)
        if ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold) {
            $totalShippingFee = 0; // Free shipping
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_order_amount' => round($totalOrderAmount, 2),
                'total_quantity' => $totalQuantity,
                'calculated_shipping_fee' => round($totalShippingFee, 2),
                'is_free_shipping' => $totalShippingFee == 0
            ]
        ]);
    }

    /**
     * Haversine formula to calculate distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}