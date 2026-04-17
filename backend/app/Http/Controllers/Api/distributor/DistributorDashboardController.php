<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DistributorDashboardController extends Controller
{
    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $distributorId = $this->getDistributorId($user);

            // Timeframes
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $validOrderStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

            // ==========================================
            // 1. FINANCE DATA (Current Month)
            // ==========================================
            $currentClientSales = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('client_orders.id', 'client_orders.grand_total')->distinct()->get()->sum('grand_total');

            $currentSpSales = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('sp_orders.id', 'sp_orders.grand_total')->distinct()->get()->sum('grand_total');

            $currentTotalSales = $currentClientSales + $currentSpSales;

            $lastClientSales = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$lastMonthStart, $lastMonthEnd])
                ->select('client_orders.id', 'client_orders.grand_total')->distinct()->get()->sum('grand_total');

            $lastSpSales = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$lastMonthStart, $lastMonthEnd])
                ->select('sp_orders.id', 'sp_orders.grand_total')->distinct()->get()->sum('grand_total');

            $lastTotalSales = $lastClientSales + $lastSpSales;
            $salesChangePercent = $lastTotalSales > 0 ? (($currentTotalSales - $lastTotalSales) / $lastTotalSales) * 100 : ($currentTotalSales > 0 ? 100 : 0);

            // Expenses calculation
            $currentVat = DB::table('order_vat_deductions')
                ->join('client_orders', 'order_vat_deductions.order_id', '=', 'client_orders.id')
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('order_vat_deductions.vat_amount') +
                DB::table('order_vat_deductions')
                ->join('sp_orders', 'order_vat_deductions.sp_order_id', '=', 'sp_orders.id')
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('order_vat_deductions.vat_amount');

            $currentProcurement = DB::table('procurement_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', ['ready', 'processing', 'prepared', 'shipped', 'in_transit', 'delivered', 'completed'])
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('total_cost');

            $employeeIds = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->pluck('user_id')->toArray();
            $currentPayroll = DB::table('payrolls')->whereIn('user_id', $employeeIds)->where('status', 'paid')
                ->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])->sum('net_pay');

            $currentRefunds = DB::table('ec_refund_requests')->where('distributor_id', $distributorId)
                ->whereIn('status', ['approved', 'completed'])
                ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd])->sum('amount');

            $totalExpenses = $currentVat + $currentProcurement + $currentPayroll + $currentRefunds;
            $netCashFlow = $currentTotalSales - $totalExpenses;

            // ==========================================
            // 2. FINANCE: 6-Month Revenue Trend (NEW)
            // ==========================================
            $monthlyRevenue = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
                
                $cSales = DB::table('client_orders')->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                    ->where('client_order_items.distributor_id', $distributorId)->whereIn('client_orders.status', $validOrderStatuses)
                    ->whereBetween('client_orders.created_at', [$mStart, $mEnd])->select('client_orders.id', 'client_orders.grand_total')->distinct()->get()->sum('grand_total');

                $sSales = DB::table('sp_orders')->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                    ->where('sp_order_items.distributor_id', $distributorId)->whereIn('sp_orders.status', $validOrderStatuses)
                    ->whereBetween('sp_orders.created_at', [$mStart, $mEnd])->select('sp_orders.id', 'sp_orders.grand_total')->distinct()->get()->sum('grand_total');

                $monthlyRevenue[] = [
                    'label' => $mStart->format('M'),
                    'revenue' => $cSales + $sSales
                ];
            }

            // ==========================================
            // 3. HR DATA
            // ==========================================
            $totalEmployees = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->count();
            $activeEmployees = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->where('status', 'active')->count();
            $onLeave = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->where('status', 'on_leave')->count();

            // Attendance - Look back up to 7 days to ensure graph data isn't 0 if checked early in the morning
            $latestAttendanceDate = DB::table('employee_attendances')->where('distributor_id', $distributorId)->max('date') ?? Carbon::today();
            
            $presentCount = DB::table('employee_attendances')
                ->where('distributor_id', $distributorId)->whereDate('date', $latestAttendanceDate)->whereNotNull('time_in')->count();
            $absentCount = max(0, $totalEmployees - $presentCount - $onLeave);

            $attendanceData = [
                ['name' => 'Present', 'value' => $presentCount, 'color' => '#10B981'],
                ['name' => 'Absent', 'value' => $absentCount, 'color' => '#EF4444'],
                ['name' => 'On Leave', 'value' => $onLeave, 'color' => '#F59E0B']
            ];

            // ==========================================
            // 4. E-COMMERCE DATA
            // ==========================================
            $clientOrderIds = DB::table('client_order_items')->where('distributor_id', $distributorId)->pluck('order_id');
            $spOrderIds = DB::table('sp_order_items')->where('distributor_id', $distributorId)->pluck('sp_order_id');

            $totalOrders = DB::table('client_orders')->whereIn('id', $clientOrderIds)->count() + DB::table('sp_orders')->whereIn('id', $spOrderIds)->count();
            $pendingOrders = DB::table('client_orders')->whereIn('id', $clientOrderIds)->whereIn('status', ['pending', 'confirmed', 'prepared', 'ready_for_pickup', 'shipped'])->count() + 
                             DB::table('sp_orders')->whereIn('id', $spOrderIds)->whereIn('status', ['pending', 'confirmed', 'prepared', 'ready_for_pickup', 'shipped'])->count();

            // Top Products
            $clientItems = DB::table('client_order_items')->where('distributor_id', $distributorId)
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_sales'))->groupBy('product_id')->get();
            $spItems = DB::table('sp_order_items')->where('distributor_id', $distributorId)
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_sales'))->groupBy('product_id')->get();

            $mergedProducts = [];
            foreach ([$clientItems, $spItems] as $items) {
                foreach ($items as $item) {
                    if (!isset($mergedProducts[$item->product_id])) {
                        $mergedProducts[$item->product_id] = ['product_id' => $item->product_id, 'total_sold' => 0, 'total_sales' => 0];
                    }
                    $mergedProducts[$item->product_id]['total_sold'] += $item->total_sold;
                    $mergedProducts[$item->product_id]['total_sales'] += $item->total_sales;
                }
            }

            usort($mergedProducts, function($a, $b) { return $b['total_sold'] - $a['total_sold']; });
            $mergedProducts = array_slice($mergedProducts, 0, 5);
            $productIds = array_column($mergedProducts, 'product_id');
            $products = DB::table('distributor_products')->whereIn('id', $productIds)->get()->keyBy('id');

            $bestSellingProducts = collect($mergedProducts)->map(function($item) use ($products) {
                $product = $products->get($item['product_id']);
                return [
                    'id' => $item['product_id'],
                    'name' => $product ? $product->name : 'Unknown Product',
                    'sales' => number_format((float) $item['total_sales'], 2, '.', ''),
                    'sold' => (int) $item['total_sold']
                ];
            })->values();

            // ==========================================
            // 5. RECENT ORDERS DATATABLE (NEW)
            // ==========================================
            $recentClientOrders = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->select('client_orders.id as order_id', 'client_orders.grand_total as amount', 'client_orders.status', 'client_orders.created_at', DB::raw("'Client' as type"))
                ->distinct()->orderByDesc('created_at')->limit(5)->get();

            $recentSpOrders = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->select('sp_orders.id as order_id', 'sp_orders.grand_total as amount', 'sp_orders.status', 'sp_orders.created_at', DB::raw("'Service Provider' as type"))
                ->distinct()->orderByDesc('created_at')->limit(5)->get();

            $recentOrders = collect($recentClientOrders)->merge($recentSpOrders)->sortByDesc('created_at')->take(5)->values();

            return response()->json([
                'success' => true,
                'finance' => [
                    'totalSales' => $currentTotalSales,
                    'salesChangePercent' => round($salesChangePercent, 1),
                    'netCashFlow' => $netCashFlow,
                    'totalExpenses' => $totalExpenses,
                    'monthlyRevenue' => $monthlyRevenue
                ],
                'hr' => [
                    'totalEmployees' => $totalEmployees,
                    'activeEmployees' => $activeEmployees,
                    'attendanceData' => $attendanceData
                ],
                'ecommerce' => [
                    'totalOrders' => $totalOrders,
                    'pendingOrders' => $pendingOrders,
                    'bestSellingProducts' => $bestSellingProducts,
                    'recentOrders' => $recentOrders
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Combined Dashboard Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate combined dashboard.'], 500);
        }
    }

    /**
     * Handle Direct Deposit initialization using PayMongo (GCash) or Stripe (Bank)
     */
    public function depositFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'return_url' => 'required|string'
        ]);

        $user = Auth::user();
        $distributorId = $this->getDistributorId($user);
        $amount = (float) $request->amount;
        
        $paymentTerm = $amount <= 10000 ? 'gcash' : 'stripe';
        $referenceNumber = 'DEP-' . strtoupper(Str::random(10));
        
        // =================================================================================
        // VERIFY PAYMENT CONFIGURATION SETTINGS
        // =================================================================================
        $paymentSettings = DB::table('distributor_payment_settings')->where('distributor_id', $distributorId)->first();

        if (!$paymentSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Payment configuration is missing. Please formally request the Operations department to set up the banking details before proceeding.'
            ], 400);
        }

        if ($paymentTerm === 'gcash' && empty($paymentSettings->gcash_number)) {
            return response()->json([
                'success' => false,
                'message' => 'GCash transactions are currently unavailable. Please formally request the Operations department to configure the necessary GCash banking details.'
            ], 400);
        }

        // Assuming is_bank_enabled column controls Bank Transfers, alternatively checking if bank details are set
        $bankDisabled = isset($paymentSettings->is_bank_enabled) && !$paymentSettings->is_bank_enabled;
        
        if ($paymentTerm === 'stripe' && $bankDisabled) {
            return response()->json([
                'success' => false,
                'message' => 'Bank Transfer transactions are currently disabled. Please formally request the Operations department to enable and configure the banking details.'
            ], 400);
        }
        // =================================================================================

        $billingName = trim($user->first_name . ' ' . $user->last_name) ?: 'Distributor';
        $billingEmail = $user->email;
        $descriptionStr = 'Dashboard Direct Deposit via ' . strtoupper($paymentTerm);
        $baseUrl = rtrim($request->return_url, '/');
        
        $client = new \GuzzleHttp\Client();
        $checkoutUrl = null;
        $sessionId = null;
        $provider = null;

        try {
            if ($paymentTerm === 'gcash') {
                $provider = 'paymongo';
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
                                'send_email_receipt' => false,
                                'show_description' => true,
                                'show_line_items' => true,
                                'payment_method_types' => ['gcash'],
                                'description' => $descriptionStr,
                                'reference_number' => $referenceNumber, 
                                'billing' => [
                                    'name' => $billingName,
                                    'email' => $billingEmail,
                                    'phone' => $paymentSettings->gcash_number ?? '09123456789'
                                ],
                                'line_items' => [
                                    [
                                        'currency' => 'PHP',
                                        'amount' => (int) round($amount * 100), 
                                        'name' => 'Direct Deposit ' . $referenceNumber,
                                        'quantity' => 1,
                                    ]
                                ],
                                'success_url' => $baseUrl . '?deposit_ref=' . $referenceNumber,
                                'cancel_url' => $baseUrl
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

            } else {
                // Stripe Implementation
                $provider = 'stripe';
                $stripeSecretKey = env('STRIPE_SECRET_KEY', 'sk_test_51TJD8JGe0rCXOZj6zNH9eUdp5IxSCOVJejF9WH9SaNjdICx4Ftr75YiEcSHu2A3jm2zKXrPkxnfAabLAiztdDGP8003ePhoRZE');
                
                // Demo Conversion
                $usdAmount = (int) round(($amount / 56) * 100);
                if ($usdAmount < 50) $usdAmount = 50; // Stripe min is 50 cents

                $response = $client->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $stripeSecretKey,
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'http_errors' => false,
                    'form_params' => [
                        'payment_method_types' => [
                            0 => 'us_bank_account', 
                            1 => 'card'
                        ],
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => 'usd',
                                    'product_data' => [
                                        'name' => 'Direct Deposit ' . $referenceNumber,
                                        'description' => $descriptionStr . ' (Converted to USD for Demo)',
                                    ],
                                    'unit_amount' => $usdAmount,
                                ],
                                'quantity' => 1,
                            ]
                        ],
                        'mode' => 'payment',
                        'success_url' => $baseUrl . '?deposit_ref=' . $referenceNumber,
                        'cancel_url' => $baseUrl,
                        'customer_email' => $billingEmail ?: 'no-reply@example.com',
                    ]
                ]);

                $stripeData = json_decode($response->getBody(), true);

                if ($response->getStatusCode() !== 200) {
                    $errorMsg = $stripeData['error']['message'] ?? 'Unknown Stripe Error';
                    return response()->json(['success' => false, 'message' => 'Stripe Error: ' . $errorMsg], 400);
                }

                $checkoutUrl = $stripeData['url'] ?? null;
                $sessionId = $stripeData['id'] ?? null;
            }

            if (!$checkoutUrl || !$sessionId) {
                return response()->json(['success' => false, 'message' => 'Failed to generate Checkout link.'], 500);
            }

            // Save deposit intention securely
            $cacheData = [
                'session_id' => $sessionId,
                'reference_number' => $referenceNumber,
                'distributor_id' => $distributorId,
                'amount' => $amount,
                'payment_method' => $paymentTerm,
                'provider' => $provider
            ];

            Storage::disk('local')->put('pending_deposits/' . $referenceNumber . '.json', json_encode($cacheData));

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);

        } catch (\Exception $e) {
            Log::error("Deposit Initialization Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal server error processing deposit.'], 500);
        }
    }

    /**
     * Verify Deposit via PayMongo or Stripe after redirect
     */
    public function verifyDeposit(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $referenceNumber = trim($request->reference_number);
        $filePath = 'pending_deposits/' . $referenceNumber . '.json';
        $client = new \GuzzleHttp\Client();

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Deposit session invalid or already processed.'], 400);
        }

        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);
        $sessionId = $cacheData['session_id']; 
        $provider = $cacheData['provider'] ?? 'paymongo';
        $distributorId = $cacheData['distributor_id'];
        $amount = (float) $cacheData['amount'];
        $isPaid = false;

        try {
            if ($provider === 'paymongo') {
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
                }
            } 
            else if ($provider === 'stripe') {
                $stripeSecretKey = env('STRIPE_SECRET_KEY', 'sk_test_51TJD8JGe0rCXOZj6zNH9eUdp5IxSCOVJejF9WH9SaNjdICx4Ftr75YiEcSHu2A3jm2zKXrPkxnfAabLAiztdDGP8003ePhoRZE');
                
                $response = $client->request('GET', 'https://api.stripe.com/v1/checkout/sessions/' . $sessionId, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $stripeSecretKey,
                        'accept' => 'application/json'
                    ],
                    'http_errors' => false 
                ]);

                if ($response->getStatusCode() === 200) {
                    $stripeData = json_decode($response->getBody(), true);
                    $isStatusComplete = isset($stripeData['status']) && $stripeData['status'] === 'complete';
                    $isPaymentPaid = isset($stripeData['payment_status']) && $stripeData['payment_status'] === 'paid';

                    if ($isStatusComplete || $isPaymentPaid) {
                        $isPaid = true;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Verification ping error: " . $e->getMessage());
            $isPaid = false; 
        }

        if (!$isPaid) {
            return response()->json(['success' => false, 'message' => ucfirst($provider) . ' Status: Payment has not been fully verified.'], 400);
        }

        // Apply Funds safely to DB
        DB::beginTransaction();
        try {
            $existing = DB::table('distributor_overall_sales')->where('distributor_id', $distributorId)->first();
            
            if ($existing) {
                DB::table('distributor_overall_sales')
                    ->where('distributor_id', $distributorId)
                    ->increment('total_revenue', $amount);
            } else {
                DB::table('distributor_overall_sales')->insert([
                    'distributor_id' => $distributorId,
                    'total_revenue' => $amount,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Deposit verified and funds credited successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Deposit Transaction Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to apply deposit to account.'], 500);
        }
    }
}