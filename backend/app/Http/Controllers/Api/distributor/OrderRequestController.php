<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderRequestController extends Controller
{
    /**
     * Resolve the distributor ID based on the logged-in user's role.
     */
    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    /**
     * Fetch all Client and SP Orders associated with this Distributor
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $distributorId = $this->getDistributorId($user);

            // 1. Fetch Client Orders
            $clientOrders = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->join('users', 'client_orders.client_id', '=', 'users.id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->select(
                    'client_orders.id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as customer_name"),
                    DB::raw("'Client' as type"),
                    'client_orders.status',
                    'client_orders.created_at',
                    'client_orders.grand_total as total_amount'
                )
                ->distinct()
                ->get();

            // 2. Fetch Service Provider Orders
            // FIXED: Changed 'sp_orders.sp_id' to 'sp_orders.service_provider_id'
            $spOrders = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->join('users', 'sp_orders.service_provider_id', '=', 'users.id') 
                ->where('sp_order_items.distributor_id', $distributorId)
                ->select(
                    'sp_orders.id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as customer_name"),
                    DB::raw("'Service Provider' as type"),
                    'sp_orders.status',
                    'sp_orders.created_at',
                    'sp_orders.grand_total as total_amount'
                )
                ->distinct()
                ->get();

            // Merge collections and sort by newest first
            $allOrders = collect($clientOrders)
                ->merge($spOrders)
                ->sortByDesc('created_at')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $allOrders
            ]);

        } catch (\Exception $e) {
            Log::error('Distributor Orders Request Fetch Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch order requests data.'
            ], 500);
        }
    }
}