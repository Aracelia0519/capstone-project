<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\DistributorDelivery\ECDeliveryRemittance;
use Carbon\Carbon;

class PaymentManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. Determine the correct Distributor ID
        // Start by assuming the user is the main distributor
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

        // 2. Fetch orders that belong to this correct distributor
        $orders = ClientOrder::with(['client', 'items' => function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            }])
            ->whereHas('items', function($query) use ($distributorId) {
                $query->where('distributor_id', $distributorId);
            })
            ->where('payment_method', 'cod') // Only fetch Cash on Delivery for now
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Fetch all remittance records for this distributor to check completed payments
        $remittances = ECDeliveryRemittance::where('distributor_id', $distributorId)
            ->pluck('order_id')
            ->toArray();

        $payments = [];

        foreach ($orders as $order) {
            // 4. Only make it "Completed" if there is data in the ec_delivery_remittances table
            $isCompleted = in_array($order->id, $remittances);
            
            // Format Client Name safely 
            $clientName = 'Customer';
            if ($order->client) {
                $clientName = trim(($order->client?->first_name ?? '') . ' ' . ($order->client?->last_name ?? ''));
                if (empty($clientName)) {
                    $clientName = $order->client?->name ?? 'Customer';
                }
            }

            $payments[] = [
                'id' => $order->id,
                'paymentId' => 'PAY-' . str_replace('ORD-', '', $order->order_number),
                'orderId' => $order->order_number,
                'client' => $clientName,
                'method' => 'COD',
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
}