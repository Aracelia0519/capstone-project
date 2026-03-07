<?php

namespace App\Http\Controllers\Api\SpecialRBAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use App\Models\HR\PositionAccessibility;

class SpecialRBACSidebarController extends Controller
{
    /**
     * Get dynamic sidebar access based on granular RBAC permissions.
     */
    public function getSidebarAccess(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Find the employee record
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }

            // Get their position details
            $position = Position::where('distributor_id', $employee->parent_distributor_id)
                ->where('title', $employee->position)
                ->where('status', 'active')
                ->first();

            $grantedKeys = [];
            
            if ($position) {
                // Fetch granted permissions that have 'can_view' enabled
                $grantedKeys = PositionAccessibility::where('position_id', $position->id)
                    ->where('is_granted', true)
                    ->where('can_view', true)
                    ->pluck('permission_key')
                    ->toArray();
            }

            // If backward compatibility is needed via JSON requirements column
            if (empty($grantedKeys) && $position && isset($position->requirements['accessibility'])) {
                $grantedKeys = $position->requirements['accessibility'];
            }

            // Master Navigation Dictionary (Mapped correctly to /special-rbac/...)
            $masterNav = [
                // ================= HUMAN RESOURCES =================
                [
                    'title' => 'Human Resources',
                    'items' => [
                        ['name' => 'Dashboard', 'path' => '/special-rbac/HRdashboard', 'icon' => 'LayoutDashboard', 'permissionKey' => 'dashboard'],
                        ['name' => 'Employee List', 'path' => '/special-rbac/employeesListHR', 'icon' => 'Users', 'permissionKey' => 'employee_list'],
                        ['name' => 'Positions & Roles', 'path' => '/special-rbac/positionRolesHR', 'icon' => 'Briefcase', 'permissionKey' => 'positions_roles'],
                        ['name' => 'Departments', 'path' => '/special-rbac/departmentsHR', 'icon' => 'Building2', 'permissionKey' => 'departments'],
                        ['name' => 'Employment Status', 'path' => '/special-rbac/employmentStatusHR', 'icon' => 'UserCheck', 'permissionKey' => 'employment_status'],
                        ['name' => 'Recruitment', 'path' => '/special-rbac/recruitmentApplication', 'icon' => 'UserPlus', 'permissionKey' => 'recruitment'],
                        ['name' => 'Payroll', 'path' => '/special-rbac/payrollHR', 'icon' => 'Banknote', 'permissionKey' => 'payroll_management'],
                        ['name' => 'Attendance Request', 'path' => '/special-rbac/attendanceRequestHR', 'icon' => 'Clock', 'permissionKey' => 'attendance_request'],
                        ['name' => 'Leave Request', 'path' => '/special-rbac/leaveRequestHR', 'icon' => 'CalendarX', 'permissionKey' => 'leave_request'],
                        ['name' => 'HR Reports', 'path' => '/special-rbac/reportsHR', 'icon' => 'FileBarChart', 'permissionKey' => 'reports'],
                    ]
                ],
                // ================= FINANCE =================
                [
                    'title' => 'Finance',
                    'items' => [
                        ['name' => 'Dashboard', 'path' => '/special-rbac/financeDashboard', 'icon' => 'PieChart', 'permissionKey' => 'finance_dashboard'],
                        ['name' => 'Transactions', 'path' => '/special-rbac/transactions', 'icon' => 'ArrowLeftRight', 'permissionKey' => 'finance_transactions'],
                        ['name' => 'Payment Methods', 'path' => '/special-rbac/paymentMethods', 'icon' => 'CreditCard', 'permissionKey' => 'finance_payment_methods'],
                        ['name' => 'Invoices / Billing', 'path' => '/special-rbac/invoices', 'icon' => 'FileText', 'permissionKey' => 'finance_invoices'],
                        ['name' => 'Finance Reports', 'path' => '/special-rbac/reportFinance', 'icon' => 'BarChart3', 'permissionKey' => 'finance_reports'],
                        ['name' => 'Procurement', 'path' => '/special-rbac/procurementRequestFinance', 'icon' => 'ShoppingCart', 'permissionKey' => 'finance_procurement'],
                        ['name' => 'Budget Release', 'path' => '/special-rbac/procurementBudgetRelease', 'icon' => 'Wallet', 'permissionKey' => 'finance_budget_release'],
                        ['name' => 'Payroll Request', 'path' => '/special-rbac/PayrollRequestFinance', 'icon' => 'Users', 'permissionKey' => 'finance_payroll_request'],
                        ['name' => 'Payroll Paid', 'path' => '/special-rbac/PayrollPaidFinance', 'icon' => 'CheckCircle', 'permissionKey' => 'finance_payroll_paid'],
                    ]
                ],
                // ================= OPERATIONAL DISTRIBUTOR =================
                [
                    'title' => 'Operations (OD)',
                    'items' => [
                        ['name' => 'Dashboard', 'path' => '/special-rbac/ECDashboard', 'icon' => 'Activity', 'permissionKey' => 'ec_dashboard'],
                        ['name' => 'OD Reports', 'path' => '/special-rbac/ECReports', 'icon' => 'FileBarChart', 'permissionKey' => 'ec_reports'],
                        
                        // Procurement & Inventory
                        ['name' => 'Procurement', 'path' => '/special-rbac/ECProcurement', 'icon' => 'PackagePlus', 'permissionKey' => 'ec_procurement'],
                        ['name' => 'Process Request', 'path' => '/special-rbac/ECProcessProcurement', 'icon' => 'FileText', 'permissionKey' => 'ec_process_procurement'],
                        ['name' => 'Track Procurement', 'path' => '/special-rbac/ECPTrackProcurement', 'icon' => 'Truck', 'permissionKey' => 'ec_track_procurement'],
                        ['name' => 'Arrived Items', 'path' => '/special-rbac/ECArrivedItem', 'icon' => 'CheckCircle', 'permissionKey' => 'ec_arrived_item'],
                        ['name' => 'Inventory', 'path' => '/special-rbac/ECInventory', 'icon' => 'Boxes', 'permissionKey' => 'ec_inventory'],
                        ['name' => 'Categories', 'path' => '/special-rbac/ECCategories', 'icon' => 'FileText', 'permissionKey' => 'ec_categories'],
                        
                        // Orders & Fulfillment
                        ['name' => 'Orders', 'path' => '/special-rbac/ECOrders', 'icon' => 'ShoppingBag', 'permissionKey' => 'ec_orders'],
                        ['name' => 'Prepare Order', 'path' => '/special-rbac/ECPrepareOrder', 'icon' => 'PackagePlus', 'permissionKey' => 'ec_prepare_order'],
                        ['name' => 'Delivery', 'path' => '/special-rbac/ECDelivery', 'icon' => 'Truck', 'permissionKey' => 'ec_delivery'],
                        ['name' => 'Returns', 'path' => '/special-rbac/ECReturns', 'icon' => 'ArrowLeftRight', 'permissionKey' => 'ec_returns'],
                        
                        // Network & Marketing
                        ['name' => 'Partner Supplier', 'path' => '/special-rbac/ECPartnerSupplier', 'icon' => 'Building2', 'permissionKey' => 'ec_partner_supplier'],
                        ['name' => 'Service Providers', 'path' => '/special-rbac/ECServiceProvider', 'icon' => 'Users', 'permissionKey' => 'ec_service_provider'],
                        ['name' => 'Messages', 'path' => '/special-rbac/ECMessages', 'icon' => 'FileText', 'permissionKey' => 'ec_messages'],
                        ['name' => 'Reviews', 'path' => '/special-rbac/ECReviews', 'icon' => 'CheckCircle', 'permissionKey' => 'ec_reviews'],
                        ['name' => 'Promotions', 'path' => '/special-rbac/ECPromotions', 'icon' => 'Activity', 'permissionKey' => 'ec_promotions'],
                        ['name' => 'Promo Approval', 'path' => '/special-rbac/ECPromoApproval', 'icon' => 'CheckCircle', 'permissionKey' => 'ec_promo_approval'],
                        
                        // Financial
                        ['name' => 'Payments', 'path' => '/special-rbac/ECPayment', 'icon' => 'CreditCard', 'permissionKey' => 'ec_payment'],
                    ]
                ]
            ];

            $dynamicNav = [];

            // Filter the master list based on granted permissions
            foreach ($masterNav as $group) {
                $filteredItems = array_filter($group['items'], function ($item) use ($grantedKeys) {
                    return in_array($item['permissionKey'], $grantedKeys);
                });

                if (!empty($filteredItems)) {
                    $dynamicNav[] = [
                        'title' => $group['title'],
                        'items' => array_values($filteredItems) // Re-index array
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'navigation' => $dynamicNav,
                    'user_details' => [
                        'name' => $user->full_name,
                        'position' => $employee->position,
                        'department' => $employee->department
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sidebar access.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}