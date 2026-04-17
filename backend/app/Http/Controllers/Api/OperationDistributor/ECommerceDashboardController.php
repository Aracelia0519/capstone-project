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

        // Matching ECommerceReports Valid Statuses
        $validStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

        // Base queries for items to get relevant Order IDs
        $clientItemQuery = DB::table('client_order_items')->where('distributor_id', $distributorId);
        $spItemQuery = DB::table('sp_order_items')->where('distributor_id', $distributorId);

        $clientOrderIds = (clone $clientItemQuery)->pluck('order_id')->unique();
        $spOrderIds = (clone $spItemQuery)->pluck('sp_order_id')->unique();

        // 1. Dashboard Top Stats
        $totalOrders = $clientOrderIds->count() + $spOrderIds->count();

        // Total Sales (Combined from valid client and sp orders matching ECommerceReports)
        $clientSales = DB::table('client_orders')->whereIn('id', $clientOrderIds)->whereIn('status', $validStatuses)->sum('grand_total');
        $spSales = DB::table('sp_orders')->whereIn('id', $spOrderIds)->whereIn('status', $validStatuses)->sum('grand_total');
        $totalSales = $clientSales + $spSales;

        // Pending Orders
        $pendingOrders = DB::table('client_orders')->whereIn('id', $clientOrderIds)->where('status', 'pending')->count()
                       + DB::table('sp_orders')->whereIn('id', $spOrderIds)->where('status', 'pending')->count();

        // Completed Orders
        $completedOrders = DB::table('client_orders')->whereIn('id', $clientOrderIds)->where('status', 'delivered')->count()
                         + DB::table('sp_orders')->whereIn('id', $spOrderIds)->where('status', 'delivered')->count();

        // 2. Sales Overview Data (Last 7 Days)
        $salesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->format('D');
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $dayClientSales = DB::table('client_orders')
                ->whereIn('id', $clientOrderIds)
                ->whereIn('status', $validStatuses)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('grand_total');

            $daySpSales = DB::table('sp_orders')
                ->whereIn('id', $spOrderIds)
                ->whereIn('status', $validStatuses)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('grand_total');
                
            $daySales = $dayClientSales + $daySpSales;

            $salesData[] = [
                'day' => $dayName,
                'raw_amount' => $daySales,
                'amount' => number_format($daySales, 2)
            ];
        }

        $maxSales = max(array_column($salesData, 'raw_amount')) ?: 1;
        foreach ($salesData as &$data) {
            $data['value'] = ($data['raw_amount'] / $maxSales) * 100;
        }

        // 3. Order Status Donut Chart
        $clientOrdersStatus = DB::table('client_orders')->whereIn('id', $clientOrderIds)->pluck('status');
        $spOrdersStatus = DB::table('sp_orders')->whereIn('id', $spOrderIds)->pluck('status');
        
        $allStatuses = $clientOrdersStatus->merge($spOrdersStatus);

        $statusCounts = [
            'Completed' => $allStatuses->filter(fn($s) => $s == 'delivered')->count(),
            'Pending' => $allStatuses->filter(fn($s) => $s == 'pending')->count(),
            'Processing' => $allStatuses->filter(fn($s) => in_array($s, ['confirmed', 'prepared', 'shipped', 'ready_for_pickup']))->count(),
            'Cancelled' => $allStatuses->filter(fn($s) => in_array($s, ['cancelled', 'rejected']))->count(),
        ];

        $totalForPercentage = $allStatuses->count() > 0 ? $allStatuses->count() : 1;
        $orderStatus = [
            ['name' => 'Completed', 'count' => $statusCounts['Completed'], 'percentage' => round(($statusCounts['Completed'] / $totalForPercentage) * 100), 'color' => 'bg-green-500'],
            ['name' => 'Pending', 'count' => $statusCounts['Pending'], 'percentage' => round(($statusCounts['Pending'] / $totalForPercentage) * 100), 'color' => 'bg-yellow-500'],
            ['name' => 'Processing', 'count' => $statusCounts['Processing'], 'percentage' => round(($statusCounts['Processing'] / $totalForPercentage) * 100), 'color' => 'bg-blue-500'],
            ['name' => 'Cancelled', 'count' => $statusCounts['Cancelled'], 'percentage' => round(($statusCounts['Cancelled'] / $totalForPercentage) * 100), 'color' => 'bg-red-500'],
        ];

        // 4. Recent Transactions (Combining Client and SP Orders)
        $recentClientOrders = DB::table('client_orders')
            ->whereIn('id', $clientOrderIds)
            ->select('id', 'order_number', 'grand_total', 'status', 'created_at', 'client_id as user_id', DB::raw("'client' as type"));
            
        $recentSpOrders = DB::table('sp_orders')
            ->whereIn('id', $spOrderIds)
            ->select('id', 'order_number', 'grand_total', 'status', 'created_at', 'service_provider_id as user_id', DB::raw("'sp' as type"));
        
        $recentOrdersRaw = $recentClientOrders->unionAll($recentSpOrders)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $recentOrdersRaw->map(function($order) {
            $uiStatus = 'Processing';
            if ($order->status == 'delivered') $uiStatus = 'Completed';
            elseif ($order->status == 'pending') $uiStatus = 'Pending';
            elseif ($order->status == 'cancelled') $uiStatus = 'Cancelled';

            $clientName = 'Unknown Client';
            if ($order->user_id) {
                $user = DB::table('users')->where('id', $order->user_id)->first();
                if ($user) {
                    $clientName = trim($user->first_name . ' ' . $user->last_name);
                }
            }

            return [
                'id' => $order->id . '_' . $order->type,
                'orderId' => $order->order_number,
                'client' => $clientName,
                'amount' => number_format((float)$order->grand_total, 2),
                'status' => $uiStatus
            ];
        })->values();

        // 5. Best Selling Products (Combining Client and SP Sales)
        $clientProducts = DB::table('client_order_items')
            ->join('client_orders', 'client_orders.id', '=', 'client_order_items.order_id')
            ->where('client_order_items.distributor_id', $distributorId)
            ->where('client_orders.status', 'delivered')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(client_order_items.price * quantity) as total_sales'))
            ->groupBy('product_id')
            ->get();

        $spProducts = DB::table('sp_order_items')
            ->join('sp_orders', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
            ->where('sp_order_items.distributor_id', $distributorId)
            ->where('sp_orders.status', 'delivered')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(sp_order_items.price * quantity) as total_sales'))
            ->groupBy('product_id')
            ->get();

        $mergedProducts = [];
        foreach ([$clientProducts, $spProducts] as $dataset) {
            foreach ($dataset as $item) {
                if (!isset($mergedProducts[$item->product_id])) {
                    $mergedProducts[$item->product_id] = [
                        'product_id' => $item->product_id,
                        'total_sold' => 0,
                        'total_sales' => 0
                    ];
                }
                $mergedProducts[$item->product_id]['total_sold'] += $item->total_sold;
                $mergedProducts[$item->product_id]['total_sales'] += $item->total_sales;
            }
        }

        // Sort by total sold and take the top 5
        usort($mergedProducts, function($a, $b) {
            return $b['total_sold'] - $a['total_sold'];
        });
        $mergedProducts = array_slice($mergedProducts, 0, 5);

        $productIds = array_column($mergedProducts, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $bestSellingProducts = collect($mergedProducts)->map(function($item) use ($products) {
            $product = $products->get($item['product_id']);
            return [
                'id' => $item['product_id'],
                'name' => $product ? $product->name : 'Unknown Product',
                'category' => $product ? $product->category : 'Uncategorized',
                'sales' => number_format((float) $item['total_sales'], 2),
                'sold' => (int) $item['total_sold']
            ];
        })->values();

        return response()->json([
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalSales' => number_format($totalSales, 2),
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