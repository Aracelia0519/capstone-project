<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\EcommerceClient\ClientCart;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ClientOrderItem;
use App\Models\EcommerceClient\ProductReview; 
use App\Models\Distributor\Product; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function getProducts()
    {
        $inventories = DistributorInventory::with(['product'])
            ->where('ecommerce_status', 'deployed')
            ->get()
            ->groupBy('product_id');

        // Fetch active promotions
        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        // Fetch published reviews to attach to products
        $publishedReviews = ProductReview::with('client')
            ->where('status', 'published') 
            ->get()
            ->groupBy('product_id');

        $products = [];

        foreach ($inventories as $productId => $items) {
            $totalQuantity = $items->sum('quantity');
            $firstItem = $items->first();
            $productModel = $firstItem->product;

            if (!$productModel || !$productModel->is_active) {
                continue;
            }

            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $firstItem->distributor_id)
                ->select('latitude', 'longitude')
                ->first();

            $originalPrice = (float) $productModel->price;
            $discountedPrice = $originalPrice;
            $promoData = null;

            if ($promotions->isNotEmpty()) {
                $productPromo = $promotions->where('distributor_id', $firstItem->distributor_id)
                    ->filter(function($promo) use ($productModel) {
                        return $promo->product_id == $productModel->id || is_null($promo->product_id);
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
            }

            $productReviews = $publishedReviews->get($productModel->id, collect());
            $avgRating = $productReviews->avg('rating') ? round($productReviews->avg('rating'), 1) : 0;
            $reviewCount = $productReviews->count();
            
            $formattedReviews = $productReviews->map(function($rev) {
                $clientName = 'Customer';
                if ($rev->client) {
                    $clientName = trim(($rev->client->first_name ?? '') . ' ' . ($rev->client->last_name ?? ''));
                    if (empty($clientName)) $clientName = $rev->client->name ?? 'Customer';
                }
                return [
                    'id' => $rev->id,
                    'client' => $clientName,
                    'clientInitials' => strtoupper(substr($clientName, 0, 1)),
                    'rating' => (int)$rev->rating,
                    'comment' => $rev->comment,
                    'response' => $rev->response,
                    'response_date' => $rev->response_date ? \Carbon\Carbon::parse($rev->response_date)->format('M d, Y') : null,
                    'date' => \Carbon\Carbon::parse($rev->created_at)->format('M d, Y')
                ];
            })->values()->toArray();

            $products[] = [
                'id' => $productModel->id,
                'distributor_id' => $firstItem->distributor_id,
                'name' => $productModel->name,
                'brand' => 'Distributor Brand', 
                'type' => $productModel->type,
                'category' => $productModel->category,
                'finish' => 'Standard', 
                'original_price' => round($originalPrice, 2),
                'price' => round($discountedPrice, 2),
                'promotion' => $promoData,
                'stock' => $totalQuantity, 
                'rating' => $avgRating,
                'review_count' => $reviewCount,
                'reviews' => $formattedReviews,
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

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:distributor_products,id',
            'distributor_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        $cartItem = ClientCart::where('client_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('distributor_id', $request->distributor_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            ClientCart::create([
                'client_id' => $user->id,
                'distributor_id' => $request->distributor_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully'
        ]);
    }

    public function orderNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:distributor_products,id',
            'distributor_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'distributor_lat' => 'required|numeric',
            'distributor_lng' => 'required|numeric',
            'custom_address' => 'nullable|string' 
        ]);

        $user = Auth::user();

        // 1. Get Client coordinates and address string
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

        // 2. Verify Product and Total Available Stock for this specific distributor
        $product = Product::find($request->product_id);
        
        $totalStock = DistributorInventory::where('product_id', $product->id)
            ->where('distributor_id', $request->distributor_id)
            ->where('ecommerce_status', 'deployed')
            ->sum('quantity');

        if ($totalStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock for this product.'
            ], 400);
        }

        // 3. Apply Promotions to Price & Shipping
        $currentDate = now()->toDateString();
        $promotion = DB::table('crm_promotions')
            ->where('distributor_id', $request->distributor_id)
            ->where(function($q) use ($product) {
                $q->where('product_id', $product->id)->orWhereNull('product_id');
            })
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->first();

        $originalPrice = (float) $product->price;
        $discountedPrice = $originalPrice;
        $hasFreeShipping = false;

        if ($promotion) {
            if ($promotion->type === 'percentage_discount') {
                $discountedPrice = $originalPrice - ($originalPrice * ((float)$promotion->discount_value / 100));
            } elseif ($promotion->type === 'fixed_discount' || $promotion->type === 'fixed_amount') {
                $discountedPrice = max(0, $originalPrice - (float)$promotion->discount_value);
            } elseif ($promotion->type === 'free_shipping') {
                $hasFreeShipping = true;
            }
        }

        $totalOrderAmount = round($discountedPrice * $request->quantity, 2);

        // Calculate Distance and Shipping
        $shippingRule = ShippingRule::first() ?? new ShippingRule([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00
        ]);

        $distance = $this->calculateDistance(
            $clientAddress->latitude,
            $clientAddress->longitude,
            $request->distributor_lat,
            $request->distributor_lng
        );

        // FIX: Round distance fee to nearest whole number, and enforce a minimum 50 PHP base fare
        $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
        $distanceFee = max(50, $calculatedDistanceFee);
        $quantityFee = ($request->quantity * $shippingRule->rate_per_item);
        
        $shippingFee = round($distanceFee + $quantityFee, 2);

        if ($hasFreeShipping || ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold)) {
            $shippingFee = 0;
        }

        $grandTotal = round($totalOrderAmount + $shippingFee, 2);

        DB::beginTransaction();
        try {
            // 4. Create Order
            $order = ClientOrder::create([
                'client_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalOrderAmount,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'payment_method' => 'cod',
                'status' => 'pending',
                'delivery_address' => $fullAddress,
            ]);

            // 5. Create Order Item
            ClientOrderItem::create([
                'order_id' => $order->id,
                'distributor_id' => $request->distributor_id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $discountedPrice 
            ]);

            // Increment promo usage if applied
            if ($promotion) {
                DB::table('crm_promotions')->where('id', $promotion->id)->increment('used_count');
            }

            // 6. Deduct Stock from DistributorInventory (FIFO method)
            $remainingToDeduct = $request->quantity;
            
            $inventories = DistributorInventory::where('product_id', $product->id)
                ->where('distributor_id', $request->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc') 
                ->lockForUpdate() 
                ->get();

            foreach ($inventories as $inventory) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

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
                throw new \Exception('Not enough active stock across inventory records.');
            }
            
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

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array',
            'cart_items.*.product_id' => 'required|integer', 
            'cart_items.*.distributor_id' => 'required|integer',
            'cart_items.*.quantity' => 'required|integer|min:1',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.distributor_lat' => 'required|numeric',
            'cart_items.*.distributor_lng' => 'required|numeric',
        ]);

        $user = Auth::user();
        
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

        $shippingRule = ShippingRule::first() ?? new ShippingRule([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00
        ]);

        $totalShippingFee = 0;
        $totalOrderAmount = 0;
        $totalQuantity = 0;
        $currentDate = now()->toDateString();

        // FIX: Group items by distributor so distance fee isn't charged multiple times per seller
        $groupedItems = collect($request->cart_items)->groupBy('distributor_id');

        foreach ($groupedItems as $distributorId => $items) {
            $firstItem = $items->first();
            $distance = $this->calculateDistance(
                $clientAddress->latitude,
                $clientAddress->longitude,
                $firstItem['distributor_lat'],
                $firstItem['distributor_lng']
            );

            // Distance fee is applied ONCE per distributor (min 50 PHP rounded)
            $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
            $distributorShippingFee = max(50, $calculatedDistanceFee);
            $hasFreeShippingPromo = false;

            foreach ($items as $item) {
                $totalOrderAmount += ($item['price'] * $item['quantity']);
                $totalQuantity += $item['quantity'];
                
                // Add per-item handling fee
                $distributorShippingFee += ($item['quantity'] * $shippingRule->rate_per_item);

                // Check if this specific item triggers a free shipping promo
                $promoExists = DB::table('crm_promotions')
                    ->where('distributor_id', $distributorId)
                    ->where(function($q) use ($item) {
                        $q->where('product_id', $item['product_id'])->orWhereNull('product_id');
                    })
                    ->whereIn('status', ['approved', 'active', 'pending'])
                    ->whereDate('start_date', '<=', $currentDate)
                    ->whereDate('end_date', '>=', $currentDate)
                    ->whereRaw('used_count < usage_limit')
                    ->where('type', 'free_shipping')
                    ->exists();

                if ($promoExists) {
                    $hasFreeShippingPromo = true;
                }
            }

            if ($hasFreeShippingPromo) {
                $distributorShippingFee = 0;
            }

            $totalShippingFee += $distributorShippingFee;
        }

        if ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold) {
            $totalShippingFee = 0; 
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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}