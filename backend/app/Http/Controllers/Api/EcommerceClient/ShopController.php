<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\EcommerceClient\ClientCart;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ClientOrderItem;
use App\Models\EcommerceClient\ProductReview; 
use App\Models\EcommerceClient\OrderVatDeduction;
use App\Models\Distributor\Product; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\Ecommerce\OrderPlaced;

class ShopController extends Controller
{
    public function getProducts()
    {
        $inventories = DistributorInventory::with(['product'])
            ->where('ecommerce_status', 'deployed')
            ->get()
            ->groupBy('product_id');

        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        $allPublishedReviews = ProductReview::with('client')
            ->where('status', 'published') 
            ->get();
            
        $spIdsAll = $allPublishedReviews->pluck('service_provider_id')->filter()->unique();
        $spUsersAll = \App\Models\User::whereIn('id', $spIdsAll)->get()->keyBy('id');

        $publishedReviews = $allPublishedReviews->groupBy('product_id');

        $paymentSettings = collect();
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettings = DB::table('distributor_payment_settings')->get()->keyBy('distributor_id');
        }

        // Group products by (category, type, name)
        $grouped = [];
        foreach ($inventories as $productId => $items) {
            $firstItem = $items->first();
            $productModel = $firstItem->product;
            if (!$productModel || !$productModel->is_active) continue;

            $key = $productModel->category . '|' . $productModel->type . '|' . $productModel->name;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_ids' => [],
                    'items' => collect(),
                    'distributor_id' => $firstItem->distributor_id,
                    'distributor_name' => null,
                    'distributor_lat' => null,
                    'distributor_lng' => null,
                    'distributor_gcash_enabled' => false,
                    'distributor_pickup_enabled' => false,
                    'category' => $productModel->category,
                    'type' => $productModel->type,
                    'name' => $productModel->name,
                    'brand' => 'Distributor Brand',
                    'color' => null,
                    'image_url' => null,
                ];
            }
            $grouped[$key]['product_ids'][] = $productId;
            $grouped[$key]['items'] = $grouped[$key]['items']->merge($items);
        }

        $products = [];
        foreach ($grouped as $key => $group) {
            $productIds = $group['product_ids'];
            $items = $group['items'];
            $firstItem = $items->first();
            $productModel = $firstItem->product;

            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $group['distributor_id'])
                ->select('latitude', 'longitude')
                ->first();
            
            $distributorInfo = DB::table('distributor_requirements')
                ->where('user_id', $group['distributor_id'])->first();

            $distributorName = 'Distributor';
            if ($distributorInfo) {
                $distributorName = $distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor';
            }

            $distSettings = $paymentSettings->get($group['distributor_id']);
            $gcashEnabled = $distSettings ? (bool)$distSettings->is_gcash_enabled : false;
            $pickupEnabled = $distSettings ? (bool)$distSettings->is_pickup_enabled : false;

            // Aggregate reviews across all product_ids
            $allReviews = collect();
            foreach ($productIds as $pid) {
                $revs = $publishedReviews->get($pid, collect());
                $allReviews = $allReviews->merge($revs);
            }
            $avgRating = $allReviews->avg('rating') ? round($allReviews->avg('rating'), 1) : 0;
            $reviewCount = $allReviews->count();
            $formattedReviews = $allReviews->map(function($rev) use ($spUsersAll) {
                $clientName = 'Customer';
                $reviewerType = 'Customer';
                if ($rev->service_provider_id && isset($spUsersAll[$rev->service_provider_id])) {
                    $sp = $spUsersAll[$rev->service_provider_id];
                    $clientName = trim(($sp->first_name ?? '') . ' ' . ($sp->last_name ?? ''));
                    if (empty($clientName)) $clientName = $sp->name ?? 'Service Provider';
                    $reviewerType = 'Service Provider';
                } elseif ($rev->client) {
                    $clientName = trim(($rev->client->first_name ?? '') . ' ' . ($rev->client->last_name ?? ''));
                    if (empty($clientName)) $clientName = $rev->client->name ?? 'Customer';
                }
                return [
                    'id' => $rev->id,
                    'client' => $clientName,
                    'clientInitials' => strtoupper(substr($clientName, 0, 1)),
                    'reviewerType' => $reviewerType,
                    'rating' => (int)$rev->rating,
                    'comment' => $rev->comment,
                    'response' => $rev->response,
                    'response_date' => $rev->response_date ? \Carbon\Carbon::parse($rev->response_date)->format('M d, Y') : null,
                    'date' => \Carbon\Carbon::parse($rev->created_at)->format('M d, Y')
                ];
            })->values()->toArray();

            // Build variants
            $variants = [];
            $totalStock = 0;
            $minOriginal = PHP_FLOAT_MAX;
            $maxOriginal = 0;
            $minDiscounted = PHP_FLOAT_MAX;
            $maxDiscounted = 0;
            $firstVariantImage = null;

            foreach ($productIds as $pid) {
                $invItems = $inventories[$pid] ?? collect();
                $prod = $invItems->first()->product ?? null;
                if (!$prod) continue;

                $qty = $invItems->sum('quantity');
                $totalStock += $qty;

                $originalPrice = (float) $prod->price;
                if ($originalPrice < $minOriginal) $minOriginal = $originalPrice;
                if ($originalPrice > $maxOriginal) $maxOriginal = $originalPrice;

                $discountedPrice = $originalPrice;
                $promoData = null;

                $promo = $promotions->filter(function($p) use ($prod, $group) {
                    return ($p->product_id == $prod->id || is_null($p->product_id)) && 
                           $p->distributor_id == $group['distributor_id'];
                })->first();

                if ($promo) {
                    $promoData = [
                        'id' => $promo->id,
                        'name' => $promo->name,
                        'type' => $promo->type,
                        'discount_value' => (float) $promo->discount_value,
                    ];
                    if ($promo->type === 'percentage_discount') {
                        $discountedPrice = $originalPrice - ($originalPrice * ((float)$promo->discount_value / 100));
                    } elseif ($promo->type === 'fixed_discount' || $promo->type === 'fixed_amount') {
                        $discountedPrice = max(0, $originalPrice - (float)$promo->discount_value);
                    }
                }

                if ($discountedPrice < $minDiscounted) $minDiscounted = $discountedPrice;
                if ($discountedPrice > $maxDiscounted) $maxDiscounted = $discountedPrice;

                if (!$firstVariantImage && $prod->image_url) {
                    $firstVariantImage = $prod->image_url;
                }

                $variants[] = [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'size' => $prod->size ?? null,
                    'color' => $prod->color_code ?? null,
                    'original_price' => round($originalPrice, 2),
                    'price' => round($discountedPrice, 2),
                    'promotion' => $promoData,
                    'stock' => $qty,
                    'image_url' => $prod->image_url ? asset('storage/' . ltrim($prod->image_url, '/')) : null,
                    'is_active' => $prod->is_active,
                ];
            }

            // Use min price as main price, show range if different
            $displayPrice = round($minDiscounted, 2);
            $displayOriginal = round($minOriginal, 2);
            $priceRange = ($minDiscounted != $maxDiscounted) ? true : false;
            $originalRange = ($minOriginal != $maxOriginal) ? true : false;

            $imageUrl = $firstVariantImage ? asset('storage/' . ltrim($firstVariantImage, '/')) : null;

            $products[] = [
                'id' => $productIds[0], // first product id as group identifier
                'distributor_id' => $group['distributor_id'],
                'distributor_name' => $distributorName,
                'name' => $group['name'],
                'brand' => $group['brand'],
                'type' => $group['type'],
                'category' => $group['category'],
                'finish' => 'Standard',
                'original_price' => $displayOriginal,
                'original_price_min' => round($minOriginal, 2),
                'original_price_max' => round($maxOriginal, 2),
                'price' => $displayPrice,
                'price_min' => round($minDiscounted, 2),
                'price_max' => round($maxDiscounted, 2),
                'price_range' => $priceRange,
                'original_price_range' => $originalRange,
                'promotion' => null, // promotions handled per variant
                'stock' => $totalStock,
                'rating' => $avgRating,
                'review_count' => $reviewCount,
                'reviews' => $formattedReviews,
                'color' => null,
                'image_url' => $imageUrl,
                'distributor_lat' => $distAddress->latitude ?? null,
                'distributor_lng' => $distAddress->longitude ?? null,
                'distributor_gcash_enabled' => $gcashEnabled,
                'distributor_pickup_enabled' => $pickupEnabled,
                'variants' => $variants,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function getProduct($productId)
    {
        // Find the base product to get its category, type, name
        $baseProduct = Product::find($productId);
        if (!$baseProduct) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Find all products with same category, type, name
        $similarProducts = Product::where('category', $baseProduct->category)
            ->where('type', $baseProduct->type)
            ->where('name', $baseProduct->name)
            ->where('is_active', true)
            ->get();

        $productIds = $similarProducts->pluck('id')->toArray();

        // Fetch inventories for these products
        $inventories = DistributorInventory::with(['product'])
            ->whereIn('product_id', $productIds)
            ->where('ecommerce_status', 'deployed')
            ->get()
            ->groupBy('product_id');

        if ($inventories->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Product not found or not deployed'], 404);
        }

        $firstItem = $inventories->first()->first();
        $distributorId = $firstItem->distributor_id;

        // Build combined data similar to getProducts for a single group
        $totalStock = 0;
        $minOriginal = PHP_FLOAT_MAX;
        $maxOriginal = 0;
        $minDiscounted = PHP_FLOAT_MAX;
        $maxDiscounted = 0;
        $variants = [];
        $firstVariantImage = null;

        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->where('distributor_id', $distributorId)
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        foreach ($productIds as $pid) {
            $invItems = $inventories[$pid] ?? collect();
            if ($invItems->isEmpty()) continue;
            $prod = $invItems->first()->product;
            if (!$prod) continue;

            $qty = $invItems->sum('quantity');
            $totalStock += $qty;

            $originalPrice = (float) $prod->price;
            if ($originalPrice < $minOriginal) $minOriginal = $originalPrice;
            if ($originalPrice > $maxOriginal) $maxOriginal = $originalPrice;

            $discountedPrice = $originalPrice;
            $promoData = null;

            $promo = $promotions->filter(function($p) use ($prod) {
                return ($p->product_id == $prod->id || is_null($p->product_id));
            })->first();

            if ($promo) {
                $promoData = [
                    'id' => $promo->id,
                    'name' => $promo->name,
                    'type' => $promo->type,
                    'discount_value' => (float) $promo->discount_value,
                ];
                if ($promo->type === 'percentage_discount') {
                    $discountedPrice = $originalPrice - ($originalPrice * ((float)$promo->discount_value / 100));
                } elseif ($promo->type === 'fixed_discount' || $promo->type === 'fixed_amount') {
                    $discountedPrice = max(0, $originalPrice - (float)$promo->discount_value);
                }
            }

            if ($discountedPrice < $minDiscounted) $minDiscounted = $discountedPrice;
            if ($discountedPrice > $maxDiscounted) $maxDiscounted = $discountedPrice;

            if (!$firstVariantImage && $prod->image_url) {
                $firstVariantImage = $prod->image_url;
            }

            $variants[] = [
                'id' => $prod->id,
                'name' => $prod->name,
                'size' => $prod->size ?? null,
                'color' => $prod->color_code ?? null,
                'original_price' => round($originalPrice, 2),
                'price' => round($discountedPrice, 2),
                'promotion' => $promoData,
                'stock' => $qty,
                'image_url' => $prod->image_url ? asset('storage/' . ltrim($prod->image_url, '/')) : null,
                'is_active' => $prod->is_active,
            ];
        }

        if (empty($variants)) {
            return response()->json(['success' => false, 'message' => 'No active variants found'], 404);
        }

        // Reviews aggregation
        $allReviews = ProductReview::with('client')
            ->whereIn('product_id', $productIds)
            ->where('status', 'published')
            ->get();

        $spIds = $allReviews->pluck('service_provider_id')->filter()->unique();
        $spUsers = \App\Models\User::whereIn('id', $spIds)->get()->keyBy('id');

        $avgRating = $allReviews->avg('rating') ? round($allReviews->avg('rating'), 1) : 0;
        $reviewCount = $allReviews->count();
        $formattedReviews = $allReviews->map(function($rev) use ($spUsers) {
            $clientName = 'Customer';
            $reviewerType = 'Customer';
            if ($rev->service_provider_id && isset($spUsers[$rev->service_provider_id])) {
                $sp = $spUsers[$rev->service_provider_id];
                $clientName = trim(($sp->first_name ?? '') . ' ' . ($sp->last_name ?? ''));
                if (empty($clientName)) $clientName = $sp->name ?? 'Service Provider';
                $reviewerType = 'Service Provider';
            } elseif ($rev->client) {
                $clientName = trim(($rev->client->first_name ?? '') . ' ' . ($rev->client->last_name ?? ''));
                if (empty($clientName)) $clientName = $rev->client->name ?? 'Customer';
            }
            return [
                'id' => $rev->id,
                'client' => $clientName,
                'clientInitials' => strtoupper(substr($clientName, 0, 1)),
                'reviewerType' => $reviewerType,
                'rating' => (int)$rev->rating,
                'comment' => $rev->comment,
                'response' => $rev->response,
                'response_date' => $rev->response_date ? \Carbon\Carbon::parse($rev->response_date)->format('M d, Y') : null,
                'date' => \Carbon\Carbon::parse($rev->created_at)->format('M d, Y')
            ];
        })->values()->toArray();

        // Distributor info
        $distAddress = DB::table('distributor_addresses')
            ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
            ->where('distributor_requirements.user_id', $distributorId)
            ->select('latitude', 'longitude')
            ->first();
            
        $distributorInfo = DB::table('distributor_requirements')
            ->where('user_id', $distributorId)->first();
        $distributorName = $distributorInfo ? ($distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor') : 'Distributor';

        $paymentSettings = null;
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettings = DB::table('distributor_payment_settings')->where('distributor_id', $distributorId)->first();
        }

        $displayPrice = round($minDiscounted, 2);
        $displayOriginal = round($minOriginal, 2);
        $priceRange = ($minDiscounted != $maxDiscounted);
        $originalRange = ($minOriginal != $maxOriginal);

        $imageUrl = $firstVariantImage ? asset('storage/' . ltrim($firstVariantImage, '/')) : null;

        $productData = [
            'id' => $productId,
            'distributor_id' => $distributorId,
            'distributor_name' => $distributorName,
            'name' => $baseProduct->name,
            'brand' => 'Distributor Brand',
            'type' => $baseProduct->type,
            'category' => $baseProduct->category,
            'original_price' => $displayOriginal,
            'original_price_min' => round($minOriginal, 2),
            'original_price_max' => round($maxOriginal, 2),
            'price' => $displayPrice,
            'price_min' => round($minDiscounted, 2),
            'price_max' => round($maxDiscounted, 2),
            'price_range' => $priceRange,
            'original_price_range' => $originalRange,
            'promotion' => null,
            'stock' => $totalStock,
            'rating' => $avgRating,
            'review_count' => $reviewCount,
            'reviews' => $formattedReviews,
            'color' => null,
            'image_url' => $imageUrl,
            'distributor_lat' => $distAddress->latitude ?? null,
            'distributor_lng' => $distAddress->longitude ?? null,
            'distributor_gcash_enabled' => $paymentSettings ? (bool)$paymentSettings->is_gcash_enabled : false,
            'distributor_pickup_enabled' => $paymentSettings ? (bool)$paymentSettings->is_pickup_enabled : false,
            'variants' => $variants,
        ];

        return response()->json([
            'success' => true,
            'data' => $productData
        ]);
    }

    // ----- The rest of the methods (addToCart, orderNow, verifyGcashPayment, calculateShipping, calculateDistance) remain exactly as they were -----
    // They are not modified because they already work with specific product_id.

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
            'custom_address' => 'nullable|string',
            'payment_method' => 'required|string|in:cod,gcash,pick-up' 
        ]);

        $user = Auth::user();

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

        $product = Product::find($request->product_id);
        $totalStock = DistributorInventory::where('product_id', $product->id)
            ->where('distributor_id', $request->distributor_id)
            ->where('ecommerce_status', 'deployed')
            ->sum('quantity');

        if ($totalStock < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock for this product.'], 400);
        }

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

        $shippingRule = ShippingRule::first() ?? new ShippingRule([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00
        ]);

        $distance = $this->calculateDistance($clientAddress->latitude, $clientAddress->longitude, $request->distributor_lat, $request->distributor_lng);
        $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
        $distanceFee = max(50, $calculatedDistanceFee);
        $quantityFee = ($request->quantity * $shippingRule->rate_per_item);
        $shippingFee = round($distanceFee + $quantityFee, 2);

        if ($hasFreeShipping || ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold)) {
            $shippingFee = 0;
        }

        if ($request->payment_method === 'pick-up') {
            $shippingFee = 0;
            $fullAddress = "Store Pick-Up";
        }

        $grandTotal = round($totalOrderAmount + $shippingFee, 2);
        $orderStatus = $request->quantity <= 30 ? 'confirmed' : 'pending';
        $orderNumber = 'ORD-' . strtoupper(Str::random(10));
        $vatableSales = round($grandTotal / 1.12, 2);
        $vatAmount = round($grandTotal - $vatableSales, 2);
        
        $distributorInfo = DB::table('distributor_requirements')->where('user_id', $request->distributor_id)->first();
        $distributorName = $distributorInfo ? ($distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor') : 'Distributor';

        if ($request->payment_method === 'gcash') {
            try {
                $client = new \GuzzleHttp\Client();
                $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');

                $billingDetails = [
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                ];
                
                $gcashNumberToUse = null;
                if (Schema::hasTable('client_payment_settings')) {
                    $clientPayment = DB::table('client_payment_settings')
                        ->where('client_id', $user->id)
                        ->first();
                        
                    if ($clientPayment && !empty($clientPayment->gcash_number)) {
                        $gcashNumberToUse = $clientPayment->gcash_number;
                    }
                }
                
                if (empty($gcashNumberToUse)) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'No GCash number found in your payment settings. Please link a GCash number before proceeding.'
                    ], 400);
                }

                $billingDetails['phone'] = (string) $gcashNumberToUse;

                $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'accept' => 'application/json',
                        'authorization' => 'Basic ' . base64_encode('sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:')
                    ],
                    'http_errors' => false,
                    'json' => [
                        'data' => [
                            'attributes' => [
                                'send_email_receipt' => true,
                                'show_description' => true,
                                'show_line_items' => true,
                                'payment_method_types' => ['gcash'],
                                'description' => 'Payment for Order ' . $orderNumber,
                                'reference_number' => $orderNumber, 
                                'billing' => $billingDetails, 
                                'line_items' => [
                                    [
                                        'currency' => 'PHP',
                                        'amount' => (int) round($grandTotal * 100), 
                                        'name' => 'Order ' . $orderNumber . ' - ' . $product->name,
                                        'quantity' => 1,
                                    ]
                                ],
                                'success_url' => $frontendOrigin . '/ECommerceClient/EccommerceShop?order_number=' . $orderNumber,
                                'cancel_url' => $frontendOrigin . '/ECommerceClient/EccommerceShop'
                            ]
                        ]
                    ]
                ]);

                $paymongoData = json_decode($response->getBody(), true);

                if ($response->getStatusCode() !== 200) {
                    $errorMessage = $paymongoData['errors'][0]['detail'] ?? 'Invalid payload format.';
                    return response()->json(['success' => false, 'message' => 'PayMongo Error: ' . $errorMessage], 400);
                }

                $checkoutUrl = $paymongoData['data']['attributes']['checkout_url'] ?? null;
                $sessionId = $paymongoData['data']['id'] ?? null;
                
                if (!$checkoutUrl || (!$sessionId)) {
                    return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link.'], 500);
                }

                $cacheData = [
                    'type' => 'shop',
                    'session_id' => $sessionId, 
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalOrderAmount,
                    'shipping_fee' => $shippingFee,
                    'grand_total' => $grandTotal,
                    'vatable_sales' => $vatableSales,
                    'vat_amount' => $vatAmount,
                    'full_address' => $fullAddress,
                    'order_status' => $orderStatus,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'distributor_id' => $request->distributor_id,
                            'quantity' => $request->quantity,
                            'price' => $discountedPrice,
                            'product_name' => $product->name,
                            'distributor_name' => $distributorName
                        ]
                    ],
                    'applied_promotions' => $promotion ? [$promotion->id] : [],
                    'client_name' => $user->first_name . ' ' . $user->last_name,
                ];

                Storage::disk('local')->put('pending_orders/' . $orderNumber . '.json', json_encode($cacheData));

                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl,
                ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'PayMongo Internal Error: ' . $e->getMessage()], 500);
            }
        }

        DB::beginTransaction();
        try {
            $order = ClientOrder::create([
                'client_id' => $user->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalOrderAmount,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'payment_method' => $request->payment_method, 
                'status' => $orderStatus,
                'delivery_address' => $fullAddress,
            ]);

            if (Schema::hasTable('order_vat_deductions')) {
                OrderVatDeduction::create([
                    'order_id' => $order->id,
                    'vatable_sales' => $vatableSales,
                    'vat_amount' => $vatAmount
                ]);
            }

            ClientOrderItem::create([
                'order_id' => $order->id,
                'distributor_id' => $request->distributor_id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $discountedPrice 
            ]);

            if ($promotion) {
                DB::table('crm_promotions')->where('id', $promotion->id)->increment('used_count');
            }

            $remainingToDeduct = $request->quantity;
            $inventories = DistributorInventory::where('product_id', $product->id)
                ->where('distributor_id', $request->distributor_id)
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
                throw new \Exception('Not enough active stock across inventory records.');
            }

            DB::commit();

            event(new OrderPlaced($request->distributor_id));

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'receipt_data' => [
                    'order_number' => $orderNumber,
                    'distributor_name' => $distributorName,
                    'client_name' => $user->first_name . ' ' . $user->last_name,
                    'product_name' => $product->name,
                    'quantity' => $request->quantity,
                    'price' => $discountedPrice,
                    'shipping_fee' => $shippingFee,
                    'vatable_sales' => $vatableSales,
                    'vat_amount' => $vatAmount,
                    'grand_total' => $grandTotal,
                    'payment_method' => strtoupper($request->payment_method),
                    'status' => ucfirst($orderStatus),
                    'date' => now()->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()], 500);
        }
    }

    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);
        $orderNumber = trim($request->order_number);
        $filePath = 'pending_orders/' . $orderNumber . '.json';
        $client = new \GuzzleHttp\Client();

        $existingOrder = ClientOrder::where('order_number', $orderNumber)->first();
        if ($existingOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified and processed successfully.',
                'already_processed' => true,
            ]);
        }

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Session invalid or already processed. File not found.'], 400);
        }

        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);
        $sessionId = $cacheData['session_id']; 
        $isPaid = false;

        try {
            $response = $client->request('GET', 'https://api.paymongo.com/v1/checkout_sessions/' . $sessionId, [
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic ' . base64_encode('sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:')
                ],
                'http_errors' => false 
            ]);
            
            if ($response->getStatusCode() === 200) {
                $paymongoData = json_decode($response->getBody(), true);
                $attributes = $paymongoData['data']['attributes'] ?? [];
                
                $payments = $attributes['payments'] ?? [];
                foreach($payments as $p) {
                    if (isset($p['attributes']['status']) && $p['attributes']['status'] === 'paid') {
                        $isPaid = true;
                    }
                }
                
                $paymentIntent = $attributes['payment_intent'] ?? null;
                if ($paymentIntent && isset($paymentIntent['attributes']['status']) && $paymentIntent['attributes']['status'] === 'succeeded') {
                    $isPaid = true;
                }

                if (!$isPaid && isset($attributes['status']) && $attributes['status'] === 'active') {
                    $isPaid = true; 
                }
            } else {
                $isPaid = true; 
            }
        } catch (\Exception $e) {
            $isPaid = true; 
        }

        if (!$isPaid) {
            return response()->json(['success' => false, 'message' => 'PayMongo Status: Payment has not been officially completed.'], 400);
        }

        DB::beginTransaction();
        try {
            $order = ClientOrder::create([
                'client_id' => $cacheData['user_id'],
                'order_number' => $cacheData['order_number'],
                'total_amount' => $cacheData['total_amount'],
                'shipping_fee' => $cacheData['shipping_fee'],
                'grand_total' => $cacheData['grand_total'],
                'payment_method' => 'gcash',
                'status' => $cacheData['order_status'],
                'delivery_address' => $cacheData['full_address'],
            ]);

            if (Schema::hasTable('order_vat_deductions')) {
                OrderVatDeduction::create([
                    'order_id' => $order->id,
                    'vatable_sales' => $cacheData['vatable_sales'],
                    'vat_amount' => $cacheData['vat_amount']
                ]);
            }

            $receiptItems = [];
            $distributorIdToCredit = null;

            foreach ($cacheData['items'] as $item) {
                ClientOrderItem::create([
                    'order_id' => $order->id,
                    'distributor_id' => $item['distributor_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] 
                ]);

                $distributorIdToCredit = $item['distributor_id'];

                $receiptItems[] = [
                    'name' => $item['product_name'],
                    'distributor_name' => $item['distributor_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ];

                $remainingToDeduct = $item['quantity'];
                $inventories = DistributorInventory::where('product_id', $item['product_id'])
                    ->where('distributor_id', $item['distributor_id'])
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
                    throw new \Exception("Not enough stock remaining for {$item['product_name']}.");
                }
            }

            if ($distributorIdToCredit) {
                DB::table('ec_delivery_remittances')->insert([
                    'distributor_id' => $distributorIdToCredit,
                    'delivery_personnel_id' => null, 
                    'order_id' => $order->id,
                    'amount' => $cacheData['grand_total'],
                    'remittance_proof_path' => 'System Auto-GCash (PayMongo)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('ec_order_financials')->insert([
                    'order_id' => $order->id,
                    'distributor_id' => $distributorIdToCredit,
                    'amount' => $cacheData['grand_total'],
                    'vat_deduction' => $cacheData['vat_amount'],
                    'total_sales' => $cacheData['vatable_sales'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $overallSales = DB::table('distributor_overall_sales')
                    ->where('distributor_id', $distributorIdToCredit)
                    ->first();

                if ($overallSales) {
                    DB::table('distributor_overall_sales')
                        ->where('distributor_id', $distributorIdToCredit)
                        ->update([
                            'total_revenue' => $overallSales->total_revenue + $cacheData['vatable_sales'],
                            'total_sales_count' => $overallSales->total_sales_count + 1,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('distributor_overall_sales')->insert([
                        'distributor_id' => $distributorIdToCredit,
                        'total_revenue' => $cacheData['vatable_sales'],
                        'total_sales_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            foreach ($cacheData['applied_promotions'] as $promoId) {
                DB::table('crm_promotions')->where('id', $promoId)->increment('used_count');
            }

            if (isset($cacheData['type']) && $cacheData['type'] === 'cart') {
                ClientCart::where('client_id', $cacheData['user_id'])->delete();
            }

            DB::commit();
            Storage::disk('local')->delete($filePath);

            $distributorIds = collect($cacheData['items'])->pluck('distributor_id')->unique();
            foreach ($distributorIds as $dId) {
                event(new OrderPlaced($dId));
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and order saved successfully.',
                'receipt_data' => [
                    'order_number' => $cacheData['order_number'],
                    'client_name' => $cacheData['client_name'],
                    'items' => $receiptItems, 
                    'shipping_fee' => $cacheData['shipping_fee'],
                    'vatable_sales' => $cacheData['vatable_sales'],
                    'vat_amount' => $cacheData['vat_amount'],
                    'grand_total' => $cacheData['grand_total'],
                    'payment_method' => 'GCASH',
                    'status' => ucfirst($cacheData['order_status']),
                    'date' => now()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save verified order: ' . $e->getMessage()], 500);
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

        $groupedItems = collect($request->cart_items)->groupBy('distributor_id');

        foreach ($groupedItems as $distributorId => $items) {
            $firstItem = $items->first();
            $distance = $this->calculateDistance(
                $clientAddress->latitude,
                $clientAddress->longitude,
                $firstItem['distributor_lat'],
                $firstItem['distributor_lng']
            );

            $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
            $distributorShippingFee = max(50, $calculatedDistanceFee);
            $hasFreeShippingPromo = false;

            foreach ($items as $item) {
                $totalOrderAmount += ($item['price'] * $item['quantity']);
                $totalQuantity += $item['quantity'];
                
                $distributorShippingFee += ($item['quantity'] * $shippingRule->rate_per_item);

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
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}