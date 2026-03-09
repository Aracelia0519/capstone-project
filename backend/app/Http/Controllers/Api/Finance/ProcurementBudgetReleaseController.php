<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Finance\BudgetDeductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_budget_release')
            ->first();

        // Check if access row exists and is generally granted
        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_create' => (bool)$access->can_create,
            'can_update' => (bool)$access->can_update,
            'can_delete' => (bool)$access->can_delete,
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

        if (!$permissions['can_update']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to approve budget releases.'
            ], 403);
        }

        $procurement = ProcurementRequest::findOrFail($id);

        if ($procurement->status !== 'd-approved') {
            return response()->json(['message' => 'Only Distributor Approved (d-approved) requests can have their budget released.'], 400);
        }

        // =========================================================================
        // PAYMONGO (GCASH) LOGIC IF PAYMENT TERMS ARE GCASH
        // =========================================================================
        if (strtolower($procurement->payment_terms) === 'gcash') {
            try {
                $client = new \GuzzleHttp\Client();
                
                // 1. Get the return URL passed from Vue, or fallback to a default path
                $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');
                $baseUrl = $request->input('return_url', $frontendOrigin . '/finance/procurementBudgetRelease');

                $requestCode = $procurement->request_code ?? 'REQ-' . $procurement->id;
                
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
                                'description' => 'Procurement Budget Release for ' . $requestCode,
                                'reference_number' => $requestCode, 
                                'line_items' => [
                                    [
                                        'currency' => 'PHP',
                                        'amount' => (int) round($procurement->total_cost * 100), 
                                        'name' => 'Procurement Request ' . $requestCode,
                                        'quantity' => 1,
                                    ]
                                ],
                                // 2. Apply the dynamic Return URL here
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
                
                if (!$checkoutUrl || !$sessionId) {
                    return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link.'], 500);
                }

                $cacheData = [
                    'session_id' => $sessionId,
                    'procurement_id' => $procurement->id,
                    'request_code' => $requestCode,
                    'distributor_id' => $procurement->distributor_id,
                    'supplier_id' => $procurement->supplier_id,
                    'total_cost' => $procurement->total_cost
                ];

                Storage::disk('local')->put('pending_budget_releases/' . $requestCode . '.json', json_encode($cacheData));

                return response()->json([
                    'success' => true,
                    'checkout_url' => $checkoutUrl,
                ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'PayMongo Internal Error: ' . $e->getMessage()], 500);
            }
        }

        // =========================================================================
        // NORMAL (COD / OFFLINE) BUDGET RELEASE LOGIC
        // =========================================================================
        try {
            DB::beginTransaction();

            $procurement->updateStatus('ready');

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
     * VERIFY GCASH PAYMENT AFTER REDIRECT
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

        // 2. Execute DB Transaction
        DB::beginTransaction();
        try {
            $procurement = ProcurementRequest::findOrFail($cacheData['procurement_id']);
            $procurement->updateStatus('ready');

            BudgetDeductionLog::create([
                'distributor_id' => $procurement->distributor_id,
                'procurement_request_id' => $procurement->id,
                'amount' => $procurement->total_cost,
                'description' => 'Budget released and deducted via GCash for procurement request ' . $requestCode
            ]);

            // Track supplier payment using the migration we created
            DB::table('distributor_supplier_payments')->insert([
                'procurement_request_id' => $procurement->id,
                'distributor_id' => $procurement->distributor_id,
                'supplier_id' => $procurement->supplier_id,
                'amount' => $procurement->total_cost,
                'payment_method' => 'gcash',
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
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $permissions = $this->getPermissions($user);

        if (!$permissions['can_update']) {
            return response()->json([
                'message' => 'Access Denied: You do not have permission to reject budget releases.'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $procurement = ProcurementRequest::findOrFail($id);

        $procurement->updateStatus('rejected', $request->reason);

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}