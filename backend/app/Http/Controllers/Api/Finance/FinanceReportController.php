<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinanceReportController extends Controller
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

        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'finance_reports')
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
                return response()->json(['success' => false, 'message' => 'Unauthorized access to finance reports.'], 403);
            }

            $distributorId = $this->getDistributorId($user);

            // Handle Date Ranges
            $period = $request->input('period', 'monthly');
            $startDate = Carbon::now();
            $endDate = Carbon::now();

            switch ($period) {
                case 'daily':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'weekly':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'quarterly':
                    $startDate = Carbon::now()->startOfQuarter();
                    $endDate = Carbon::now()->endOfQuarter();
                    break;
                case 'yearly':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                case 'custom':
                    $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
                    $endDate = Carbon::parse($request->input('endDate'))->endOfDay();
                    break;
            }

            // ==========================================
            // 1. OVERALL LIFETIME SALES
            // ==========================================
            $overallSales = DB::table('distributor_overall_sales')
                ->where('distributor_id', $distributorId)
                ->value('total_revenue') ?? 0;

            // ==========================================
            // 2. PROCUREMENT BUDGET RELEASED (Based on Procurement Requests)
            // ==========================================
            // Excluding 'pending', 'approved', 'op-approved', and 'd-approved' as requested
            $validProcurementStatuses = ['ready', 'processing', 'prepared', 'shipped', 'in_transit', 'delivered', 'completed'];

            $procurementRequestsQuery = DB::table('procurement_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', $validProcurementStatuses)
                ->whereBetween('created_at', [$startDate, $endDate]);

            $procurementBudgetReleased = (clone $procurementRequestsQuery)->sum('total_cost');

            $procurementTransactions = $procurementRequestsQuery
                ->select(
                    'request_code as reference', 
                    'product_name as description', 
                    'total_cost as amount', 
                    'status',
                    'created_at as date'
                )
                ->orderByDesc('created_at')
                ->get();

            // ==========================================
            // 2.5 BUDGET DEDUCTION LOGS 
            // ==========================================
            $budgetDeductionLogs = DB::table('budget_deduction_logs')
                ->where('distributor_id', $distributorId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    'id as reference',
                    'description',
                    'amount',
                    'created_at as date'
                )
                ->orderByDesc('created_at')
                ->get();

            // ==========================================
            // 3. REFUNDS PROCESSED
            // ==========================================
            $refundsQuery = DB::table('ec_refund_requests')
                ->where('distributor_id', $distributorId)
                ->whereIn('status', ['approved', 'completed'])
                ->whereBetween('updated_at', [$startDate, $endDate]);

            $refundsProcessed = (clone $refundsQuery)->sum('amount');

            $refundTransactionsRaw = $refundsQuery
                ->select('id', 'return_request_id', 'sp_return_request_id', 'amount', 'updated_at as date')
                ->orderByDesc('updated_at')
                ->get();

            $refundTransactions = [];
            foreach($refundTransactionsRaw as $refund) {
                $description = 'Refund Processed';
                if ($refund->return_request_id) {
                    $description = 'Refund for Client Return Request #' . $refund->return_request_id;
                } elseif ($refund->sp_return_request_id) {
                    $description = 'Refund for SP Return Request #' . $refund->sp_return_request_id;
                }

                $refundTransactions[] = [
                    'reference' => $refund->id, 
                    'description' => $description,
                    'amount' => $refund->amount,
                    'date' => $refund->date
                ];
            }

            // ==========================================
            // 4. SALES & VAT TRANSACTIONS
            // ==========================================
            $validOrderStatuses = ['confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'completed'];

            $clientOrdersQuery = DB::table('client_orders')
                ->join('client_order_items', 'client_orders.id', '=', 'client_order_items.order_id')
                ->leftJoin('order_vat_deductions', 'client_orders.id', '=', 'order_vat_deductions.order_id')
                ->where('client_order_items.distributor_id', $distributorId)
                ->whereIn('client_orders.status', $validOrderStatuses)
                ->whereBetween('client_orders.created_at', [$startDate, $endDate])
                ->select(
                    'client_orders.id as order_id', 
                    'client_orders.grand_total', 
                    'order_vat_deductions.vat_amount',
                    'client_orders.created_at as date'
                )
                ->distinct();

            $spOrdersQuery = DB::table('sp_orders')
                ->join('sp_order_items', 'sp_orders.id', '=', 'sp_order_items.sp_order_id')
                ->leftJoin('order_vat_deductions', 'sp_orders.id', '=', 'order_vat_deductions.sp_order_id')
                ->where('sp_order_items.distributor_id', $distributorId)
                ->whereIn('sp_orders.status', $validOrderStatuses)
                ->whereBetween('sp_orders.created_at', [$startDate, $endDate])
                ->select(
                    'sp_orders.id as order_id', 
                    'sp_orders.grand_total', 
                    'order_vat_deductions.vat_amount',
                    'sp_orders.created_at as date'
                )
                ->distinct();

            $clientSalesRaw = $clientOrdersQuery->get();
            $spSalesRaw = $spOrdersQuery->get();

            $totalSales = $clientSalesRaw->sum('grand_total') + $spSalesRaw->sum('grand_total');
            $totalVat = $clientSalesRaw->sum('vat_amount') + $spSalesRaw->sum('vat_amount');

            // Format Sales Transactions for the table
            $salesTransactions = [];
            foreach ($clientSalesRaw as $order) {
                $salesTransactions[] = [
                    'reference' => 'Client Order #' . $order->order_id,
                    'type' => 'Client Order',
                    'amount' => $order->grand_total,
                    'vat' => $order->vat_amount ?? 0,
                    'date' => $order->date
                ];
            }
            foreach ($spSalesRaw as $order) {
                $salesTransactions[] = [
                    'reference' => 'SP Order #' . $order->order_id,
                    'type' => 'Service Provider Order',
                    'amount' => $order->grand_total,
                    'vat' => $order->vat_amount ?? 0,
                    'date' => $order->date
                ];
            }

            // Sort sales by date descending
            usort($salesTransactions, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // ==========================================
            // 5. PAYROLL TRANSACTIONS
            // ==========================================
            $employeeIds = DB::table('hr_employees')
                ->where('parent_distributor_id', $distributorId)
                ->pluck('user_id')
                ->toArray();

            $payrollsQuery = DB::table('payrolls')
                ->join('users', 'payrolls.user_id', '=', 'users.id')
                ->whereIn('payrolls.user_id', $employeeIds)
                ->where('payrolls.status', 'paid')
                ->whereBetween('payrolls.paid_at', [$startDate, $endDate]);

            $totalPayrollDisbursed = (clone $payrollsQuery)->sum('payrolls.net_pay');

            $payrollTransactions = $payrollsQuery
                ->select(
                    'payrolls.payment_reference as reference', 
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as description"), 
                    'payrolls.net_pay as amount', 
                    'payrolls.paid_at as date'
                )
                ->orderByDesc('payrolls.paid_at')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    // Aggregates
                    'overallSalesLifetime' => $overallSales,
                    'totalSales' => $totalSales,
                    'totalVat' => $totalVat,
                    'procurementBudgetReleased' => $procurementBudgetReleased,
                    'refundsProcessed' => $refundsProcessed,
                    'totalPayrollDisbursed' => $totalPayrollDisbursed,
                    
                    // Transaction Lists for Data Tables
                    'salesTransactions' => $salesTransactions,
                    'procurementTransactions' => $procurementTransactions,
                    'budgetDeductionLogs' => $budgetDeductionLogs,
                    'refundTransactions' => $refundTransactions,
                    'payrollTransactions' => $payrollTransactions
                ],
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            Log::error('Finance Report Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate financial report.', 'error' => $e->getMessage()], 500);
        }
    }
}