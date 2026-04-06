<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Finance\BudgetDeductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcurementBudgetReleaseController extends Controller
{
    /**
     * Resolve the distributor ID based on the logged-in user's role.
     */
    private function getDistributorId($user)
    {
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $employee ? $employee->parent_distributor_id : null;
        } elseif (in_array($user->role, ['finance_manager', 'operational_distributor', 'hr_manager'])) {
            $tableName = $user->role . 's';
            $staff = DB::table($tableName)->where('user_id', $user->id)->first();
            return $staff ? $staff->parent_distributor_id : null;
        } elseif ($user->role === 'distributor') {
            return $user->id;
        }
        
        return null;
    }

    /**
     * Fetch RBAC permissions for the logged-in user (Level-Based).
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_budget_release')
            ->first();

        // Check if access row exists and is generally granted
        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_manage' => (bool)$access->can_manage,
            'can_approve' => (bool)$access->can_approve,
        ];
    }

    /**
     * Fetch procurement requests that are 'd-approved' (Distributor Approved) or 'ready'
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_view']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to view budget releases.'
            ], 403);
        }

        $distributorId = $this->getDistributorId($user);

        // Calculate Available Budget directly from total_revenue (ignoring logs)
        $totalRevenue = DB::table('distributor_overall_sales')
            ->where('distributor_id', $distributorId)
            ->sum('total_revenue');

        $availableBudget = $totalRevenue;

        // Fetches requests mapped to the authorized distributor
        $requests = ProcurementRequest::with(['requester', 'distributor'])
            ->where('distributor_id', $distributorId)
            ->whereIn('status', ['d-approved', 'ready'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $formatted = $requests->map(function ($req) {
            $department = 'General';
            if ($req->requester && method_exists($req->requester, 'employee') && $req->requester->employee) {
                $department = $req->requester->employee->department;
            }

            // Fetch supplier payment settings to verify if Bank Transfer is available
            $paymentSettings = DB::table('supplier_payment_settings')->where('supplier_id', $req->supplier_id)->first();
            $isBankEnabled = $paymentSettings ? (bool)$paymentSettings->is_bank_enabled : false;

            return [
                'id' => $req->request_code ?? 'REQ-' . $req->id,
                'db_id' => $req->id,
                'department' => $department, 
                'requester' => $req->requester ? $req->requester->full_name : 'Unknown',
                'distributor' => $req->distributor ? $req->distributor->full_name : 'Unknown',
                'date' => $req->request_date->format('Y-m-d'),
                'location' => $req->delivery_address ?? 'Main Office',
                'status' => $req->status,
                'priority' => ucfirst($req->priority),
                'payment_terms' => $req->payment_terms ?? 'cod',
                'is_bank_enabled' => $isBankEnabled,
                'totalAmount' => (float) $req->total_cost,
                'items' => [
                    [
                        'name' => $req->product_name,
                        'quantity' => $req->quantity,
                        'unitPrice' => (float) $req->unit_price
                    ]
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'available_budget' => $availableBudget,
            'permissions' => $permissions
        ]);
    }

    /**
     * Approve and release budget (Marks status as 'ready' and logs deduction)
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        // Explicitly requires Approve level for releasing funds
        if (!$permissions['can_approve']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to approve budget releases.'
            ], 403);
        }

        $procurement = ProcurementRequest::findOrFail($id);

        if ($procurement->status !== 'd-approved') {
            return response()->json(['message' => 'Only Distributor Approved (d-approved) requests can have their budget released.'], 400);
        }

        // Apply updated payment term if the user switched it via the DSS recommendation
        if ($request->has('payment_terms')) {
            $procurement->payment_terms = $request->payment_terms;
            $procurement->save();
        }

        // Budget Validation (Check directly against total_revenue)
        $distributorId = $this->getDistributorId($user);
        $totalRevenue = DB::table('distributor_overall_sales')
            ->where('distributor_id', $distributorId)
            ->sum('total_revenue');
            
        $availableBudget = $totalRevenue;

        if ($procurement->total_cost > $availableBudget) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available budget to approve this request.'
            ], 400);
        }

        $paymentTerm = strtolower($procurement->payment_terms);

        // =========================================================================
        // ONLINE PAYMENT LOGIC (GCASH via PayMongo & BANK via STRIPE)
        // =========================================================================
        if (in_array($paymentTerm, ['gcash', 'bank'])) {
            
            // FETCH BILLING DETAILS FROM USERS & DISTRIBUTOR_PAYMENT_SETTINGS
            $distributorUser = DB::table('users')->where('id', $distributorId)->first();
            $paymentSettings = DB::table('distributor_payment_settings')->where('distributor_id', $distributorId)->first();

            // STRICT GCASH NUMBER VALIDATION (Only strictly fail if GCash is requested)
            if ($paymentTerm === 'gcash' && (!$paymentSettings || empty($paymentSettings->gcash_number))) {
                return response()->json([
                    'success' => false,
                    'message' => 'GCash payment cannot be processed. No valid GCash number found in your Payment Settings.'
                ], 400);
            }

            // Format Name and Email
            $billingName = $distributorUser ? trim($distributorUser->first_name . ' ' . $distributorUser->last_name) : 'Distributor';
            $billingEmail = $distributorUser ? $distributorUser->email : 'no-reply@example.com';
            $billingPhone = $paymentSettings ? $paymentSettings->gcash_number : '';

            $descriptionStr = 'Procurement Budget Release via ' . strtoupper($paymentTerm) . ' for ' . ($procurement->request_code ?? 'REQ-'.$procurement->id);
            $requestCode = $procurement->request_code ?? 'REQ-' . $procurement->id;
            
            $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $baseUrl = $request->input('return_url', $frontendOrigin . '/finance/procurementBudgetRelease');

            $client = new \GuzzleHttp\Client();
            $provider = 'paymongo'; // Default fallback
            $checkoutUrl = null;
            $sessionId = null;

            // -----------------------------------------------------------------
            // GATEWAY 1: PAYMONGO (For GCash)
            // -----------------------------------------------------------------
            if ($paymentTerm === 'gcash') {
                try {
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
                                    'reference_number' => $requestCode, 
                                    'billing' => [
                                        'name' => $billingName,
                                        'email' => $billingEmail,
                                        'phone' => $billingPhone
                                    ],
                                    'line_items' => [
                                        [
                                            'currency' => 'PHP',
                                            'amount' => (int) round($procurement->total_cost * 100), 
                                            'name' => 'Procurement Request ' . $requestCode,
                                            'quantity' => 1,
                                        ]
                                    ],
                                    'success_url' => $baseUrl . '?request_code=' . $requestCode,
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

                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'PayMongo Internal Error: ' . $e->getMessage()], 500);
                }
            } 
            // -----------------------------------------------------------------
            // GATEWAY 2: STRIPE (For Bank Transfer / Direct Debit Sandbox)
            // -----------------------------------------------------------------
            // -----------------------------------------------------------------
            // GATEWAY 2: STRIPE (For Bank Transfer / Direct Debit Sandbox)
            // -----------------------------------------------------------------
            else if ($paymentTerm === 'bank') {
                try {
                    $provider = 'stripe';
                    // Using your provided secret key directly for the capstone demo
                    $stripeSecretKey = env('STRIPE_SECRET_KEY', 'sk_test_51TJD8JGe0rCXOZj6zNH9eUdp5IxSCOVJejF9WH9SaNjdICx4Ftr75YiEcSHu2A3jm2zKXrPkxnfAabLAiztdDGP8003ePhoRZE');

                    // Convert PHP to a rough USD equivalent for the Stripe Demo (Assuming ~₱56 = $1)
                    $usdAmount = (int) round(($procurement->total_cost / 56) * 100);
                    // Ensure minimum amount for Stripe is met (Stripe requires at least $0.50 USD)
                    if ($usdAmount < 50) $usdAmount = 50;

                    $response = $client->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $stripeSecretKey,
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                        'http_errors' => false,
                        'form_params' => [
                            // FORCE STRIPE TO SHOW BANK TRANSFER (ACH)
                            'payment_method_types' => [
                                0 => 'us_bank_account', // This forces the Bank Transfer UI
                                1 => 'card'
                            ],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => 'usd', // Forced to USD because Stripe Bank Transfers require it
                                        'product_data' => [
                                            'name' => 'Procurement Request ' . $requestCode,
                                            'description' => $descriptionStr . ' (Converted to USD for Bank Transfer Demo)',
                                        ],
                                        'unit_amount' => $usdAmount,
                                    ],
                                    'quantity' => 1,
                                ]
                            ],
                            'mode' => 'payment',
                            'success_url' => $baseUrl . '?request_code=' . $requestCode,
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

                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Stripe Internal Error: ' . $e->getMessage()], 500);
                }
            }

            if (!$checkoutUrl || !$sessionId) {
                return response()->json(['success' => false, 'message' => 'Failed to generate Checkout link for ' . strtoupper($provider)], 500);
            }

            $cacheData = [
                'session_id' => $sessionId,
                'procurement_id' => $procurement->id,
                'request_code' => $requestCode,
                'distributor_id' => $procurement->distributor_id,
                'supplier_id' => $procurement->supplier_id,
                'total_cost' => $procurement->total_cost,
                'payment_method' => $paymentTerm,
                'provider' => $provider
            ];

            Storage::disk('local')->put('pending_budget_releases/' . $requestCode . '.json', json_encode($cacheData));

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);
        }

        // =========================================================================
        // NORMAL (COD / OFFLINE) BUDGET RELEASE LOGIC
        // =========================================================================
        try {
            DB::beginTransaction();

            $procurement->updateStatus('ready');

            // 1. DEDUCT DIRECTLY FROM OVERALL SALES
            DB::table('distributor_overall_sales')
                ->where('distributor_id', $procurement->distributor_id)
                ->decrement('total_revenue', $procurement->total_cost);

            // 2. Keep the log as requested
            BudgetDeductionLog::create([
                'distributor_id' => $procurement->distributor_id,
                'procurement_request_id' => $procurement->id,
                'amount' => $procurement->total_cost,
                'description' => 'Budget released and deducted for procurement request ' . ($procurement->request_code ?? $procurement->id)
            ]);
            
            DB::commit();

            return response()->json([
                'message' => 'Budget successfully released. Status is now Ready.',
                'data' => $procurement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to release budget: ' . $e->getMessage()], 500);
        }
    }

    /**
     * VERIFY ONLINE PAYMENT AFTER REDIRECT
     */
    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['request_code' => 'required|string']);
        $requestCode = trim($request->request_code);
        $filePath = 'pending_budget_releases/' . $requestCode . '.json';
        $client = new \GuzzleHttp\Client();

        // 1. Check if it's already recorded in budget deduction logs
        $procurement = ProcurementRequest::where('request_code', $requestCode)->first();
        if ($procurement && $procurement->status === 'ready') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already verified and processed successfully.',
            ]);
        }

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Session invalid or already processed. File not found.'], 400);
        }

        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);
        $sessionId = $cacheData['session_id']; 
        $paymentMethodStr = $cacheData['payment_method'] ?? 'gcash';
        $provider = $cacheData['provider'] ?? 'paymongo';
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
                    
                    // NEW FIX: Bank Transfers are asynchronous. We check if the user completed the checkout 
                    // OR if it's instantly paid (like a credit card).
                    $isStatusComplete = isset($stripeData['status']) && $stripeData['status'] === 'complete';
                    $isPaymentPaid = isset($stripeData['payment_status']) && $stripeData['payment_status'] === 'paid';

                    if ($isStatusComplete || $isPaymentPaid) {
                        $isPaid = true;
                    }
                }
            }
        } catch (\Exception $e) {
            $isPaid = false; 
        }

        if (!$isPaid) {
            return response()->json(['success' => false, 'message' => ucfirst($provider) . ' Status: Payment has not been officially completed.'], 400);
        }

        // 2. Execute DB Transaction
        DB::beginTransaction();
        try {
            $procurement = ProcurementRequest::findOrFail($cacheData['procurement_id']);
            $procurement->updateStatus('ready');

            // 1. DEDUCT DIRECTLY FROM OVERALL SALES
            DB::table('distributor_overall_sales')
                ->where('distributor_id', $procurement->distributor_id)
                ->decrement('total_revenue', $procurement->total_cost);

            // 2. Log deduction
            BudgetDeductionLog::create([
                'distributor_id' => $procurement->distributor_id,
                'procurement_request_id' => $procurement->id,
                'amount' => $procurement->total_cost,
                'description' => 'Budget released and deducted via ' . strtoupper($paymentMethodStr) . ' for procurement request ' . $requestCode
            ]);

            // Track supplier payment
            DB::table('distributor_supplier_payments')->insert([
                'procurement_request_id' => $procurement->id,
                'distributor_id' => $procurement->distributor_id,
                'supplier_id' => $procurement->supplier_id,
                'amount' => $procurement->total_cost,
                'payment_method' => $paymentMethodStr,
                'reference_number' => $requestCode,
                'status' => 'paid',
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and budget released successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to process payment release: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a request and send Email Notifications
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        // Explicitly requires Approve level to reject financial releases
        if (!$permissions['can_approve']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to reject budget releases.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        // Load the related models to fetch their emails
        $procurement = ProcurementRequest::with(['requester', 'distributor'])->findOrFail($id);

        $procurement->updateStatus('rejected', $request->reason);

        // =========================================================================
        // EMAIL NOTIFICATION DISPATCH LOGIC
        // =========================================================================
        $emailsToNotify = [];
        
        if ($procurement->requester && !empty($procurement->requester->email)) {
            $emailsToNotify[] = $procurement->requester->email;
        }
        
        if ($procurement->distributor && !empty($procurement->distributor->email)) {
            $emailsToNotify[] = $procurement->distributor->email;
        }

        // Prevent duplicate emails (e.g. if the requester is the distributor)
        $emailsToNotify = array_unique($emailsToNotify);

        if (!empty($emailsToNotify)) {
            $requestCode = $procurement->request_code ?? 'REQ-' . $procurement->id;
            $reason = $request->reason;
            $productName = $procurement->product_name;
            $quantity = $procurement->quantity;
            $totalCost = number_format($procurement->total_cost, 2);

            $subject = "Procurement Budget Release Rejected: {$requestCode}";
            
            $message = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; line-height: 1.6;'>
                    <h2 style='color: #dc2626;'>Procurement Budget Release Rejected</h2>
                    <p>Hello,</p>
                    <p>Please be advised that the budget release for the following procurement request has been <strong>rejected</strong> by the Finance Department.</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px;'>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #e5e7eb; width: 35%;'><strong>Request Code:</strong></td>
                            <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$requestCode}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #e5e7eb;'><strong>Product:</strong></td>
                            <td style='padding: 10px; border: 1px solid #e5e7eb;'>{$productName} (Qty: {$quantity})</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #e5e7eb;'><strong>Total Budget Required:</strong></td>
                            <td style='padding: 10px; border: 1px solid #e5e7eb;'>₱{$totalCost}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #fecaca; background-color: #fef2f2; color: #991b1b;'><strong>Reason for Rejection:</strong></td>
                            <td style='padding: 10px; border: 1px solid #fecaca; background-color: #fef2f2; color: #991b1b;'>{$reason}</td>
                        </tr>
                    </table>
                    
                    <p>Please log in to the CaviteGoPaint system to review this request or contact the Finance Department for further clarification.</p>
                    <br>
                    <p>Best regards,<br><strong>CaviteGoPaint Finance System</strong></p>
                </div>
            ";

            try {
                Mail::html($message, function ($mail) use ($emailsToNotify, $subject) {
                    $mail->to($emailsToNotify)
                         ->subject($subject);
                });
            } catch (\Exception $e) {
                // Log the failure to prevent crashing the response if SMTP misbehaves
                Log::error("Failed to send budget rejection email for {$requestCode}: " . $e->getMessage());
            }
        }
        // =========================================================================

        return response()->json([
            'message' => 'Request rejected successfully and email notifications have been sent.',
            'data' => $procurement
        ]);
    }
}