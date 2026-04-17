<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DistributorReportController extends Controller
{
    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    public function getCombinedReports(Request $request)
    {
        try {
            $user = Auth::user();
            $distributorId = $this->getDistributorId($user);

            $period = $request->input('period', 'month');
            $startDate = Carbon::now();
            $endDate = Carbon::now();
            $prevStartDate = Carbon::now();
            $prevEndDate = Carbon::now();

            // Date Range Handling
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    $prevStartDate = Carbon::yesterday();
                    $prevEndDate = Carbon::yesterday()->endOfDay();
                    break;
                case 'yesterday':
                    $startDate = Carbon::yesterday();
                    $endDate = Carbon::yesterday()->endOfDay();
                    $prevStartDate = Carbon::yesterday()->subDay();
                    $prevEndDate = Carbon::yesterday()->subDay()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    $prevStartDate = Carbon::now()->subWeek()->startOfWeek();
                    $prevEndDate = Carbon::now()->subWeek()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $prevStartDate = Carbon::now()->subMonth()->startOfMonth();
                    $prevEndDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    $prevStartDate = Carbon::now()->subMonths(2)->startOfMonth();
                    $prevEndDate = Carbon::now()->subMonths(2)->endOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->startOfQuarter();
                    $endDate = Carbon::now()->endOfQuarter();
                    $prevStartDate = Carbon::now()->subQuarter()->startOfQuarter();
                    $prevEndDate = Carbon::now()->subQuarter()->endOfQuarter();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    $prevStartDate = Carbon::now()->subYear()->startOfYear();
                    $prevEndDate = Carbon::now()->subYear()->endOfYear();
                    break;
                case 'custom':
                    $startDate = Carbon::parse($request->input('startDate', Carbon::now()->startOfMonth()))->startOfDay();
                    $endDate = Carbon::parse($request->input('endDate', Carbon::now()->endOfMonth()))->endOfDay();
                    // For custom, prev period is same duration backward
                    $days = $startDate->diffInDays($endDate);
                    $prevStartDate = (clone $startDate)->subDays($days + 1);
                    $prevEndDate = (clone $startDate)->subDay()->endOfDay();
                    break;
            }

            $validStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

            // ==========================================
            // 1. OPERATIONS & FINANCE AGGREGATIONS
            // ==========================================
            $getSalesData = function($start, $end) use ($distributorId, $validStatuses) {
                $clientOrders = DB::table('client_orders')
                    ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                    ->where('client_order_items.distributor_id', $distributorId)
                    ->whereIn('client_orders.status', $validStatuses)
                    ->whereBetween('client_orders.created_at', [$start, $end])
                    ->select('client_orders.id', 'client_orders.grand_total', 'client_order_items.quantity')
                    ->get();

                $spOrders = DB::table('sp_orders')
                    ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                    ->where('sp_order_items.distributor_id', $distributorId)
                    ->whereIn('sp_orders.status', $validStatuses)
                    ->whereBetween('sp_orders.created_at', [$start, $end])
                    ->select('sp_orders.id', 'sp_orders.grand_total', 'sp_order_items.quantity')
                    ->get();

                $uniqueClientOrders = $clientOrders->unique('id');
                $uniqueSpOrders = $spOrders->unique('id');

                return [
                    'revenue' => $uniqueClientOrders->sum('grand_total') + $uniqueSpOrders->sum('grand_total'),
                    'orders' => $uniqueClientOrders->count() + $uniqueSpOrders->count(),
                    'quantity' => $clientOrders->sum('quantity') + $spOrders->sum('quantity')
                ];
            };

            $currentMetrics = $getSalesData($startDate, $endDate);
            $prevMetrics = $getSalesData($prevStartDate, $prevEndDate);

            $revenueGrowth = $prevMetrics['revenue'] > 0 ? (($currentMetrics['revenue'] - $prevMetrics['revenue']) / $prevMetrics['revenue']) * 100 : ($currentMetrics['revenue'] > 0 ? 100 : 0);
            $orderGrowth = $prevMetrics['orders'] > 0 ? (($currentMetrics['orders'] - $prevMetrics['orders']) / $prevMetrics['orders']) * 100 : ($currentMetrics['orders'] > 0 ? 100 : 0);

            $monthlySummary = [
                'totalRevenue' => round($currentMetrics['revenue'], 2),
                'totalQuantity' => $currentMetrics['quantity'],
                'totalOrders' => $currentMetrics['orders'],
                'averageOrderValue' => $currentMetrics['orders'] > 0 ? round($currentMetrics['revenue'] / $currentMetrics['orders'], 2) : 0,
                'revenueGrowth' => round($revenueGrowth, 1),
                'orderGrowth' => round($orderGrowth, 1)
            ];

            // ==========================================
            // 2. HR SUMMARY ADDITION (Combined reporting)
            // ==========================================
            $totalEmployees = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->count();
            $activeAttendances = DB::table('employee_attendances')
                ->where('distributor_id', $distributorId)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            
            $hrSummary = [
                'totalEmployees' => $totalEmployees,
                'activeAttendances' => $activeAttendances
            ];

            // ==========================================
            // 3. MONTHLY CHART DATA (Last 12 Months)
            // ==========================================
            $monthlyChartData = [];
            for ($i = 11; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
                $mMetrics = $getSalesData($mStart, $mEnd);
                $monthlyChartData[] = [
                    'month' => $mStart->format('M'),
                    'revenue' => round($mMetrics['revenue'], 2)
                ];
            }

            // ==========================================
            // 4. TOP SELLING PRODUCTS
            // ==========================================
            $clientProducts = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->join('distributor_products', 'client_order_items.product_id', '=', 'distributor_products.id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validStatuses)
                ->whereBetween('client_orders.created_at', [$startDate, $endDate])
                ->select('distributor_products.id', 'distributor_products.name', 'distributor_products.sku_code', DB::raw('SUM(client_order_items.price * client_order_items.quantity) as revenue'), DB::raw('SUM(client_order_items.quantity) as quantity'))
                ->groupBy('distributor_products.id', 'distributor_products.name', 'distributor_products.sku_code')
                ->get();

            $spProducts = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->join('distributor_products', 'sp_order_items.product_id', '=', 'distributor_products.id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validStatuses)
                ->whereBetween('sp_orders.created_at', [$startDate, $endDate])
                ->select('distributor_products.id', 'distributor_products.name', 'distributor_products.sku_code', DB::raw('SUM(sp_order_items.price * sp_order_items.quantity) as revenue'), DB::raw('SUM(sp_order_items.quantity) as quantity'))
                ->groupBy('distributor_products.id', 'distributor_products.name', 'distributor_products.sku_code')
                ->get();

            $mergedProducts = [];
            foreach ([$clientProducts, $spProducts] as $dataset) {
                foreach ($dataset as $product) {
                    if (!isset($mergedProducts[$product->id])) {
                        $mergedProducts[$product->id] = [
                            'id' => $product->id, 
                            'name' => $product->name, 
                            'brand' => 'In-House', // Adjust based on your schema
                            'finish' => 'Standard', // Adjust based on your schema
                            'sku' => $product->sku_code,
                            'revenue' => 0, 
                            'quantity' => 0
                        ];
                    }
                    $mergedProducts[$product->id]['revenue'] += (float)$product->revenue;
                    $mergedProducts[$product->id]['quantity'] += (int)$product->quantity;
                }
            }

            $colors = ['#3B82F6', '#10B981', '#8B5CF6', '#EF4444', '#F59E0B'];
            $topSellingPaints = collect($mergedProducts)
                ->sortByDesc('revenue')
                ->take(5)
                ->values()
                ->map(function($product, $index) use ($currentMetrics, $colors) {
                    $product['marketShare'] = $currentMetrics['revenue'] > 0 ? round(($product['revenue'] / $currentMetrics['revenue']) * 100, 1) : 0;
                    $product['color'] = $colors[$index % count($colors)];
                    return $product;
                });

            // ==========================================
            // 5. STOCK MOVEMENT (INVENTORY DSS)
            // ==========================================
            $inventoryData = DB::table('distributor_products')
                ->leftJoin('distributor_inventories', function($join) use ($distributorId) {
                    $join->on('distributor_products.id', '=', 'distributor_inventories.product_id')
                         ->where('distributor_inventories.distributor_id', '=', $distributorId);
                })
                ->leftJoin('inactive_distributor_inventories', function($join) use ($distributorId) {
                    $join->on('distributor_products.id', '=', 'inactive_distributor_inventories.product_id')
                         ->where('inactive_distributor_inventories.distributor_id', '=', $distributorId);
                })
                ->where('distributor_products.distributor_id', $distributorId)
                ->select(
                    'distributor_products.id', 
                    'distributor_products.name', 
                    'distributor_products.sku_code',
                    'distributor_products.min_stock_level',
                    'distributor_products.max_stock_level',
                    DB::raw('COALESCE(distributor_inventories.quantity, 0) + COALESCE(inactive_distributor_inventories.quantity, 0) as currentStock')
                )
                ->get();

            $stockMovement = [];
            foreach ($inventoryData as $index => $item) {
                $minReq = $item->min_stock_level > 0 ? $item->min_stock_level : 1;
                $maxReq = $item->max_stock_level > 0 ? $item->max_stock_level : $minReq * 2;
                $percentage = round(($item->currentStock / $maxReq) * 100);
                
                if ($item->currentStock <= 0) $status = 'Critical';
                elseif ($item->currentStock <= $minReq) $status = 'Low Stock';
                elseif ($item->currentStock >= $maxReq) $status = 'Overstock';
                else $status = 'Adequate';

                $stockMovement[] = [
                    'id' => 'STK-' . $item->id,
                    'name' => $item->name,
                    'brand' => 'System Product',
                    'finish' => 'Standard',
                    'sku' => $item->sku_code,
                    'color' => $colors[$index % count($colors)] ?? '#9CA3AF',
                    'currentStock' => (int)$item->currentStock,
                    'minRequired' => (int)$minReq,
                    'status' => $status,
                    'stockPercentage' => $percentage > 100 ? 100 : $percentage,
                    'lastMovement' => [
                        'type' => 'System Sync',
                        'quantity' => 0,
                        'date' => Carbon::today()->format('Y-m-d')
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'monthlySummary' => $monthlySummary,
                    'hrSummary' => $hrSummary,
                    'monthlyChartData' => $monthlyChartData,
                    'topSellingPaints' => $topSellingPaints,
                    'stockMovement' => $stockMovement
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Combined Reports Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate combined reports.', 'error' => $e->getMessage()], 500);
        }
    }
}