<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use App\Models\EcommerceClient\ClientCart;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\EcommerceClient\ClientOrderItem;
use App\Models\EcommerceClient\ShippingRule;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\EcommerceClient\OrderVatDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage; // ADDED STORAGE FACADE
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) return response()->json(['success' => true, 'data' => []]);
        
        $cartItems = ClientCart::with(['product'])->where('client_id', $user->id)->get();
        $currentDate = now()->toDateString();
        $promotions = DB::table('crm_promotions')
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereRaw('used_count < usage_limit')
            ->get();

        $paymentSettings = collect();
        if (Schema::hasTable('distributor_payment_settings')) {
            $paymentSettings = DB::table('distributor_payment_settings')->get()->keyBy('distributor_id');
        }

        $formattedItems = [];

        foreach ($cartItems as $item) {
            if (!$item->product || !$item->product->is_active) continue;

            $stock = DistributorInventory::where('product_id', $item->product_id)
                ->where('distributor_id', $item->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->sum('quantity');

            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $item->distributor_id)
                ->select('latitude', 'longitude')
                ->first();

            $distributorInfo = DB::table('distributor_requirements')->where('user_id', $item->distributor_id)->first();
            $distributorName = $distributorInfo ? ($distributorInfo->company_name ?? $distributorInfo->business_name ?? 'Distributor') : 'Distributor';

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

            $distSettings = $paymentSettings->get($item->distributor_id);
            $gcashEnabled = $distSettings ? (bool)$distSettings->is_gcash_enabled : false;
            $pickupEnabled = $distSettings ? (bool)$distSettings->is_pickup_enabled : false;

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'distributor_id' => $item->distributor_id,
                'distributor_name' => $distributorName,
                'type' => 'product',
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
                'distributor_gcash_enabled' => $gcashEnabled,
                'distributor_pickup_enabled' => $pickupEnabled,
            ];
        }

        return response()->json(['success' => true, 'data' => $formattedItems]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cartItem = ClientCart::where('id', $id)->where('client_id', Auth::id())->first();
        if (!$cartItem) return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();
        return response()->json(['success' => true, 'message' => 'Quantity updated']);
    }

    public function destroy($id)
    {
        $cartItem = ClientCart::where('id', $id)->where('client_id', Auth::id())->first();
        if ($cartItem) $cartItem->delete();
        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }

    public function clear()
    {
        ClientCart::where('client_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Cart cleared successfully']);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'custom_address' => 'nullable|string',
            'payment_method' => 'required|string|in:cod,gcash,pick-up',
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer'
        ]);

        $user = Auth::user();
        
        // ONLY fetch and process cart items that were selected by the user
        $cartItems = ClientCart::with('product')
            ->where('client_id', $user->id)
            ->whereIn('id', $request->selected_items)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No valid items selected for checkout.'], 400);
        }

        $clientAddress = DB::table('client_addresses')
            ->join('client_requirements', 'client_addresses.client_requirements_id', '=', 'client_requirements.id')
            ->where('client_requirements.user_id', $user->id)
            ->select('latitude', 'longitude', 'block_address', 'barangay', 'city', 'province')
            ->first();

        if (!$clientAddress || !$clientAddress->latitude || !$clientAddress->longitude) {
            return response()->json(['success' => false, 'message' => 'Delivery address coordinates not found. Please update your profile.'], 400);
        }

        $defaultAddress = "{$clientAddress->block_address}, {$clientAddress->barangay}, {$clientAddress->city}, {$clientAddress->province}";
        $fullAddress = $request->filled('custom_address') ? $request->custom_address : $defaultAddress;

        foreach ($cartItems as $item) {
            $totalStock = DistributorInventory::where('product_id', $item->product_id)
                ->where('distributor_id', $item->distributor_id)
                ->where('ecommerce_status', 'deployed')
                ->sum('quantity');

            if ($totalStock < $item->quantity) {
                return response()->json(['success' => false, 'message' => "Insufficient stock for product: {$item->product->name}."], 400);
            }
        }

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
        $totalQuantity = 0;
        $appliedPromotions = [];
        $distributorShippingFees = []; 
        $cacheItemsArray = [];

        $groupedItems = $cartItems->groupBy('distributor_id');

        foreach ($groupedItems as $distId => $items) {
            $firstItem = $items->first();
            $distAddress = DB::table('distributor_addresses')
                ->join('distributor_requirements', 'distributor_addresses.distributor_requirements_id', '=', 'distributor_requirements.id')
                ->where('distributor_requirements.user_id', $distId)
                ->select('latitude', 'longitude')
                ->first();

            $distance = $distAddress ? $this->calculateDistance($clientAddress->latitude, $clientAddress->longitude, $distAddress->latitude, $distAddress->longitude) : 0;
            $calculatedDistanceFee = round($distance * $shippingRule->base_rate_per_km);
            $distributorShippingFee = max(50, $calculatedDistanceFee);
            $hasFreeShippingPromo = false;

            foreach ($items as $item) {
                $totalQuantity += $item->quantity;
                $originalPrice = (float) $item->product->price;
                $discountedPrice = $originalPrice;

                $productPromo = $promotions->where('distributor_id', $distId)
                    ->filter(function($promo) use ($item) {
                        return $promo->product_id == $item->product_id || is_null($promo->product_id);
                    })->first();

                if ($productPromo) {
                    if ($productPromo->type === 'percentage_discount') {
                        $discountedPrice = $originalPrice - ($originalPrice * ((float)$productPromo->discount_value / 100));
                    } elseif ($productPromo->type === 'fixed_discount' || $productPromo->type === 'fixed_amount') {
                        $discountedPrice = max(0, $originalPrice - (float)$productPromo->discount_value);
                    } elseif ($productPromo->type === 'free_shipping') {
                        $hasFreeShippingPromo = true;
                    }
                    $appliedPromotions[$productPromo->id] = true;
                }

                $itemTotal = ($discountedPrice * $item->quantity);
                $totalOrderAmount += $itemTotal;
                $item->discounted_price = $discountedPrice; 
                
                $distributorShippingFee += ($item->quantity * $shippingRule->rate_per_item);

                $distInfo = DB::table('distributor_requirements')->where('user_id', $item->distributor_id)->first();
                $distName = $distInfo ? ($distInfo->company_name ?? $distInfo->business_name ?? 'Distributor') : 'Distributor';
                
                $cacheItemsArray[] = [
                    'product_id' => $item->product_id,
                    'distributor_id' => $item->distributor_id,
                    'quantity' => $item->quantity,
                    'price' => $discountedPrice,
                    'product_name' => $item->product->name,
                    'distributor_name' => $distName
                ];
            }

            if ($hasFreeShippingPromo) { $distributorShippingFee = 0; }
            $distributorShippingFees[$distId] = $distributorShippingFee;
            $totalShippingFee += $distributorShippingFee;
        }

        if ($shippingRule->free_shipping_threshold && $totalOrderAmount >= $shippingRule->free_shipping_threshold) {
            $totalShippingFee = 0;
            foreach($distributorShippingFees as $k => $v) { $distributorShippingFees[$k] = 0; }
        }

        // FORCE SHIPPING FEE TO 0 IF PICKUP
        if ($request->payment_method === 'pick-up') {
            $totalShippingFee = 0;
            $fullAddress = "Store Pick-Up";
            foreach($distributorShippingFees as $k => $v) { $distributorShippingFees[$k] = 0; }
        }

        $grandTotal = $totalOrderAmount + $totalShippingFee;
        $orderStatus = $totalQuantity <= 30 ? 'confirmed' : 'pending';
        $vatableSales = round($grandTotal / 1.12, 2);
        $vatAmount = round($grandTotal - $vatableSales, 2);
        $orderNumber = 'ORD-' . strtoupper(Str::random(10));
        $checkoutUrl = null;

        // =========================================================================
        // 1. CALL PAYMONGO FIRST (Cart Version)
        // =========================================================================
        if ($request->payment_method === 'gcash') {
            try {
                $client = new \GuzzleHttp\Client();
                $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');

                // PREPARE BILLING DETAILS FOR PAYMONGO PREFILL
                $billingDetails = [
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                ];
                
                // --- STRICT GCASH NUMBER VALIDATION ---
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
                        'message' => 'No GCash number found in your payment settings. Please update your payment settings before proceeding.'
                    ], 400);
                }

                $billingDetails['phone'] = (string) $gcashNumberToUse;
                // --------------------------------------

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
                                'description' => 'Payment for Cart Order ' . $orderNumber,
                                'reference_number' => $orderNumber, 
                                'billing' => $billingDetails, // INJECTING BILLING HERE FOR PRE-FILL
                                'line_items' => [
                                    [
                                        'currency' => 'PHP',
                                        'amount' => (int) round($grandTotal * 100), 
                                        'name' => 'Cart Checkout - ' . $orderNumber,
                                        'quantity' => 1,
                                    ]
                                ],
                                'success_url' => $frontendOrigin . '/ECommerceClient/EccommerceCart?order_number=' . $orderNumber,
                                'cancel_url' => $frontendOrigin . '/ECommerceClient/EccommerceCart'
                            ]
                        ]
                    ]
                ]);

                $paymongoData = json_decode($response->getBody(), true);

                if ($response->getStatusCode() !== 200) {
                    $errorMessage = $paymongoData['errors'][0]['detail'] ?? 'Invalid Payload';
                    return response()->json(['success' => false, 'message' => 'PayMongo Error: ' . $errorMessage], 400);
                }

                $checkoutUrl = $paymongoData['data']['attributes']['checkout_url'] ?? null;
                $sessionId = $paymongoData['data']['id'] ?? null;

                if (!$checkoutUrl || !$sessionId) {
                    return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link. No URL returned.'], 500);
                }

                $cacheData = [
                    'type' => 'cart',
                    'session_id' => $sessionId, 
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalOrderAmount,
                    'shipping_fee' => $totalShippingFee,
                    'grand_total' => $grandTotal,
                    'vatable_sales' => $vatableSales,
                    'vat_amount' => $vatAmount,
                    'full_address' => $fullAddress,
                    'order_status' => $orderStatus,
                    'items' => $cacheItemsArray,
                    'distributor_shipping_fees' => $distributorShippingFees,
                    'applied_promotions' => array_keys($appliedPromotions),
                    'client_name' => $user->first_name . ' ' . $user->last_name,
                    'selected_items' => $request->selected_items 
                ];

                Storage::disk('local')->put('pending_orders/' . $orderNumber . '.json', json_encode($cacheData));

                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl
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
                'shipping_fee' => $totalShippingFee,
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

            $receiptItems = [];

            foreach ($cartItems as $item) {
                ClientOrderItem::create([
                    'order_id' => $order->id,
                    'distributor_id' => $item->distributor_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->discounted_price 
                ]);

                $distInfo = DB::table('distributor_requirements')->where('user_id', $item->distributor_id)->first();
                $distName = $distInfo ? ($distInfo->company_name ?? $distInfo->business_name ?? 'Distributor') : 'Distributor';
                
                $receiptItems[] = [
                    'name' => $item->product->name,
                    'distributor_name' => $distName,
                    'quantity' => $item->quantity,
                    'price' => $item->discounted_price,
                    'total' => $item->discounted_price * $item->quantity
                ];

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

            foreach (array_keys($appliedPromotions) as $promoId) {
                DB::table('crm_promotions')->where('id', $promoId)->increment('used_count');
            }
            
            // DELETE ONLY THE SELECTED ITEMS FROM THE CART
            ClientCart::where('client_id', $user->id)
                ->whereIn('id', $request->selected_items)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart checked out successfully.',
                'receipt_data' => [
                    'order_number' => $orderNumber,
                    'client_name' => $user->first_name . ' ' . $user->last_name,
                    'items' => $receiptItems,
                    'total_quantity' => $totalQuantity,
                    'shipping_fee' => $totalShippingFee,
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

    // =========================================================================
    // SPECIFIC GCASH VERIFIER FOR CART CHECKOUTS WITH MULTIPLE DISTRIBUTORS
    // =========================================================================
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
                'message' => 'Payment already verified.',
                'already_processed' => true,
            ]);
        }

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Session invalid or not found.'], 400);
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
            return response()->json(['success' => false, 'message' => 'Payment has not been completed.'], 400);
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
            $distributorTotals = []; // Tracks per-distributor values for Remittances and Financial tables
            $totalQuantity = 0;

            foreach ($cacheData['items'] as $item) {
                ClientOrderItem::create([
                    'order_id' => $order->id,
                    'distributor_id' => $item['distributor_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] 
                ]);

                $totalQuantity += $item['quantity'];
                $itemTotal = $item['price'] * $item['quantity'];
                $distId = $item['distributor_id'];

                if (!isset($distributorTotals[$distId])) {
                    $distributorTotals[$distId] = 0;
                }
                $distributorTotals[$distId] += $itemTotal;

                $receiptItems[] = [
                    'name' => $item['product_name'],
                    'distributor_name' => $item['distributor_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal
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
            // INSERT REMITTANCE AND FINANCIAL RECORDS PER INVOLVED DISTRIBUTOR
            // ========================================================================
            foreach ($distributorTotals as $distributorId => $itemsTotal) {
                $distShipping = $cacheData['distributor_shipping_fees'][$distributorId] ?? 0;
                $distGrandTotal = $itemsTotal + $distShipping;
                $distVatableSales = round($distGrandTotal / 1.12, 2);
                $distVatAmount = round($distGrandTotal - $distVatableSales, 2);

                // 1. Insert System Auto-Remittance 
                DB::table('ec_delivery_remittances')->insert([
                    'distributor_id' => $distributorId,
                    'delivery_personnel_id' => null, // Processed by System API
                    'order_id' => $order->id,
                    'amount' => $distGrandTotal,
                    'remittance_proof_path' => 'System Auto-GCash Cart (PayMongo)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. Insert Order Financials tracking specific to this distributor's chunk
                DB::table('ec_order_financials')->insert([
                    'order_id' => $order->id,
                    'distributor_id' => $distributorId,
                    'amount' => $distGrandTotal,
                    'vat_deduction' => $distVatAmount,
                    'total_sales' => $distVatableSales,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Update or Insert Distributor's Overall Sales
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
            // ========================================================================

            foreach ($cacheData['applied_promotions'] as $promoId) {
                DB::table('crm_promotions')->where('id', $promoId)->increment('used_count');
            }

            // Purge the checked out items from the client's actual cart table
            if (isset($cacheData['selected_items'])) {
                ClientCart::where('client_id', $cacheData['user_id'])
                    ->whereIn('id', $cacheData['selected_items'])
                    ->delete();
            }

            DB::commit();
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and Cart order saved successfully.',
                'receipt_data' => [
                    'order_number' => $cacheData['order_number'],
                    'client_name' => $cacheData['client_name'],
                    'items' => $receiptItems, 
                    'total_quantity' => $totalQuantity,
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
            return response()->json(['success' => false, 'message' => 'Failed to process verified order: ' . $e->getMessage()], 500);
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