<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Log;

class ECommerceReportController extends Controller
{
    /**
     * Check RBAC Permissions for Reports Module (Level-Based)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        if ($user->role === 'admin') {
            return ['has_access' => true, 'distributor_id' => null, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        }
        if ($user->role === 'distributor') {
            return ['has_access' => true, 'distributor_id' => $user->id, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        }

        $distributorId = $this->getDistributorId($user);
        if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
            return ['has_access' => true, 'distributor_id' => $distributorId, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        } 
        elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')->where('distributor_id', $employee->parent_distributor_id)->where('title', $employee->position)->first();
                if ($position) {
                    $access = DB::table('position_accessibilities')->where('position_id', $position->id)->where('permission_key', 'ec_reports')->first();
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => ['can_view' => (bool)$access->can_view, 'can_manage' => (bool)$access->can_manage, 'can_approve' => (bool)$access->can_approve,]
                            ];
                        }
                    }
                }
            }
        }
        return ['has_access' => false, 'distributor_id' => null, 'permissions' => ['can_view' => false, 'can_manage' => false, 'can_approve' => false]];
    }

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') {
            $model = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'hr_manager') {
            $model = DB::table('hr_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'finance_manager') {
            $model = DB::table('finance_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'employee') {
            $model = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        return $user->id;
    }

    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized access to reports'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            // Handle Date Ranges
            $dateRange = $request->input('dateRange', 'year');
            $startDate = Carbon::now();
            $endDate = Carbon::now();

            switch ($dateRange) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->startOfQuarter();
                    $endDate = Carbon::now()->endOfQuarter();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                case 'custom':
                    $startDate = Carbon::parse($request->input('fromDate'))->startOfDay();
                    $endDate = Carbon::parse($request->input('toDate'))->endOfDay();
                    break;
            }

            $validStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

            // ==========================================
            // 1. DATA COLLECTION (CLIENT & SP ORDERS)
            // ==========================================

            $clientOrdersQuery = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->whereIn('client_orders.status', $validStatuses)
                ->whereBetween('client_orders.created_at', [$startDate, $endDate]);

            if ($distributorId) {
                $clientOrdersQuery->where('client_order_items.distributor_id', $distributorId);
            }

            $spOrdersQuery = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id') 
                ->whereIn('sp_orders.status', $validStatuses)
                ->whereBetween('sp_orders.created_at', [$startDate, $endDate]);

            if ($distributorId) {
                $spOrdersQuery->where('sp_order_items.distributor_id', $distributorId);
            }

            // ==========================================
            // 2. KEY METRICS CALCULATION (OPTION A: Using Grand Total)
            // ==========================================

            // Get distinct orders to accurately sum grand_total without duplicating if an order has multiple items
            $clientDistinctOrders = (clone $clientOrdersQuery)
                ->select('client_orders.id', 'client_orders.grand_total', DB::raw('DATE(client_orders.created_at) as date'))
                ->distinct()
                ->get();

            $spDistinctOrders = (clone $spOrdersQuery)
                ->select('sp_orders.id', 'sp_orders.grand_total', DB::raw('DATE(sp_orders.created_at) as date'))
                ->distinct()
                ->get();

            $totalSales = $clientDistinctOrders->sum('grand_total') + $spDistinctOrders->sum('grand_total');
            $totalOrders = (int) ($clientDistinctOrders->count() + $spDistinctOrders->count());
            
            $clientItemsSold = (clone $clientOrdersQuery)->sum('client_order_items.quantity');
            $spItemsSold = (clone $spOrdersQuery)->sum('sp_order_items.quantity');
            $totalItemsSold = (int) ($clientItemsSold + $spItemsSold);

            $averageOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0;

            // ==========================================
            // 3. PROCUREMENT DATA
            // ==========================================
            
            $procurementQuery = DB::table('procurement_requests')
                ->whereBetween('created_at', [$startDate, $endDate]);
                
            if ($distributorId) {
                $procurementQuery->where('distributor_id', $distributorId);
            }

            $procurementStats = (clone $procurementQuery)
                ->select(DB::raw('COUNT(id) as total_requests'), DB::raw('SUM(total_cost) as total_spent'))
                ->first();

            $totalProcurementRequests = (int) ($procurementStats->total_requests ?? 0);
            $totalProcurementCost = $procurementStats->total_spent ?? 0;

            // ==========================================
            // 4. SALES DATA FOR CHARTS (MERGED & FIXED)
            // ==========================================
            
            $mergedSalesData = [];
            
            foreach ($clientDistinctOrders as $order) {
                $dateStr = $order->date;
                if (!isset($mergedSalesData[$dateStr])) {
                    $mergedSalesData[$dateStr] = 0;
                }
                $mergedSalesData[$dateStr] += (float)$order->grand_total;
            }

            foreach ($spDistinctOrders as $order) {
                $dateStr = $order->date;
                if (!isset($mergedSalesData[$dateStr])) {
                    $mergedSalesData[$dateStr] = 0;
                }
                $mergedSalesData[$dateStr] += (float)$order->grand_total;
            }

            $salesData = collect($mergedSalesData)->map(function($value, $date) {
                return ['label' => Carbon::parse($date)->format('M d'), 'value' => $value, 'raw_date' => $date];
            })->sortBy('raw_date')->values()->map(function($item) { unset($item['raw_date']); return $item; });

            // ==========================================
            // 5. TOP PRODUCTS (MERGED)
            // ==========================================

            $clientProducts = (clone $clientOrdersQuery)
                ->join('distributor_products', 'distributor_products.id', '=', 'client_order_items.product_id')
                ->select('distributor_products.id', 'distributor_products.name', DB::raw('SUM(client_order_items.price * client_order_items.quantity) as revenue'), DB::raw('SUM(client_order_items.quantity) as orders'))
                ->groupBy('distributor_products.id', 'distributor_products.name')->get();

            $spProducts = (clone $spOrdersQuery)
                ->join('distributor_products', 'distributor_products.id', '=', 'sp_order_items.product_id')
                ->select('distributor_products.id', 'distributor_products.name', DB::raw('SUM(sp_order_items.price * sp_order_items.quantity) as revenue'), DB::raw('SUM(sp_order_items.quantity) as orders'))
                ->groupBy('distributor_products.id', 'distributor_products.name')->get();

            $mergedProducts = [];
            foreach ([$clientProducts, $spProducts] as $dataset) {
                foreach ($dataset as $product) {
                    if (!isset($mergedProducts[$product->id])) {
                        $mergedProducts[$product->id] = ['id' => $product->id, 'name' => $product->name, 'revenue' => 0, 'orders' => 0];
                    }
                    $mergedProducts[$product->id]['revenue'] += (float)$product->revenue;
                    $mergedProducts[$product->id]['orders'] += (int)$product->orders;
                }
            }

            $topProducts = collect($mergedProducts)->sortByDesc('revenue')->take(5)->values()->map(function($product) use ($totalSales) {
                $product['marketShare'] = $totalSales > 0 ? round(($product['revenue'] / $totalSales) * 100, 2) : 0;
                return $product;
            });

            // =========================================================
            // 6. DECISION SUPPORT SYSTEM: PROCUREMENT RECOMMENDATIONS
            // =========================================================
            
            $allProducts = DB::table('distributor_products')
                ->leftJoin('supplier_raw_materials', 'distributor_products.sku_code', '=', 'supplier_raw_materials.sku_code')
                ->where('distributor_products.distributor_id', $distributorId)
                ->where('distributor_products.is_active', 1)
                ->select(
                    'distributor_products.id', 
                    'distributor_products.name', 
                    'distributor_products.min_stock_level',
                    'distributor_products.max_stock_level',
                    'supplier_raw_materials.min_order as supplier_min_order'
                )
                ->get();

            $activeInventories = DB::table('distributor_inventories')
                ->where('distributor_id', $distributorId)
                ->pluck('quantity', 'product_id');

            $inactiveInventories = DB::table('inactive_distributor_inventories')
                ->where('distributor_id', $distributorId)
                ->pluck('quantity', 'product_id');

            $dssRecommendations = [];

            foreach ($allProducts as $product) {
                $id = $product->id;
                
                $activeStock = $activeInventories->get($id) ?? 0;
                $inactiveStock = $inactiveInventories->get($id) ?? 0;
                $currentStock = $activeStock + $inactiveStock;
                
                $minStock = $product->min_stock_level;
                $maxStock = $product->max_stock_level;
                
                if ($currentStock >= $maxStock) {
                    continue; 
                }

                $supplierMinOrder = $product->supplier_min_order ?? 1;
                $sold = isset($mergedProducts[$id]) ? $mergedProducts[$id]['orders'] : 0;
                $predictedNeed = $sold > 0 ? ceil($sold * 1.2) : ceil($minStock * 1.5);
                
                $suggestedQuantity = 0;
                $priority = 'Low';
                $reason = 'Stock is sufficient.';

                if ($currentStock <= $minStock) {
                    $priority = 'High';
                    $suggestedQuantity = max($minStock * 2, $predictedNeed) - $currentStock;
                    $reason = 'Critical: Stock is below minimum safe level.';
                } 
                elseif ($sold > 0 && $currentStock < $sold) {
                    $priority = 'High';
                    $suggestedQuantity = $predictedNeed - $currentStock;
                    $reason = 'Warning: Stock cannot sustain current sales velocity.';
                } 
                elseif ($currentStock < $predictedNeed) {
                    $priority = 'Medium';
                    $suggestedQuantity = $predictedNeed - $currentStock;
                    $reason = 'Monitor: Buffer stock is running low based on trend.';
                }

                if ($priority === 'Low' || $suggestedQuantity <= 0) {
                    continue;
                }

                if ($suggestedQuantity < $supplierMinOrder) {
                    $suggestedQuantity = $supplierMinOrder;
                }
                
                if (($currentStock + $suggestedQuantity) > $maxStock && $suggestedQuantity > $supplierMinOrder) {
                    $suggestedQuantity = max($supplierMinOrder, $maxStock - $currentStock);
                }

                $dssRecommendations[] = [
                    'id' => $id,
                    'name' => $product->name,
                    'sold_in_period' => $sold,
                    'current_stock' => $currentStock,
                    'priority' => $priority,
                    'suggested_quantity' => (int) $suggestedQuantity,
                    'reason' => $reason
                ];
            }

            usort($dssRecommendations, function($a, $b) {
                $prioMap = ['High' => 3, 'Medium' => 2, 'Low' => 1];
                if ($prioMap[$a['priority']] != $prioMap[$b['priority']]) {
                    return $prioMap[$b['priority']] - $prioMap[$a['priority']];
                }
                return $b['sold_in_period'] - $a['sold_in_period'];
            });

            // ==========================================
            // 7. PROCUREMENT LIST
            // ==========================================

            $procurements = $procurementQuery
                ->select('request_code', 'product_name', 'quantity', 'total_cost', 'status', 'created_at')
                ->orderBy('created_at', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'keyMetrics' => [
                    'totalSales' => $totalSales,
                    'totalOrders' => $totalOrders,
                    'averageOrderValue' => $averageOrderValue,
                    'totalItemsSold' => $totalItemsSold,
                    'totalProcurementCost' => $totalProcurementCost,
                    'totalProcurementRequests' => $totalProcurementRequests,
                ],
                'salesData' => $salesData,
                'topProducts' => $topProducts,
                'dssRecommendations' => $dssRecommendations, 
                'procurements' => $procurements,
                'permissions' => $accessData['permissions']
            ]);

        } catch (\Exception $e) {
            Log::error('ECommerce Report Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate report data.', 'error' => $e->getMessage()], 500);
        }
    }
}