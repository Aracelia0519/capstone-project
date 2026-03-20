<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client\ClientSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Carbon\Carbon;

class ClientSubscriptionController extends Controller
{
    private $receivingNumber = '09922617057';

    private $plans = [
        'starter' => [
            'amount' => 0, 
            'days' => 30, 
            'features' => [
                '3-Color Weighted Blending', 
                'Real-time HEX & RGB Output', 
                'Save up to 5 Colors'
            ]
        ],
        'monthly' => [
            'amount' => 49, 
            'days' => 30, 
            'features' => [
                'All Starter Features', 
                'Harmony Palettes (Mono, Analog, Comp)', 
                'Color Influence Progress Analysis',
                'Save up to 100 Colors'
            ]
        ],
        'half_year' => [
            'amount' => 149, 
            'days' => 180, 
            'features' => [
                'All Monthly Features', 
                'Luminance & Readability Check', 
                'Temperature & Color Family Data', 
                'Unlimited Color Saves'
            ]
        ],
        'annual' => [
            'amount' => 349, 
            'days' => 365, 
            'features' => [
                'All 6-Month Features', 
                'Interactive Orbit Visualizer', 
                'Advanced Mixing Insights & Tips',
                'Unlimited Color Saves'
            ]
        ]
    ];

    public function getCurrentSubscription(Request $request)
    {
        $subscription = ClientSubscription::where('client_id', Auth::id())
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $subscription
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|in:starter,monthly,half_year,annual'
        ]);

        $user = Auth::user();
        $planKey = $request->plan_name;
        $planDetails = $this->plans[$planKey];
        $referenceNumber = 'SUB-' . strtoupper(Str::random(10));

        // If it's the free Starter plan, activate immediately (DB write is required here since it's instantly active)
        if ($planDetails['amount'] == 0) {
            DB::beginTransaction();
            try {
                // Expire old subscriptions
                ClientSubscription::where('client_id', $user->id)->update(['status' => 'expired']);

                ClientSubscription::create([
                    'client_id' => $user->id,
                    'plan_name' => $planKey,
                    'features' => $planDetails['features'],
                    'amount' => 0,
                    'status' => 'active',
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays($planDetails['days']),
                    'reference_number' => $referenceNumber,
                    'admin_receiving_number' => $this->receivingNumber
                ]);
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Starter plan activated successfully!']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to activate plan.'], 500);
            }
        }

        // For Paid Plans - GCash Integration via PayMongo
        try {
            $client = new Client();
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
                            'description' => 'Subscription to ' . ucfirst($planKey) . ' Plan (Send to ' . $this->receivingNumber . ')',
                            'reference_number' => $referenceNumber,
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => (int) round($planDetails['amount'] * 100),
                                    'name' => 'Color Lab ' . ucfirst($planKey) . ' Subscription',
                                    'quantity' => 1,
                                ]
                            ],
                            'success_url' => $frontendOrigin . '/Clients/dashboardC?subscription_ref=' . $referenceNumber,
                            'cancel_url' => $frontendOrigin . '/Clients/dashboardC'
                        ]
                    ]
                ]
            ]);

            $paymongoData = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                $errorMessage = $paymongoData['errors'][0]['detail'] ?? 'Invalid payload format.';
                return response()->json(['success' => false, 'message' => 'PayMongo Error: ' . $errorMessage], 400);
            }

            $checkoutUrl = $paymongoData['data']['attributes']['checkout_url'];
            $sessionId = $paymongoData['data']['id'];

            // INSTEAD OF SAVING TO DB, SAVE TO LOCAL STORAGE (Prevents DB bloat on abandoned checkouts)
            $cacheData = [
                'client_id' => $user->id,
                'plan_name' => $planKey,
                'features' => $planDetails['features'],
                'amount' => $planDetails['amount'],
                'reference_number' => $referenceNumber,
                'paymongo_session_id' => $sessionId,
            ];

            Storage::disk('local')->put('pending_subscriptions/' . $referenceNumber . '.json', json_encode($cacheData));

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'PayMongo Error: ' . $e->getMessage()], 500);
        }
    }

    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['reference_number' => 'required|string']);
        $referenceNumber = trim($request->reference_number);
        $filePath = 'pending_subscriptions/' . $referenceNumber . '.json';

        // 1. Double-mount check. Is the subscription already successfully inserted into DB?
        $existingSub = ClientSubscription::where('reference_number', $referenceNumber)->first();
        if ($existingSub) {
            return response()->json([
                'success' => true, 
                'message' => 'Subscription already active and processed.'
            ]);
        }

        // 2. Check if the pending file exists
        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json([
                'success' => false, 
                'message' => 'Session invalid or already processed. File not found.'
            ], 400);
        }

        // 3. Retrieve session data
        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);

        // 4. Verify with PayMongo
        try {
            $client = new Client();
            $response = $client->request('GET', 'https://api.paymongo.com/v1/checkout_sessions/' . $cacheData['paymongo_session_id'], [
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic ' . base64_encode('sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:')
                ],
                'http_errors' => false
            ]);

            $isPaid = false;
            if ($response->getStatusCode() === 200) {
                $paymongoData = json_decode($response->getBody(), true);
                $attributes = $paymongoData['data']['attributes'] ?? [];

                $payments = $attributes['payments'] ?? [];
                foreach ($payments as $p) {
                    if (isset($p['attributes']['status']) && $p['attributes']['status'] === 'paid') {
                        $isPaid = true;
                    }
                }
            }

            if (!$isPaid) {
                return response()->json(['success' => false, 'message' => 'Payment has not been completed yet.'], 400);
            }

            // 5. IF PAID: Execute Database Write operations
            DB::beginTransaction();
            try {
                // Deactivate old active subscriptions for upgrades
                ClientSubscription::where('client_id', $cacheData['client_id'])->update(['status' => 'expired']);

                $planDays = $this->plans[$cacheData['plan_name']]['days'];
                
                // Write the new subscription to the database ONLY now
                ClientSubscription::create([
                    'client_id' => $cacheData['client_id'],
                    'plan_name' => $cacheData['plan_name'],
                    'features' => $cacheData['features'],
                    'amount' => $cacheData['amount'],
                    'status' => 'active',
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays($planDays),
                    'reference_number' => $cacheData['reference_number'],
                    'paymongo_session_id' => $cacheData['paymongo_session_id'],
                    'admin_receiving_number' => $this->receivingNumber
                ]);

                // Cleanup temporary storage file
                Storage::disk('local')->delete($filePath);
                
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Payment verified. Subscription active!']);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to save subscription: ' . $e->getMessage()], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Verification process failed: ' . $e->getMessage()], 500);
        }
    }
}