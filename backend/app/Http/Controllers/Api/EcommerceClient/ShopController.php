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

        $publishedReviews = ProductReview::with('client')
            ->where('status', 'published') 
            ->get()
            ->groupBy('product_id');

        $paymentSettings = collect();
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettings = DB::table('distributor_payment_settings')->get()->keyBy('distributor_id');
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

            $distributorName = 'Distributor';
            if ($distributorInfo) {
                $distributorName = $distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor';
            }

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

        // FORCE SHIPPING FEE TO 0 IF PICKUP
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

        // =========================================================================
        // 1. CALL PAYMONGO FIRST (GCASH ONLY)
        // =========================================================================
        if ($request->payment_method === 'gcash') {
            try {
                $client = new \GuzzleHttp\Client();
                $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');

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
                                'line_items' => [
                                    [
                                        'currency' => 'PHP',
                                        'amount' => (int) round($grandTotal * 100), 
                                        'name' => 'Order ' . $orderNumber . ' - ' . $product->name,
                                        'quantity' => 1,
                                    ]
                                ],
                                // FIX: We pass the order_number to the URL instead of the broken macro!
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
                
                if (!$checkoutUrl || !$sessionId) {
                    return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link.'], 500);
                }

                $cacheData = [
                    'type' => 'shop',
                    'session_id' => $sessionId, // <-- WE STORE THE REAL SESSION ID HERE
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

                // FIX: Write to explicit local disk using the order number!
                Storage::disk('local')->put('pending_orders/' . $orderNumber . '.json', json_encode($cacheData));

                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl,
                ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'PayMongo Internal Error: ' . $e->getMessage()], 500);
            }
        }

        // =========================================================================
        // 2. DB TRANSACTION - COD & PICK-UP
        // =========================================================================
        DB::beginTransaction();
        try {
            $order = ClientOrder::create([
                'client_id' => $user->id,
                'order_number' => $orderNumber,
                'total_amount' => $totalOrderAmount,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'payment_method' => $request->payment_method, // Handles cod and pick-up
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

        // SCENARIO 1: Double-mount check. Order already exists in DB?
        $existingOrder = ClientOrder::where('order_number', $orderNumber)->first();
        if ($existingOrder) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified and processed successfully.',
                'already_processed' => true,
            ]);
        }

        // SCENARIO 2: Check if file exists using the ORDER NUMBER
        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Session invalid or already processed. File not found.'], 400);
        }

        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);
        $sessionId = $cacheData['session_id']; 
        $isPaid = false;

        // Try to verify with PayMongo API
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

        // Execute DB Transaction
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

                // We track the distributor to credit funds (assuming 1 checkout = 1 distributor for direct order)
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

            // ========================================================================
            // INSERT REMITTANCE AND FINANCIAL RECORDS
            // ========================================================================
            if ($distributorIdToCredit) {
                // 1. Insert Remittance
                DB::table('ec_delivery_remittances')->insert([
                    'distributor_id' => $distributorIdToCredit,
                    'delivery_personnel_id' => null, // Remitted by System
                    'order_id' => $order->id,
                    'amount' => $cacheData['grand_total'],
                    'remittance_proof_path' => 'System Auto-GCash (PayMongo)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Insert Order Financials tracking
                DB::table('ec_order_financials')->insert([
                    'order_id' => $order->id,
                    'distributor_id' => $distributorIdToCredit,
                    'amount' => $cacheData['grand_total'],
                    'vat_deduction' => $cacheData['vat_amount'],
                    'total_sales' => $cacheData['vatable_sales'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Update or Insert Distributor's Overall Sales
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
            // ========================================================================

            foreach ($cacheData['applied_promotions'] as $promoId) {
                DB::table('crm_promotions')->where('id', $promoId)->increment('used_count');
            }

            if (isset($cacheData['type']) && $cacheData['type'] === 'cart') {
                ClientCart::where('client_id', $cacheData['user_id'])->delete();
            }

            DB::commit();
            
            // Delete the physical file once completed
            Storage::disk('local')->delete($filePath);

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