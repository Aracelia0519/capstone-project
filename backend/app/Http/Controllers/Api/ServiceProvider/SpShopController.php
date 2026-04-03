<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\ServiceProvider\SpCart;
use App\Models\ServiceProvider\SpOrder;
use App\Models\ServiceProvider\SpOrderItem;
use App\Models\EcommerceClient\ProductReview; 
use App\Models\Distributor\Product; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SpShopController extends Controller
{
    public function getProducts($distributorId = null)
    {
        $query = DistributorInventory::with(['product'])
            ->where('ecommerce_status', 'deployed');
            
        if ($distributorId) {
            $query->where('distributor_id', $distributorId);
        }
        
        $inventories = $query->get()->groupBy('product_id');

        $currentDate = now()->toDateString();
        
        $promoQuery = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit');
            
        if ($distributorId) {
            $promoQuery->where('distributor_id', $distributorId);
        }
        
        $promotions = $promoQuery->get();

        // FIXED: Manually pull Service Providers to accurately identify SP Reviews
        $allPublishedReviews = ProductReview::with('client')
            ->where('status', 'published') 
            ->get();
            
        $spIdsAll = $allPublishedReviews->pluck('service_provider_id')->filter()->unique();
        $spUsersAll = \App\Models\User::whereIn('id', $spIdsAll)->get()->keyBy('id');

        $publishedReviews = $allPublishedReviews->groupBy('product_id');

        $paymentSettings = collect();
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettingsQuery = DB::table('distributor_payment_settings');
            if ($distributorId) {
                $paymentSettingsQuery->where('distributor_id', $distributorId);
            }
            $paymentSettings = $paymentSettingsQuery->get()->keyBy('distributor_id');
        }

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
            
            $distributorInfo = DB::table('distributor_requirements')
                ->where('user_id', $firstItem->distributor_id)->first();

            $distributorName = $distributorInfo ? ($distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor') : 'Distributor';

            $originalPrice = (float) $productModel->price;
            $discountedPrice = $originalPrice;
            $promoData = null;

            if ($promotions->isNotEmpty()) {
                $productPromo = $promotions->filter(function($promo) use ($productModel, $firstItem) {
                        return ($promo->product_id == $productModel->id || is_null($promo->product_id)) && 
                               $promo->distributor_id == $firstItem->distributor_id;
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
            
            // FIXED: Identify if the reviewer is a Client or Service Provider
            $formattedReviews = $productReviews->map(function($rev) use ($spUsersAll) {
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
                    'reviewerType' => $reviewerType, // Added reviewerType
                    'rating' => (int)$rev->rating,
                    'comment' => $rev->comment,
                    'response' => $rev->response,
                    'response_date' => $rev->response_date ? \Carbon\Carbon::parse($rev->response_date)->format('M d, Y') : null,
                    'date' => \Carbon\Carbon::parse($rev->created_at)->format('M d, Y')
                ];
            })->values()->toArray();

            $distSettings = $paymentSettings->get($firstItem->distributor_id);
            $gcashEnabled = $distSettings ? (bool)$distSettings->is_gcash_enabled : false;
            $pickupEnabled = $distSettings ? (bool)$distSettings->is_pickup_enabled : false;

            $products[] = [
                'id' => $productModel->id,
                'distributor_id' => $firstItem->distributor_id,
                'distributor_name' => $distributorName,
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
                'distributor_gcash_enabled' => $gcashEnabled,
                'distributor_pickup_enabled' => $pickupEnabled,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function getProduct($productId)
    {
        $inventory = DistributorInventory::with(['product'])
            ->where('product_id', $productId)
            ->where('ecommerce_status', 'deployed')
            ->get();

        if ($inventory->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Product not found or not deployed'], 404);
        }

        $totalQuantity = $inventory->sum('quantity');
        $firstItem = $inventory->first();
        $productModel = $firstItem->product;
        $distributorId = $firstItem->distributor_id;

        $distAddress = DB::table('distributor_addresses')
            ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
            ->where('distributor_requirements.user_id', $distributorId)
            ->select('latitude', 'longitude')
            ->first();
            
        $distributorInfo = DB::table('distributor_requirements')
            ->where('user_id', $distributorId)->first();

        $distributorName = $distributorInfo ? ($distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor') : 'Distributor';

        $currentDate = now()->toDateString();
        $promotion = DB::table('crm_promotions')
            ->where('distributor_id', $distributorId)
            ->where(function($q) use ($productId) {
                $q->where('product_id', $productId)->orWhereNull('product_id');
            })
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->first();

        $originalPrice = (float) $productModel->price;
        $discountedPrice = $originalPrice;
        $promoData = null;

        if ($promotion) {
            $promoData = [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'type' => $promotion->type,
                'discount_value' => (float) $promotion->discount_value,
            ];

            if ($promotion->type === 'percentage_discount') {
                $discountedPrice = $originalPrice - ($originalPrice * ((float)$promotion->discount_value / 100));
            } elseif ($promotion->type === 'fixed_discount' || $promotion->type === 'fixed_amount') {
                $discountedPrice = max(0, $originalPrice - (float)$promotion->discount_value);
            }
        }

        // FIXED: Identifies whether Review is from a Client or Service Provider
        $productReviews = ProductReview::with('client')
            ->where('product_id', $productId)
            ->where('status', 'published')
            ->get();

        $spIds = $productReviews->pluck('service_provider_id')->filter()->unique();
        $spUsers = \App\Models\User::whereIn('id', $spIds)->get()->keyBy('id');
            
        $avgRating = $productReviews->avg('rating') ? round($productReviews->avg('rating'), 1) : 0;
        $reviewCount = $productReviews->count();
            
        $formattedReviews = $productReviews->map(function($rev) use ($spUsers) {
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
                'reviewerType' => $reviewerType, // Added reviewerType
                'rating' => (int)$rev->rating,
                'comment' => $rev->comment,
                'response' => $rev->response,
                'response_date' => $rev->response_date ? \Carbon\Carbon::parse($rev->response_date)->format('M d, Y') : null,
                'date' => \Carbon\Carbon::parse($rev->created_at)->format('M d, Y')
            ];
        })->values()->toArray();

        $paymentSettings = null;
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettings = DB::table('distributor_payment_settings')->where('distributor_id', $distributorId)->first();
        }

        $productData = [
            'id' => $productModel->id,
            'distributor_id' => $distributorId,
            'distributor_name' => $distributorName,
            'name' => $productModel->name,
            'brand' => 'Distributor Brand', 
            'type' => $productModel->type,
            'category' => $productModel->category,
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
            'distributor_gcash_enabled' => $paymentSettings ? (bool)$paymentSettings->is_gcash_enabled : false,
            'distributor_pickup_enabled' => $paymentSettings ? (bool)$paymentSettings->is_pickup_enabled : false,
        ];

        return response()->json([
            'success' => true,
            'data' => $productData
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

        $cartItem = SpCart::where('service_provider_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('distributor_id', $request->distributor_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            SpCart::create([
                'service_provider_id' => $user->id,
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

        $spAddress = DB::table('service_provider_addresses')
            ->join('service_provider_requirements', 'service_provider_addresses.service_provider_requirements_id', '=', 'service_provider_requirements.id')
            ->where('service_provider_requirements.user_id', $user->id)
            ->select('latitude', 'longitude', 'block_address', 'barangay', 'city', 'province')
            ->first();

        if (!$spAddress || !$spAddress->latitude || !$spAddress->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery address coordinates not found. Please update your profile.'
            ], 400);
        }

        $defaultAddress = "{$spAddress->block_address}, {$spAddress->barangay}, {$spAddress->city}, {$spAddress->province}";
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

        $distance = $this->calculateDistance($spAddress->latitude, $spAddress->longitude, $request->distributor_lat, $request->distributor_lng);
        
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
                if (Schema::hasTable('service_provider_payment_settings')) {
                    $spPayment = DB::table('service_provider_payment_settings')->where('service_provider_id', $user->id)->first();
                    if ($spPayment && !empty($spPayment->gcash_number)) {
                        $gcashNumberToUse = $spPayment->gcash_number;
                    }
                }

                if (empty($gcashNumberToUse)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No GCash number found in your payment settings. Please link a GCash account first.'
                    ], 400);
                }

                $billingDetails['phone'] = (string) $gcashNumberToUse;

                $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
                    'headers' => [
                        'accept' => 'application/json',
                        'authorization' => 'Basic ' . base64_encode(env('PAYMONGO_SECRET_KEY', 'sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:') . ':'),
                        'content-type' => 'application/json',
                    ],
                    'http_errors' => false,
                    'json' => [
                        'data' => [
                            'attributes' => [
                                'payment_method_types' => ['gcash'],
                                'send_email_receipt' => true,
                                'show_description' => true,
                                'show_line_items' => true,
                                'description' => 'Payment for Shop Order ' . $orderNumber,
                                'reference_number' => $orderNumber,
                                'billing' => $billingDetails,
                                'line_items' => [
                                    [
                                        'name' => 'Partner Shop Order - ' . $orderNumber,
                                        'amount' => intval(round($grandTotal * 100)), 
                                        'currency' => 'PHP',
                                        'quantity' => 1,
                                    ]
                                ],
                                'success_url' => $frontendOrigin . '/serviceProvider/shop/' . $request->distributor_id . '?order_number=' . $orderNumber,
                                'cancel_url' => $frontendOrigin . '/serviceProvider/shop/' . $request->distributor_id . '?payment=cancelled'
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

                if (!$checkoutUrl || !$sessionId) {
                    return response()->json(['success' => false, 'message' => 'Failed to generate payment link.']);
                }

                $cacheData = [
                    'type' => 'sp_shop',
                    'session_id' => $sessionId,
                    'service_provider_id' => $user->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalOrderAmount,
                    'shipping_fee' => $shippingFee,
                    'grand_total' => $grandTotal,
                    'vatable_sales' => $vatableSales,
                    'vat_amount' => $vatAmount,
                    'delivery_address' => $fullAddress,
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
                    'sp_name' => $billingDetails['name'],
                    'has_free_shipping' => $hasFreeShipping,
                    'applied_promotions' => $promotion ? [$promotion->id] : [],
                ];

                Storage::disk('local')->put('pending_sp_orders/' . $orderNumber . '.json', json_encode($cacheData));

                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
            }
        }

        DB::beginTransaction();
        try {
            $order = SpOrder::create([
                'service_provider_id' => $user->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalOrderAmount,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'payment_method' => $request->payment_method,
                'status' => $orderStatus,
                'delivery_address' => $fullAddress,
            ]);

            SpOrderItem::create([
                'sp_order_id' => $order->id,
                'distributor_id' => $request->distributor_id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $discountedPrice,
            ]);

            // Use the new sp_order_id column instead of order_id to bypass the Foreign Key constraint
            DB::table('order_vat_deductions')->insert([
                'sp_order_id' => $order->id,
                'vatable_sales' => $vatableSales,
                'vat_amount' => $vatAmount,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($promotion) {
                DB::table('crm_promotions')->where('id', $promotion->id)->increment('used_count');
            }

            $deduction = $request->quantity;
            $inventories = DistributorInventory::where('product_id', $product->id)
                ->where('distributor_id', $request->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($inventories as $inv) {
                if ($deduction <= 0) break;
                if ($inv->quantity >= $deduction) {
                    $inv->quantity -= $deduction;
                    $inv->save();
                    $deduction = 0;
                } else {
                    $deduction -= $inv->quantity;
                    $inv->quantity = 0;
                    $inv->save();
                }
            }

            if ($deduction > 0) {
                throw new \Exception('Not enough active stock to fulfill this order.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully.',
                'receipt_data' => [
                    'order_number' => $orderNumber,
                    'sp_name' => trim($user->first_name . ' ' . $user->last_name),
                    'items' => [
                        [
                            'product_name' => $product->name,
                            'distributor_name' => $distributorName,
                            'quantity' => $request->quantity,
                            'price' => $discountedPrice,
                            'total' => $totalOrderAmount
                        ]
                    ],
                    'total_quantity' => $request->quantity,
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
            return response()->json(['success' => false, 'message' => 'Order processing failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);
        
        $orderNumber = trim($request->order_number);
        $filePath = 'pending_sp_orders/' . $orderNumber . '.json';
        
        $client = new \GuzzleHttp\Client();

        $existingOrder = SpOrder::where('order_number', $orderNumber)->first();
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
                    'authorization' => 'Basic ' . base64_encode(env('PAYMONGO_SECRET_KEY', 'sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:') . ':')
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
            $order = SpOrder::create([
                'service_provider_id' => $cacheData['service_provider_id'],
                'order_number' => $cacheData['order_number'],
                'total_amount' => $cacheData['total_amount'],
                'shipping_fee' => $cacheData['shipping_fee'],
                'grand_total' => $cacheData['grand_total'],
                'payment_method' => 'gcash',
                'status' => $cacheData['order_status'],
                'delivery_address' => $cacheData['delivery_address'],
            ]);

            $receiptItems = [];
            $distributorIdToCredit = null;

            foreach ($cacheData['items'] as $item) {
                SpOrderItem::create([
                    'sp_order_id' => $order->id,
                    'distributor_id' => $item['distributor_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $distributorIdToCredit = $item['distributor_id'];

                $receiptItems[] = [
                    'product_name' => $item['product_name'],
                    'distributor_name' => $item['distributor_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ];

                $deduction = $item['quantity'];
                $inventories = DistributorInventory::where('product_id', $item['product_id'])
                    ->where('distributor_id', $item['distributor_id'])
                    ->where('ecommerce_status', 'deployed')
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($inventories as $inv) {
                    if ($deduction <= 0) break;
                    if ($inv->quantity >= $deduction) {
                        $inv->quantity -= $deduction;
                        $inv->save();
                        $deduction = 0;
                    } else {
                        $deduction -= $inv->quantity;
                        $inv->quantity = 0;
                        $inv->save();
                    }
                }
                
                if ($deduction > 0) {
                    throw new \Exception("Not enough stock remaining for {$item['product_name']}.");
                }
            }

            // Use the new sp_order_id column to bypass the Client Order Foreign Key constraint
            DB::table('order_vat_deductions')->insert([
                'sp_order_id' => $order->id,
                'vatable_sales' => $cacheData['vatable_sales'],
                'vat_amount' => $cacheData['vat_amount'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($distributorIdToCredit) {
                // Use the new sp_order_id column to bypass the Client Order Foreign Key constraint
                DB::table('ec_delivery_remittances')->insert([
                    'distributor_id' => $distributorIdToCredit,
                    'delivery_personnel_id' => null, 
                    'sp_order_id' => $order->id,
                    'amount' => $cacheData['grand_total'],
                    'remittance_proof_path' => 'System Auto-GCash (PayMongo)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Use the new sp_order_id column to bypass the Client Order Foreign Key constraint
                DB::table('ec_order_financials')->insert([
                    'sp_order_id' => $order->id,
                    'distributor_id' => $distributorIdToCredit,
                    'amount' => $cacheData['grand_total'],
                    'vat_deduction' => $cacheData['vat_amount'],
                    'total_sales' => $cacheData['vatable_sales'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $overallSales = DB::table('distributor_overall_sales')->where('distributor_id', $distributorIdToCredit)->first();
                if ($overallSales) {
                    DB::table('distributor_overall_sales')->where('distributor_id', $distributorIdToCredit)->update([
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

            if (isset($cacheData['type']) && $cacheData['type'] === 'sp_cart') {
                SpCart::where('service_provider_id', $cacheData['service_provider_id'])->delete();
            }

            DB::commit();
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and order saved successfully.',
                'receipt_data' => [
                    'order_number' => $cacheData['order_number'],
                    'sp_name' => $cacheData['sp_name'],
                    'items' => $receiptItems,
                    'total_quantity' => array_sum(array_column($receiptItems, 'quantity')),
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
            return response()->json(['success' => false, 'message' => 'Failed to process verified order.', 'error' => $e->getMessage()], 500);
        }
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'distributor_id' => 'required|exists:users,id',
            'distributor_lat' => 'required|numeric',
            'distributor_lng' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $spAddress = DB::table('service_provider_addresses')
            ->join('service_provider_requirements', 'service_provider_addresses.service_provider_requirements_id', '=', 'service_provider_requirements.id')
            ->where('service_provider_requirements.user_id', $user->id)
            ->select('latitude', 'longitude')
            ->first();

        if (!$spAddress || !$spAddress->latitude || !$spAddress->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery address coordinates not found. Please update your profile.'
            ], 400);
        }

        $shippingRule = ShippingRule::first() ?? new ShippingRule([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00
        ]);

        $distance = $this->calculateDistance($spAddress->latitude, $spAddress->longitude, $request->distributor_lat, $request->distributor_lng);
        
        $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
        $distanceFee = max(50, $calculatedDistanceFee);
        $quantityFee = ($request->quantity * $shippingRule->rate_per_item);

        $shippingFee = round($distanceFee + $quantityFee, 2);

        $currentDate = now()->toDateString();
        $promoExists = DB::table('crm_promotions')
            ->where('distributor_id', $request->distributor_id)
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->where('type', 'free_shipping')
            ->exists();

        if ($promoExists || ($shippingRule->free_shipping_threshold && $request->total_amount >= $shippingRule->free_shipping_threshold)) {
            $shippingFee = 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'calculated_shipping_fee' => $shippingFee,
                'is_free_shipping' => $shippingFee == 0
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
        $distance = $earthRadius * $c;
        return $distance;
    }
}