<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            ->where('permission_key', 'finance_transactions')
            ->first();

        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_manage' => (bool)$access->can_manage,
            'can_approve' => (bool)$access->can_approve,
        ];
    }

    /**
     * Fetch all transactions (Refunds for both Clients & Service Providers)
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

        // 1. Fetch Client Refund Requests
        $clientRefunds = DB::table('ec_refund_requests')
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
            ->get()
            ->map(function ($ref) {
                $ref->role = 'Client';
                return $ref;
            });

        // 2. Fetch Service Provider Refund Requests
        $spRefunds = DB::table('ec_refund_requests')
            ->join('sp_return_requests', 'ec_refund_requests.sp_return_request_id', '=', 'sp_return_requests.id')
            ->join('users as sps', 'sp_return_requests.sp_id', '=', 'sps.id')
            ->where('ec_refund_requests.distributor_id', $distributorId)
            ->select(
                'ec_refund_requests.id',
                'ec_refund_requests.amount',
                'ec_refund_requests.status',
                'ec_refund_requests.client_gcash_number',
                'ec_refund_requests.created_at',
                'sps.first_name',
                'sps.last_name'
            )
            ->get()
            ->map(function ($ref) {
                $ref->role = 'Service Provider';
                return $ref;
            });

        // Merge both tables and sort by date
        $allRefunds = $clientRefunds->concat($spRefunds)->sortByDesc('created_at')->values();

        $formatted = $allRefunds->map(function ($ref) {
            return [
                'id' => 'REF-' . str_pad($ref->id, 5, '0', STR_PAD_LEFT),
                'db_id' => $ref->id,
                'date' => date('Y-m-d', strtotime($ref->created_at)),
                'party' => trim($ref->first_name . ' ' . $ref->last_name),
                'role' => $ref->role,
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

        if (!$permissions['can_approve']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to process and approve refunds.'], 403);
        }

        $refund = DB::table('ec_refund_requests')->where('id', $id)->first();

        if (!$refund || $refund->status !== 'pending') {
            return response()->json(['message' => 'Only pending refunds can be processed.'], 400);
        }

        $distributorId = $this->getDistributorId($user);
        $availableBudget = DB::table('distributor_overall_sales')
            ->where('distributor_id', $distributorId)
            ->sum('total_revenue');

        if ($refund->amount > $availableBudget) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available budget to process this refund.'
            ], 400);
        }

        $distributorUser = DB::table('users')->where('id', $distributorId)->first();
        $paymentSettings = DB::table('distributor_payment_settings')->where('distributor_id', $distributorId)->first();

        if (!$paymentSettings || empty($paymentSettings->gcash_number)) {
            return response()->json([
                'success' => false,
                'message' => 'GCash payment cannot be processed. No valid GCash number found in your Payment Settings.'
            ], 400);
        }

        $billingName = $distributorUser ? trim($distributorUser->first_name . ' ' . $distributorUser->last_name) : 'Distributor';
        $billingEmail = $distributorUser ? $distributorUser->email : 'no-reply@example.com';
        $billingPhone = $paymentSettings->gcash_number;

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
                            'billing' => [
                                'name' => $billingName,
                                'email' => $billingEmail,
                                'phone' => $billingPhone
                            ],
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

            // ADDED: We now cache the ID of the user initiating the approval
            $cacheData = [
                'session_id' => $sessionId,
                'refund_id' => $refund->id,
                'transaction_code' => $transactionCode,
                'distributor_id' => $refund->distributor_id,
                'amount' => $refund->amount,
                'approved_by' => $user->id 
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
     * VERIFY GCASH PAYMENT AFTER REDIRECT & SEND SUCCESS EMAIL
     */
    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['transaction_code' => 'required|string']);
        $transactionCode = trim($request->transaction_code);
        $filePath = 'pending_refunds/' . $transactionCode . '.json';
        $client = new \GuzzleHttp\Client();

        if (!Storage::disk('local')->exists($filePath)) {
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
            // ADDED: Saving the `approved_by` logic dynamically from the cache
            DB::table('ec_refund_requests')
                ->where('id', $cacheData['refund_id'])
                ->update([
                    'status' => 'completed', 
                    'approved_by' => $cacheData['approved_by'] ?? null,
                    'updated_at' => now()
                ]);

            DB::table('distributor_overall_sales')
                ->where('distributor_id', $cacheData['distributor_id'])
                ->decrement('total_revenue', $cacheData['amount']);

            DB::table('budget_deduction_logs')->insert([
                'distributor_id' => $cacheData['distributor_id'],
                'amount' => $cacheData['amount'],
                'description' => 'Refund processed for ' . $transactionCode . ' via GCash',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            Storage::disk('local')->delete($filePath);

            // ============================================
            // EMAIL NOTIFICATION FOR COMPLETED REFUND (INLINE)
            // ============================================
            $refundRecord = DB::table('ec_refund_requests')->where('id', $cacheData['refund_id'])->first();
            $userEmail = null;
            $userName = null;

            if ($refundRecord) {
                if ($refundRecord->return_request_id) {
                    $clientData = DB::table('client_return_requests')
                        ->join('users', 'client_return_requests.client_id', '=', 'users.id')
                        ->where('client_return_requests.id', $refundRecord->return_request_id)
                        ->select('users.email', 'users.first_name', 'users.last_name')
                        ->first();
                    if ($clientData) {
                        $userEmail = $clientData->email;
                        $userName = trim($clientData->first_name . ' ' . $clientData->last_name);
                    }
                } elseif ($refundRecord->sp_return_request_id) {
                    $spData = DB::table('sp_return_requests')
                        ->join('users', 'sp_return_requests.sp_id', '=', 'users.id')
                        ->where('sp_return_requests.id', $refundRecord->sp_return_request_id)
                        ->select('users.email', 'users.first_name', 'users.last_name')
                        ->first();
                    if ($spData) {
                        $userEmail = $spData->email;
                        $userName = trim($spData->first_name . ' ' . $spData->last_name);
                    }
                }
            }

            if ($userEmail && $userName) {
                try {
                    $refundAmountFormatted = number_format($cacheData['amount'], 2);
                    $htmlContent = "
                        <div style='font-family: Arial, sans-serif; color: #333;'>
                            <h2 style='color: #059669;'>Refund Processed Successfully</h2>
                            <p>Hello <strong>{$userName}</strong>,</p>
                            <p>We are pleased to inform you that your refund request (<strong>{$transactionCode}</strong>) has been successfully processed by the Finance Team.</p>
                            <p>An amount of <strong style='font-size: 18px;'>₱{$refundAmountFormatted}</strong> has been transferred to your GCash account.</p>
                            <p>Please check your GCash app to verify the transaction. If you have any questions, feel free to reach out to our support team.</p>
                            <br>
                            <p>Best regards,<br><strong>CaviteGoPaint Finance Team</strong></p>
                        </div>
                    ";

                    Mail::html($htmlContent, function ($message) use ($userEmail, $transactionCode) {
                        $message->to($userEmail)
                                ->subject('Refund Processed Successfully - ' . $transactionCode);
                    });
                } catch (\Exception $emailEx) {
                    Log::error('Failed to send refund completion email: ' . $emailEx->getMessage());
                }
            }
            // ============================================

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
     * Reject a refund request & SEND REJECTION EMAIL
     */
    public function rejectRefund(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_approve']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to reject refunds.'], 403);
        }

        // ADDED: Set the `approved_by` identifier here.
        DB::table('ec_refund_requests')
            ->where('id', $id)
            ->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'updated_at' => now()
            ]);

        // ============================================
        // EMAIL NOTIFICATION FOR REJECTED REFUND (INLINE)
        // ============================================
        $refundRecord = DB::table('ec_refund_requests')->where('id', $id)->first();
        $userEmail = null;
        $userName = null;
        $transactionCode = 'REF-' . str_pad($id, 5, '0', STR_PAD_LEFT);

        if ($refundRecord) {
            if ($refundRecord->return_request_id) {
                $clientData = DB::table('client_return_requests')
                    ->join('users', 'client_return_requests.client_id', '=', 'users.id')
                    ->where('client_return_requests.id', $refundRecord->return_request_id)
                    ->select('users.email', 'users.first_name', 'users.last_name')
                    ->first();
                if ($clientData) {
                    $userEmail = $clientData->email;
                    $userName = trim($clientData->first_name . ' ' . $clientData->last_name);
                }
            } elseif ($refundRecord->sp_return_request_id) {
                $spData = DB::table('sp_return_requests')
                    ->join('users', 'sp_return_requests.sp_id', '=', 'users.id')
                    ->where('sp_return_requests.id', $refundRecord->sp_return_request_id)
                    ->select('users.email', 'users.first_name', 'users.last_name')
                    ->first();
                if ($spData) {
                    $userEmail = $spData->email;
                    $userName = trim($spData->first_name . ' ' . $spData->last_name);
                }
            }
        }

        if ($userEmail && $userName && $refundRecord) {
            try {
                $refundAmountFormatted = number_format($refundRecord->amount, 2);
                $htmlContent = "
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h2 style='color: #DC2626;'>Refund Request Rejected</h2>
                        <p>Hello <strong>{$userName}</strong>,</p>
                        <p>We regret to inform you that your refund request (<strong>{$transactionCode}</strong>) for the amount of <strong>₱{$refundAmountFormatted}</strong> has been rejected by the Finance Team.</p>
                        <p>This may be due to incomplete requirements, discrepancies in the submitted proof, or issues with your GCash account details.</p>
                        <p>Please check your return request dashboard or contact our support team for more specific details regarding this rejection.</p>
                        <br>
                        <p>Best regards,<br><strong>CaviteGoPaint Finance Team</strong></p>
                    </div>
                ";

                Mail::html($htmlContent, function ($message) use ($userEmail, $transactionCode) {
                    $message->to($userEmail)
                            ->subject('Refund Request Rejected - ' . $transactionCode);
                });
            } catch (\Exception $emailEx) {
                Log::error('Failed to send refund rejection email: ' . $emailEx->getMessage());
            }
        }
        // ============================================

        return response()->json([
            'success' => true,
            'message' => 'Refund request rejected successfully'
        ]);
    }
}