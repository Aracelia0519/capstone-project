<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\Distributor\Product;

class ECommerceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $distributorId = $user->id;

        // Safely query the operational_distributors table
        if ($user->role === 'operational_distributor') {
            $opDist = DB::table('operational_distributors')
                ->where('user_id', $user->id)
                ->first();
                
            if ($opDist) {
                $distributorId = $opDist->parent_distributor_id;
            }
        }

        // 1. Dashboard Top Stats
        $totalOrders = DB::table('client_order_items')
            ->where('distributor_id', $distributorId)
            ->distinct('order_id')
            ->count('order_id');

        $totalRevenue = (float) DB::table('ec_delivery_remittances')
            ->where('distributor_id', $distributorId)
            ->sum('amount');

        $pendingOrders = DB::table('client_order_items')
            ->join('client_orders', 'client_order_items.order_id', '=', 'client_orders.id')
            ->where('client_order_items.distributor_id', $distributorId)
            ->where('client_orders.status', 'pending')
            ->distinct('client_order_items.order_id')
            ->count('client_order_items.order_id');

        $completedOrders = DB::table('client_order_items')
            ->join('client_orders', 'client_order_items.order_id', '=', 'client_orders.id')
            ->where('client_order_items.distributor_id', $distributorId)
            ->where('client_orders.status', 'delivered')
            ->distinct('client_order_items.order_id')
            ->count('client_order_items.order_id');

        // 2. Sales Overview Data (Last 7 Days)
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->format('D');
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $dayRevenue = (float) DB::table('ec_delivery_remittances')
                ->join('client_orders', 'ec_delivery_remittances.order_id', '=', 'client_orders.id')
                ->where('ec_delivery_remittances.distributor_id', $distributorId)
                ->whereBetween('client_orders.created_at', [$startOfDay, $endOfDay])
                ->sum('ec_delivery_remittances.amount');

            $salesData[] = [
                'day' => $dayName,
                'raw_amount' => $dayRevenue,
                'amount' => number_format($dayRevenue, 2)
            ];
        }

        $maxSales = max(array_column($salesData, 'raw_amount')) ?: 1;
        foreach ($salesData as &$data) {
            $data['value'] = ($data['raw_amount'] / $maxSales) * 100;
        }

        // 3. Order Status Donut Chart
        $allDistributorOrders = DB::table('client_order_items')
            ->join('client_orders', 'client_order_items.order_id', '=', 'client_orders.id')
            ->where('client_order_items.distributor_id', $distributorId)
            ->select('client_orders.id', 'client_orders.status')
            ->distinct('client_orders.id')
            ->get();

        $statusCounts = [
            'Completed' => $allDistributorOrders->where('status', 'delivered')->count(),
            'Pending' => $allDistributorOrders->where('status', 'pending')->count(),
            'Processing' => $allDistributorOrders->whereIn('status', ['confirmed', 'prepared', 'shipped'])->count(),
            'Cancelled' => $allDistributorOrders->where('status', 'cancelled')->count(),
        ];

        $totalForPercentage = $totalOrders > 0 ? $totalOrders : 1;
        $orderStatus = [
            ['name' => 'Completed', 'count' => $statusCounts['Completed'], 'percentage' => round(($statusCounts['Completed'] / $totalForPercentage) * 100), 'color' => 'bg-green-500'],
            ['name' => 'Pending', 'count' => $statusCounts['Pending'], 'percentage' => round(($statusCounts['Pending'] / $totalForPercentage) * 100), 'color' => 'bg-yellow-500'],
            ['name' => 'Processing', 'count' => $statusCounts['Processing'], 'percentage' => round(($statusCounts['Processing'] / $totalForPercentage) * 100), 'color' => 'bg-blue-500'],
            ['name' => 'Cancelled', 'count' => $statusCounts['Cancelled'], 'percentage' => round(($statusCounts['Cancelled'] / $totalForPercentage) * 100), 'color' => 'bg-red-500'],
        ];

        // 4. Recent Transactions
        $recentOrderIds = DB::table('client_order_items')
            ->where('distributor_id', $distributorId)
            ->orderBy('created_at', 'desc')
            ->pluck('order_id')
            ->unique()
            ->take(5);

        $recentTransactions = ClientOrder::with(['client', 'items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            }])
            ->whereIn('id', $recentOrderIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) use ($distributorId) {
                
                $remittance = DB::table('ec_delivery_remittances')
                    ->where('order_id', $order->id)
                    ->where('distributor_id', $distributorId)
                    ->first();
                    
                $amount = $remittance ? (float) $remittance->amount : (float) $order->grand_total;

                $uiStatus = 'Processing';
                if ($order->status == 'delivered') $uiStatus = 'Completed';
                if ($order->status == 'pending') $uiStatus = 'Pending';
                if ($order->status == 'cancelled') $uiStatus = 'Cancelled';

                // FIXED: Use first_name and last_name from the User model instead of "name"
                $clientName = 'Unknown Client';
                if ($order->client) {
                    $clientName = trim($order->client->first_name . ' ' . $order->client->last_name);
                    if (empty($clientName)) {
                        $clientName = 'Unknown Client';
                    }
                }

                return [
                    'id' => $order->id,
                    'orderId' => $order->order_number,
                    'client' => $clientName,
                    'amount' => number_format($amount, 2),
                    'status' => $uiStatus
                ];
            })->values(); // Ensure it converts nicely to JSON

        // 5. Best Selling Products
        $bestSellingData = DB::table('client_order_items')
            ->select('client_order_items.product_id', DB::raw('SUM(client_order_items.quantity) as total_sold'), DB::raw('SUM(client_order_items.price * client_order_items.quantity) as total_sales'))
            ->join('client_orders', 'client_order_items.order_id', '=', 'client_orders.id')
            ->where('client_order_items.distributor_id', $distributorId)
            ->where('client_orders.status', 'delivered')
            ->groupBy('client_order_items.product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $productIds = $bestSellingData->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $bestSellingProducts = $bestSellingData->map(function($item) use ($products) {
            $product = $products->get($item->product_id);
            return [
                'id' => $item->product_id,
                'name' => $product ? $product->name : 'Unknown Product',
                'category' => $product ? $product->category : 'Uncategorized',
                'sales' => number_format((float) $item->total_sales, 2),
                'sold' => (int) $item->total_sold
            ];
        })->values(); // Ensure it converts nicely to JSON

        return response()->json([
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => number_format($totalRevenue, 2),
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
            ],
            'salesData' => $salesData,
            'orderStatus' => $orderStatus,
            'recentTransactions' => $recentTransactions,
            'bestSellingProducts' => $bestSellingProducts,
        ]);
    }
}