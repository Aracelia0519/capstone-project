<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierDashboardController extends Controller
{
    public function index()
    {
        $supplierId = Auth::id();

        // 1. Dashboard Key Stats
        $totalRevenue = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['delivered', 'completed'])
            ->sum('total_cost');

        $totalOrders = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->count();

        $totalProducts = DB::table('supplier_raw_materials')
            ->where('user_id', $supplierId)
            ->where('is_active', 1)
            ->count();

        $activePartners = DB::table('supplier_partners')
            ->where('supplier_id', $supplierId)
            ->where('status', 'active')
            ->count();

        // 2. Charts Data
        
        // A. Revenue Trajectory (Line Chart - Last 12 Months)
        $revLabels = [];
        $revData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revLabels[] = $month->format('M Y');
            
            $monthlyRevenue = DB::table('procurement_requests')
                ->where('supplier_id', $supplierId)
                ->whereIn('status', ['delivered', 'completed'])
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('total_cost');
                
            $revData[] = (float) $monthlyRevenue;
        }

        // B. Category Distribution (Doughnut Chart)
        $categories = DB::table('supplier_raw_materials')
            ->where('user_id', $supplierId)
            ->where('is_active', 1)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();
            
        $catLabels = $categories->pluck('category')->toArray();
        $catData = $categories->pluck('count')->toArray();

        // C. Top Performing Products (Vertical Bar Chart)
        $topProducts = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['delivered', 'completed'])
            ->select('product_name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
            
        $topProdLabels = $topProducts->pluck('product_name')->toArray();
        $topProdData = $topProducts->pluck('total_sold')->toArray();

        // D. Order Status Heatmap (Horizontal Bar Chart)
        $orderStatuses = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
            
        $statusLabels = $orderStatuses->map(function($item) {
            return ucfirst(str_replace('_', ' ', $item->status));
        })->toArray();
        $statusData = $orderStatuses->pluck('count')->toArray();

        // 3. Recent Order History
        $recentOrders = DB::table('procurement_requests')
            ->join('users', 'procurement_requests.distributor_id', '=', 'users.id')
            ->leftJoin('distributor_requirements', 'users.id', '=', 'distributor_requirements.user_id')
            ->where('procurement_requests.supplier_id', $supplierId)
            ->orderBy('procurement_requests.created_at', 'desc')
            ->select(
                'procurement_requests.request_code as id',
                DB::raw('COALESCE(distributor_requirements.company_name, users.first_name) as customer'),
                'procurement_requests.product_name as product',
                'procurement_requests.status',
                'procurement_requests.total_cost as amount'
            )
            ->limit(5)
            ->get();

        // 4. Pending Fulfillments
        $pendingFulfillments = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['pending', 'processing'])
            ->select('product_name as name', 'request_code as code', 'quantity')
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'revenue' => number_format($totalRevenue, 2),
                'orders' => $totalOrders,
                'products' => $totalProducts,
                'partners' => $activePartners
            ],
            'charts' => [
                'revenue_trajectory' => [
                    'labels' => $revLabels,
                    'data' => $revData
                ],
                'category_distribution' => [
                    'labels' => $catLabels,
                    'data' => $catData
                ],
                'top_products' => [
                    'labels' => $topProdLabels,
                    'data' => $topProdData
                ],
                'order_status' => [
                    'labels' => $statusLabels,
                    'data' => $statusData
                ]
            ],
            'recentOrders' => $recentOrders,
            'pendingFulfillments' => $pendingFulfillments
        ]);
    }
}