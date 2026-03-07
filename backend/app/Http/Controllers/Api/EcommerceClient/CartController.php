<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use App\Models\EcommerceClient\ClientCart;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ClientOrderItem;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\OperationDistributor\DistributorInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Fetch all cart items for the authenticated client
     * Now securely handles unauthenticated guests by returning an empty cart list.
     */
    public function index()
    {
        $user = Auth::guard('sanctum')->user();
        
        // Return an empty cart if the user is a guest
        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        $cartItems = ClientCart::with(['product'])
            ->where('client_id', $user->id)
            ->get();

        // Fetch active promotions
        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        $formattedItems = [];

        foreach ($cartItems as $item) {
            if (!$item->product || !$item->product->is_active) {
                continue;
            }

            // Calculate total available stock for this product from this distributor
            $stock = DistributorInventory::where('product_id', $item->product_id)
                ->where('distributor_id', $item->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->sum('quantity');

            // Get distributor coordinates for shipping calculation
            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $item->distributor_id)
                ->select('latitude', 'longitude')
                ->first();

            // Promotion verification
            $originalPrice = (float) $item->product->price;
            $discountedPrice = $originalPrice;
            $promoData = null;

            $productPromo = $promotions->where('distributor_id', $item->distributor_id)
                ->filter(function($promo) use ($item) {
                    return $promo->product_id == $item->product_id || is_null($promo->product_id);
                })->first();

            if ($productPromo) {
                $promoData = [
                    'id' => $productPromo->id,
                    'name' => $productPromo->name,
                    'type' => $productPromo->type,
                    'discount_value' => (float) $productPromo->discount_value,
                ];

                if ($productPromo->type === 'percentage_discount') {
                    $discountedPrice = $originalPrice - ($originalPrice * ((float)$productPromo->discount_value / 100));
                } elseif ($productPromo->type === 'fixed_discount' || $productPromo->type === 'fixed_amount') {
                    $discountedPrice = max(0, $originalPrice - (float)$productPromo->discount_value);
                }
            }

            $formattedItems[] = [
                'id' => $item->id, // Cart item ID
                'product_id' => $item->product_id,
                'distributor_id' => $item->distributor_id,
                'type' => 'product', // Keep for frontend compatibility
                'name' => $item->product->name,
                'brand' => 'Distributor Brand', 
                'typeDesc' => $item->product->type,
                'finish' => 'Standard',
                'original_price' => $originalPrice,
                'price' => $discountedPrice,
                'promotion' => $promoData,
                'quantity' => (int) $item->quantity,
                'unit' => $item->product->size ?? 'unit',
                'stock' => (int) $stock,
                'color' => $item->product->color_code ?? '#ffffff',
                'image_url' => $item->product->image_url ? asset('storage/' . ltrim($item->product->image_url, '/')) : null,
                'distributor_lat' => $distAddress->latitude ?? null,
                'distributor_lng' => $distAddress->longitude ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedItems
        ]);
    }

    /**
     * Update the quantity of a specific cart item
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = ClientCart::where('id', $id)
            ->where('client_id', Auth::id())
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Quantity updated'
        ]);
    }

    /**
     * Remove a specific item from the cart
     */
    public function destroy($id)
    {
        $cartItem = ClientCart::where('id', $id)
            ->where('client_id', Auth::id())
            ->first();

        if ($cartItem) {
            $cartItem->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    /**
     * Clear all items from the user's cart
     */
    public function clear()
    {
        ClientCart::where('client_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    /**
     * Checkout entire cart
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'custom_address' => 'nullable|string'
        ]);

        $user = Auth::user();
        $cartItems = ClientCart::with('product')->where('client_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
        }

        // 1. Get Address
        $clientAddress = DB::table('client_addresses')
            ->join('client_requirements', 'client_addresses.client_requirements_id', '=', 'client_requirements.id')
            ->where('client_requirements.user_id', $user->id)
            ->select('latitude', 'longitude', 'block_address', 'barangay', 'city', 'province')
            ->first();

        if (!$clientAddress || !$clientAddress->latitude || !$clientAddress->longitude) {
            return response()->json([
                'success' => false, 
                'message' => 'Delivery address coordinates not found. Please update your profile.'
            ], 400);
        }

        $defaultAddress = "{$clientAddress->block_address}, {$clientAddress->barangay}, {$clientAddress->city}, {$clientAddress->province}";
        $fullAddress = $request->filled('custom_address') ? $request->custom_address : $defaultAddress;

        // 2. Validate Stock for all items
        foreach ($cartItems as $item) {
            $totalStock = DistributorInventory::where('product_id', $item->product_id)
                ->where('distributor_id', $item->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->sum('quantity');

            if ($totalStock < $item->quantity) {
                return response()->json([
                    'success' => false, 
                    'message' => "Insufficient stock for product: {$item->product->name}."
                ], 400);
            }
        }

        // 3. Setup Shipping Rules and Promotions
        $shippingRule = ShippingRule::first() ?? new ShippingRule([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00
        ]);

        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        $totalOrderAmount = 0;
        $totalShippingFee = 0;
        $appliedPromotions = [];

        foreach ($cartItems as $item) {
            $originalPrice = (float) $item->product->price;
            $discountedPrice = $originalPrice;
            $hasFreeShipping = false;

            // Check if product qualifies for any active promotions
            $productPromo = $promotions->where('distributor_id', $item->distributor_id)
                ->filter(function($promo) use ($item) {
                    return $promo->product_id == $item->product_id || is_null($promo->product_id);
                })->first();

            if ($productPromo) {
                if ($productPromo->type === 'percentage_discount') {
                    $discountedPrice = $originalPrice - ($originalPrice * ((float)$productPromo->discount_value / 100));
                } elseif ($productPromo->type === 'fixed_discount' || $productPromo->type === 'fixed_amount') {
                    $discountedPrice = max(0, $originalPrice - (float)$productPromo->discount_value);
                } elseif ($productPromo->type === 'free_shipping') {
                    $hasFreeShipping = true;
                }

                // Track applied promo IDs to increment their usage counters later
                if (!isset($appliedPromotions[$productPromo->id])) {
                    $appliedPromotions[$productPromo->id] = true;
                }
            }

            $totalOrderAmount += ($discountedPrice * $item->quantity);
            $item->discounted_price = $discountedPrice; // Attach for row insertion later
            
            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $item->distributor_id)
                ->select('latitude', 'longitude')
                ->first();

            $distance = 0;
            if ($distAddress) {
                $distance = $this->calculateDistance($clientAddress->latitude, $clientAddress->longitude, $distAddress->latitude, $distAddress->longitude);
            }

            $distanceFee = $distance * $shippingRule->base_rate_per_km;
            $quantityFee = $item->quantity * $shippingRule->rate_per_item;
            $itemShippingFee = $distanceFee + $quantityFee;

            // Nullify item shipping fee if a free shipping promo was applied
            if ($hasFreeShipping) {
                $itemShippingFee = 0;
            }

            $totalShippingFee += $itemShippingFee;
        }

        // Apply global threshold rules if it's hit
        if ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold) {
            $totalShippingFee = 0;
        }

        $grandTotal = $totalOrderAmount + $totalShippingFee;

        DB::beginTransaction();
        try {
            // 4. Create Order
            $order = ClientOrder::create([
                'client_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalOrderAmount,
                'shipping_fee' => $totalShippingFee,
                'grand_total' => $grandTotal,
                'payment_method' => 'cod',
                'status' => 'pending',
                'delivery_address' => $fullAddress,
            ]);

            // 5. Create Order Items & Deduct Stock
            foreach ($cartItems as $item) {
                ClientOrderItem::create([
                    'order_id' => $order->id,
                    'distributor_id' => $item->distributor_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->discounted_price // Ensure we insert discounted price into items table
                ]);

                $remainingToDeduct = $item->quantity;
                
                $inventories = DistributorInventory::where('product_id', $item->product_id)
                    ->where('distributor_id', $item->distributor_id)
                    ->where('ecommerce_status', 'deployed')
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($inventories as $inventory) {
                    if ($remainingToDeduct <= 0) break;

                    if ($inventory->quantity >= $remainingToDeduct) {
                        $inventory->quantity -= $remainingToDeduct;
                        $inventory->save();
                        $remainingToDeduct = 0;
                    } else {
                        $remainingToDeduct -= $inventory->quantity;
                        $inventory->quantity = 0;
                        $inventory->save();
                    }
                }

                if ($remainingToDeduct > 0) {
                    throw new \Exception("Not enough stock remaining for {$item->product->name}.");
                }
            }

            // Increment usage count of applied promotions
            foreach (array_keys($appliedPromotions) as $promoId) {
                DB::table('crm_promotions')->where('id', $promoId)->increment('used_count');
            }
            
            // 6. Clear Cart
            ClientCart::where('client_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully via Cash on Delivery',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}