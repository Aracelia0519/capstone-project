<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\DistributorDelivery\ECDeliveryRemittance;
use App\Models\OperationDistributor\DistributorPaymentSetting;
use Carbon\Carbon;

class PaymentManagementController extends Controller
{
    /**
     * Reusable method to resolve the main distributor ID based on the logged-in user's role
     */
    private function resolveDistributorId($user)
    {
        $distributorId = $user->id;

        // Dynamically check the specific role table to get the parent_distributor_id
        if ($user->role === 'operational_distributor') {
            $distributorId = DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'employee') {
            $distributorId = DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'hr_manager') {
            $distributorId = DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        } elseif ($user->role === 'finance_manager') {
            $distributorId = DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $distributorId;
        }

        return $distributorId;
    }

    /**
     * Fetch all payments for the resolved distributor
     */
    public function index()
    {
        $user = Auth::user();
        $distributorId = $this->resolveDistributorId($user);

        // Fetch orders associated with this distributor's products
        $orders = ClientOrder::whereHas('items', function ($query) use ($distributorId) {
            $query->where('distributor_id', $distributorId);
        })->with(['client', 'items' => function ($query) use ($distributorId) {
            $query->where('distributor_id', $distributorId);
        }])->get();

        $payments = [];

        // Pre-fetch all delivered remittances to optimize query
        $remittances = ECDeliveryRemittance::whereIn('order_id', $orders->pluck('id'))->pluck('order_id')->toArray();

        foreach ($orders as $order) {
            // Only make it "Completed" if there is data in the ec_delivery_remittances table
            $isCompleted = in_array($order->id, $remittances);
            
            // Format Client Name safely 
            $clientName = 'Customer';
            if ($order->client) {
                $clientName = trim(($order->client?->first_name ?? '') . ' ' . ($order->client?->last_name ?? ''));
                if (empty($clientName)) {
                    $clientName = $order->client?->name ?? 'Customer';
                }
            }

            // Cleanly format Payment Method for UI matching
            $rawMethod = strtolower($order->payment_method ?? 'cod');
            if ($rawMethod === 'pick-up' || $rawMethod === 'pickup') {
                $methodName = 'Pick-Up';
            } elseif ($rawMethod === 'gcash') {
                $methodName = 'GCash';
            } else {
                $methodName = strtoupper($rawMethod);
            }

            $payments[] = [
                'id' => $order->id,
                'paymentId' => 'PAY-' . str_replace('ORD-', '', $order->order_number),
                'orderId' => $order->order_number,
                'client' => $clientName,
                'method' => $methodName,
                'amount' => (float) $order->grand_total,
                'status' => $isCompleted ? 'Completed' : 'Pending', // Force UI to Pending until remitted
                'date' => Carbon::parse($order->created_at)->format('Y-m-d'),
                'time' => Carbon::parse($order->created_at)->format('H:i'),
                'referenceNumber' => $isCompleted ? 'REM-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) : '',
                'transactionId' => '',
                'receipt' => $isCompleted
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get the current payment settings for the distributor
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        $distributorId = $this->resolveDistributorId($user);

        // Fetch or Initialize default settings if not exists
        $settings = DistributorPaymentSetting::firstOrCreate(
            ['distributor_id' => $distributorId],
            [
                'is_cod_enabled' => true,
                'is_gcash_enabled' => false,
                'is_pickup_enabled' => false,
                'gcash_number' => null
            ]
        );

        return response()->json([
            'success' => true, 
            'data' => $settings
        ]);
    }

    /**
     * Update the payment settings for the distributor
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_cod_enabled' => 'required|boolean',
            'is_gcash_enabled' => 'required|boolean',
            'is_pickup_enabled' => 'required|boolean',
            // If GCash is enabled, the number is strictly required
            'gcash_number' => 'required_if:is_gcash_enabled,true|nullable|string|max:20'
        ]);

        $user = Auth::user();
        $distributorId = $this->resolveDistributorId($user);

        $settings = DistributorPaymentSetting::updateOrCreate(
            ['distributor_id' => $distributorId],
            [
                'is_cod_enabled' => $request->is_cod_enabled,
                'is_gcash_enabled' => $request->is_gcash_enabled,
                'is_pickup_enabled' => $request->is_pickup_enabled,
                'gcash_number' => $request->is_gcash_enabled ? $request->gcash_number : null,
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Payment settings updated successfully!', 
            'data' => $settings
        ]);
    }
}