<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinanceDashboardController extends Controller
{
    /**
     * Fetch RBAC permissions for the logged-in user (Level-Based).
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        // Note: Using 'finance_dashboard' permission key based on position_accessibilities seed
        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_dashboard')
            ->first();

        if (!$access) return $noAccess;

        return [
            'can_view' => (bool) $access->can_view,
            'can_manage' => (bool) $access->can_manage,
            'can_approve' => (bool) $access->can_approve,
        ];
    }

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') {
            return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        }
        if ($user->role === 'hr_manager') {
            return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        }
        if ($user->role === 'finance_manager') {
            return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        }
        if ($user->role === 'employee') {
            return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        }
        return $user->id;
    }

    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user);
            
            if (!$permissions['can_view']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to finance dashboard.'], 403);
            }

            $distributorId = $this->getDistributorId($user);

            // Timeframes
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // ==========================================
            // 1. TOTAL GROSS SALES (Current Month vs Last Month)
            // ==========================================
            $validOrderStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

            $currentClientSales = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('client_orders.id', 'client_orders.grand_total')
                ->distinct()
                ->get()->sum('grand_total');

            $currentSpSales = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('sp_orders.id', 'sp_orders.grand_total')
                ->distinct()
                ->get()->sum('grand_total');

            $currentTotalSales = $currentClientSales + $currentSpSales;

            $lastClientSales = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$lastMonthStart, $lastMonthEnd])
                ->select('client_orders.id', 'client_orders.grand_total')
                ->distinct()
                ->get()->sum('grand_total');

            $lastSpSales = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$lastMonthStart, $lastMonthEnd])
                ->select('sp_orders.id', 'sp_orders.grand_total')
                ->distinct()
                ->get()->sum('grand_total');

            $lastTotalSales = $lastClientSales + $lastSpSales;

            $salesChangePercent = 0;
            if ($lastTotalSales > 0) {
                $salesChangePercent = (($currentTotalSales - $lastTotalSales) / $lastTotalSales) * 100;
            } else if ($currentTotalSales > 0) {
                $salesChangePercent = 100; // 100% increase if last month was 0
            }

            // ==========================================
            // 2. PENDING BUDGET RELEASES (Procurement)
            // ==========================================
            $pendingProcurementStatuses = ['pending', 'approved', 'op-approved', 'd-approved'];
            $pendingProcurements = DB::table('procurement_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', $pendingProcurementStatuses)
                ->get();

            $pendingBudgetCount = $pendingProcurements->count();
            $pendingBudgetAmount = $pendingProcurements->sum('total_cost');

            // ==========================================
            // 3. TOTAL EXPENSES (Current Month)
            // ==========================================
            // Expenses = VAT + Procurements + Payroll + Refunds
            $currentClientVat = DB::table('order_vat_deductions')
                ->join('client_orders', 'order_vat_deductions.order_id', '=', 'client_orders.id')
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('order_vat_deductions.vat_amount');
            
            $currentSpVat = DB::table('order_vat_deductions')
                ->join('sp_orders', 'order_vat_deductions.sp_order_id', '=', 'sp_orders.id')
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('order_vat_deductions.vat_amount');

            $currentVat = $currentClientVat + $currentSpVat;

            $releasedProcurementStatuses = ['ready', 'processing', 'prepared', 'shipped', 'in_transit', 'delivered', 'completed'];
            $currentProcurement = DB::table('procurement_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', $releasedProcurementStatuses)
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('total_cost');

            $employeeIds = DB::table('hr_employees')
                ->where('parent_distributor_id', $distributorId)
                ->pluck('user_id')
                ->toArray();
            $currentPayroll = DB::table('payrolls')
                ->whereIn('user_id', $employeeIds)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('net_pay');

            $currentRefunds = DB::table('ec_refund_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', ['approved', 'completed'])
                ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('amount');

            $totalExpenses = $currentVat + $currentProcurement + $currentPayroll + $currentRefunds;

            // ==========================================
            // 4. MONTHLY REVENUE CHART (Last 6 Months)
            // ==========================================
            $monthlyRevenue = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
                
                $cSales = DB::table('client_orders')
                    ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                    ->where('client_order_items.distributor_id', $distributorId)
                    ->whereIn('client_orders.status', $validOrderStatuses)
                    ->whereBetween('client_orders.created_at', [$mStart, $mEnd])
                    ->select('client_orders.id', 'client_orders.grand_total')
                    ->distinct()
                    ->get()->sum('grand_total');

                $sSales = DB::table('sp_orders')
                    ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                    ->where('sp_order_items.distributor_id', $distributorId)
                    ->whereIn('sp_orders.status', $validOrderStatuses)
                    ->whereBetween('sp_orders.created_at', [$mStart, $mEnd])
                    ->select('sp_orders.id', 'sp_orders.grand_total')
                    ->distinct()
                    ->get()->sum('grand_total');

                $monthlyRevenue[] = [
                    'label' => $mStart->format('M'),
                    'value' => $cSales + $sSales
                ];
            }

            // Find max for scaling the chart
            $maxRevenue = max(array_column($monthlyRevenue, 'value'));
            if ($maxRevenue == 0) $maxRevenue = 1; // Prevent division by zero
            foreach ($monthlyRevenue as &$month) {
                $month['percentage'] = ($month['value'] / $maxRevenue) * 100;
            }

            // ==========================================
            // 5. ORDER STATUS DISTRIBUTION CHART
            // ==========================================
            // Calculate status for ALL active orders in current month
            $clientOrdersStatus = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereBetween('client_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('client_orders.id', 'client_orders.status')
                ->distinct()
                ->get();
                
            $spOrdersStatus = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereBetween('sp_orders.created_at', [$currentMonthStart, $currentMonthEnd])
                ->select('sp_orders.id', 'sp_orders.status')
                ->distinct()
                ->get();

            $allOrdersStatus = $clientOrdersStatus->concat($spOrdersStatus);
            
            $statusCounts = [
                'completed' => 0,
                'pending' => 0,
                'cancelled' => 0
            ];

            foreach ($allOrdersStatus as $order) {
                if (in_array($order->status, ['delivered', 'completed'])) {
                    $statusCounts['completed']++;
                } elseif (in_array($order->status, ['cancelled', 'rejected'])) {
                    $statusCounts['cancelled']++;
                } else {
                    $statusCounts['pending']++;
                }
            }

            $totalOrdersCount = array_sum($statusCounts);
            $orderDistribution = [
                'completed' => $totalOrdersCount > 0 ? round(($statusCounts['completed'] / $totalOrdersCount) * 100) : 0,
                'pending' => $totalOrdersCount > 0 ? round(($statusCounts['pending'] / $totalOrdersCount) * 100) : 0,
                'cancelled' => $totalOrdersCount > 0 ? round(($statusCounts['cancelled'] / $totalOrdersCount) * 100) : 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'totalSales' => $currentTotalSales,
                    'salesChangePercent' => round($salesChangePercent, 1),
                    'pendingBudgetCount' => $pendingBudgetCount,
                    'pendingBudgetAmount' => $pendingBudgetAmount,
                    'totalExpenses' => $totalExpenses,
                    'monthlyRevenue' => $monthlyRevenue,
                    'orderDistribution' => $orderDistribution
                ],
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            Log::error('Finance Dashboard Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate financial dashboard.', 'error' => $e->getMessage()], 500);
        }
    }
}