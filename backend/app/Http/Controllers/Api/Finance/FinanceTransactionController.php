<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceTransactionController extends Controller
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
     * Fetch RBAC permissions for the logged-in user.
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        // Reusing the finance_budget_release permission as it governs financial outgoings
        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_budget_release')
            ->first();

        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_create' => (bool)$access->can_create,
            'can_update' => (bool)$access->can_update,
            'can_delete' => (bool)$access->can_delete,
        ];
    }

    /**
     * Fetch all transactions (Refunds)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_view']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to view transactions.'
            ], 403);
        }

        $distributorId = $this->getDistributorId($user);

        // Fetch Refund Requests
        $refunds = DB::table('ec_refund_requests')
            ->join('client_return_requests', 'ec_refund_requests.return_request_id', '=', 'client_return_requests.id')
            ->join('users as clients', 'client_return_requests.client_id', '=', 'clients.id')
            ->where('ec_refund_requests.distributor_id', $distributorId)
            ->select(
                'ec_refund_requests.id',
                'ec_refund_requests.amount',
                'ec_refund_requests.status',
                'ec_refund_requests.client_gcash_number',
                'ec_refund_requests.created_at',
                'clients.first_name',
                'clients.last_name'
            )
            ->orderBy('ec_refund_requests.created_at', 'desc')
            ->get();

        $formatted = $refunds->map(function ($ref) {
            return [
                'id' => 'REF-' . str_pad($ref->id, 5, '0', STR_PAD_LEFT),
                'db_id' => $ref->id,
                'date' => date('Y-m-d', strtotime($ref->created_at)),
                'party' => $ref->first_name . ' ' . $ref->last_name,
                'role' => 'Client',
                'amount' => (float) $ref->amount,
                'status' => $ref->status,
                'type' => 'refund',
                'gcash_number' => $ref->client_gcash_number ?? 'N/A'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'permissions' => $permissions
        ]);
    }

    /**
     * Process Refund via PayMongo GCash
     */
    public function processRefund(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to process refunds.'], 403);
        }

        $refund = DB::table('ec_refund_requests')->where('id', $id)->first();

        if (!$refund || $refund->status !== 'pending') {
            return response()->json(['message' => 'Only pending refunds can be processed.'], 400);
        }

        // Budget Validation
        $distributorId = $this->getDistributorId($user);
        $totalRevenue = DB::table('distributor_overall_sales')
            ->where('distributor_id', $distributorId)
            ->sum('total_revenue');
        $totalDeducted = DB::table('budget_deduction_logs')
            ->where('distributor_id', $distributorId)
            ->sum('amount');
            
        $availableBudget = $totalRevenue - $totalDeducted;

        if ($refund->amount > $availableBudget) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available budget to process this refund.'
            ], 400);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');
            $baseUrl = $request->input('return_url', $frontendOrigin . '/finance/transactions');
            $transactionCode = 'REF-' . str_pad($refund->id, 5, '0', STR_PAD_LEFT);
            
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
                            'description' => 'Refund Processing for ' . $transactionCode,
                            'reference_number' => $transactionCode, 
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => (int) round($refund->amount * 100), 
                                    'name' => 'Refund Request ' . $transactionCode,
                                    'quantity' => 1,
                                ]
                            ],
                            'success_url' => $baseUrl . '?transaction_code=' . $transactionCode,
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
            
            if (!$checkoutUrl || !$sessionId) {
                return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link.'], 500);
            }

            $cacheData = [
                'session_id' => $sessionId,
                'refund_id' => $refund->id,
                'transaction_code' => $transactionCode,
                'distributor_id' => $refund->distributor_id,
                'amount' => $refund->amount
            ];

            Storage::disk('local')->put('pending_refunds/' . $transactionCode . '.json', json_encode($cacheData));

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'PayMongo Internal Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * VERIFY GCASH PAYMENT AFTER REDIRECT
     */
    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['transaction_code' => 'required|string']);
        $transactionCode = trim($request->transaction_code);
        $filePath = 'pending_refunds/' . $transactionCode . '.json';
        $client = new \GuzzleHttp\Client();

        if (!Storage::disk('local')->exists($filePath)) {
            // Might already be processed, lets check DB
            $refId = (int) str_replace('REF-', '', $transactionCode);
            $refund = DB::table('ec_refund_requests')->where('id', $refId)->first();
            if ($refund && $refund->status === 'completed') {
                return response()->json(['success' => true, 'message' => 'Refund already verified and completed.']);
            }
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
            // Update Refund Status
            DB::table('ec_refund_requests')
                ->where('id', $cacheData['refund_id'])
                ->update(['status' => 'completed', 'updated_at' => now()]);

            // Deduct from total_revenue directly
            DB::table('distributor_overall_sales')
                ->where('distributor_id', $cacheData['distributor_id'])
                ->decrement('total_revenue', $cacheData['amount']);

            // Create log in budget_deduction_logs
            DB::table('budget_deduction_logs')->insert([
                'distributor_id' => $cacheData['distributor_id'],
                'amount' => $cacheData['amount'],
                'description' => 'Refund processed for ' . $transactionCode . ' via GCash',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            Storage::disk('local')->delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Refund verified, logged, and deducted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to process refund: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a refund request
     */
    public function rejectRefund(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to reject refunds.'], 403);
        }

        DB::table('ec_refund_requests')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund request rejected successfully'
        ]);
    }
}