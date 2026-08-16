<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $supplierId = Auth::id();
        $perPage = $request->get('per_page', 10); // 10 items per page

        // 1. KPI Metrics for Delivered Orders (Overall, not paginated)
        $deliveredQuery = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['delivered', 'completed']);

        $totalDeliveredCount = (clone $deliveredQuery)->count();
        $totalDeliveredRevenue = (clone $deliveredQuery)->sum('total_cost');
        $totalItemsDelivered = (clone $deliveredQuery)->sum('quantity');

        // 2. Detailed Order History with Deliveries, Receipts & Distributor Info (Paginated)
        $orders = DB::table('procurement_requests')
            ->join('users', 'procurement_requests.distributor_id', '=', 'users.id')
            ->leftJoin('distributor_requirements', 'users.id', '=', 'distributor_requirements.user_id')
            ->leftJoin('supplier_deliveries', 'procurement_requests.id', '=', 'supplier_deliveries.procurement_request_id')
            ->leftJoin('procurement_fulfillments', 'procurement_requests.id', '=', 'procurement_fulfillments.procurement_request_id')
            ->where('procurement_requests.supplier_id', $supplierId)
            ->whereIn('procurement_requests.status', ['delivered', 'completed'])
            ->select(
                'procurement_requests.id',
                'procurement_requests.request_code as code',
                DB::raw('COALESCE(distributor_requirements.company_name, users.first_name) as distributor_name'),
                'procurement_requests.product_name',
                'procurement_requests.category',
                'procurement_requests.quantity',
                'procurement_requests.total_cost',
                'procurement_requests.status',
                'procurement_requests.delivered_at',
                'procurement_requests.created_at',
                'supplier_deliveries.arrival_proof_path',
                'supplier_deliveries.shipping_proof_path',
                'procurement_fulfillments.receipt_file_path',
                'procurement_fulfillments.proof_file_path'
            )
            ->orderBy('procurement_requests.delivered_at', 'desc')
            ->paginate($perPage);

        // 3. Chart Metrics (Last 12 Months Delivered Revenue & Categories)
        $labels = [];
        $revenueData = [];
        for ($i = 11; $i >= 0; $i--) { // Changed to 11 to get 12 months
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            
            $rev = DB::table('procurement_requests')
                ->where('supplier_id', $supplierId)
                ->whereIn('status', ['delivered', 'completed'])
                ->whereMonth('delivered_at', $month->month)
                ->whereYear('delivered_at', $month->year)
                ->sum('total_cost');
                
            $revenueData[] = (float) $rev;
        }

        $categories = DB::table('procurement_requests')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['delivered', 'completed'])
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();

        return response()->json([
            'kpis' => [
                'total_delivered' => $totalDeliveredCount,
                'total_revenue' => number_format($totalDeliveredRevenue, 2),
                'total_items' => $totalItemsDelivered,
            ],
            'orders' => $orders, // This is now a Laravel paginated object
            'charts' => [
                'monthly_revenue' => [
                    'labels' => $labels,
                    'data' => $revenueData,
                ],
                'categories' => [
                    'labels' => $categories->pluck('category')->toArray(),
                    'data' => $categories->pluck('count')->toArray(),
                ]
            ]
        ]);
    }
}