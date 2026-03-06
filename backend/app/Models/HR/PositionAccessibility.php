<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionAccessibility extends Model
{
    protected $table = 'position_accessibilities';

    protected $fillable = [
        'position_id',
        'permission_key',
        'permission_label',
        'is_granted',
        'can_view',
        'can_create',
        'can_update',
        'can_delete'
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean'
    ];

    /**
     * Get the position that owns the accessibility.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Scope a query to only include granted permissions.
     */
    public function scopeGranted($query)
    {
        return $query->where('is_granted', true);
    }

    /**
     * Scope a query to filter by permission key.
     */
    public function scopeByPermissionKey($query, $key)
    {
        return $query->where('permission_key', $key);
    }

    /**
     * Get all available permission options mapped for all roles.
     */
    public static function getPermissionOptions(): array
    {
        return [
            // ================= Human Resources =================
            ['key' => 'dashboard', 'label' => 'Dashboard', 'description' => 'Access to HR dashboard'],
            ['key' => 'employee_list', 'label' => 'Employee List', 'description' => 'Access to employee list and management'],
            ['key' => 'positions_roles', 'label' => 'Positions & Roles', 'description' => 'Access to position and role management'],
            ['key' => 'departments', 'label' => 'Departments', 'description' => 'Access to department management'],
            ['key' => 'employment_status', 'label' => 'Employment Status', 'description' => 'Access to employee status tracking'],
            ['key' => 'recruitment', 'label' => 'Recruitment Application', 'description' => 'Access to recruitment and applicant tracking'],
            ['key' => 'payroll_management', 'label' => 'Payroll Management', 'description' => 'Access to payroll and compensation management'],
            ['key' => 'attendance_request', 'label' => 'Attendance Request', 'description' => 'Access to employee attendance requests'],
            ['key' => 'leave_request', 'label' => 'Leave Request', 'description' => 'Access to employee leave requests'],
            ['key' => 'reports', 'label' => 'HR Reports', 'description' => 'Access to HR reporting and analytics'],
            
            // ================= Finance =================
            ['key' => 'finance_dashboard', 'label' => 'Finance Dashboard', 'description' => 'Access to Finance dashboard'],
            ['key' => 'finance_transactions', 'label' => 'Transactions', 'description' => 'Access to financial transactions'],
            ['key' => 'finance_payment_methods', 'label' => 'Payment Methods', 'description' => 'Manage payment methods'],
            ['key' => 'finance_invoices', 'label' => 'Invoices / Billing', 'description' => 'Access to billing and invoicing'],
            ['key' => 'finance_reports', 'label' => 'Finance Reports', 'description' => 'Access to financial reporting'],
            ['key' => 'finance_procurement', 'label' => 'Procurement', 'description' => 'Access to procurement requests'],
            ['key' => 'finance_budget_release', 'label' => 'Budget Release', 'description' => 'Access to budget releasing'],
            ['key' => 'finance_payroll_request', 'label' => 'Payroll Request', 'description' => 'Access to pending payroll requests'],
            ['key' => 'finance_payroll_paid', 'label' => 'Payroll Paid', 'description' => 'Access to completed/paid payrolls'],
            
            // ================= Operational Distributor =================
            ['key' => 'ec_dashboard', 'label' => 'OD Dashboard', 'description' => 'Access to Operational Distributor Dashboard'],
            ['key' => 'ec_procurement', 'label' => 'Procurement', 'description' => 'Access to Catalog & Inventory Procurement'],
            ['key' => 'ec_categories', 'label' => 'Categories', 'description' => 'Access to Catalog & Inventory Categories'],
            ['key' => 'ec_process_procurement', 'label' => 'Process Request', 'description' => 'Access to Process Request'],
            ['key' => 'ec_track_procurement', 'label' => 'Track Procurement', 'description' => 'Access to Track Procurement'],
            ['key' => 'ec_arrived_item', 'label' => 'Arrived Item', 'description' => 'Access to Arrived Item'],
            ['key' => 'ec_inventory', 'label' => 'Inventory', 'description' => 'Access to Inventory'],
            ['key' => 'ec_orders', 'label' => 'Orders', 'description' => 'Access to Orders'],
            ['key' => 'ec_prepare_order', 'label' => 'Prepare Order', 'description' => 'Access to Prepare Order'],
            ['key' => 'ec_payment', 'label' => 'Payments', 'description' => 'Access to Payments'],
            ['key' => 'ec_delivery', 'label' => 'Delivery', 'description' => 'Access to Delivery'],
            ['key' => 'ec_returns', 'label' => 'Returns', 'description' => 'Access to Returns'],
            ['key' => 'ec_promo_approval', 'label' => 'Promo Approval', 'description' => 'Access to Promo Approval'],
            ['key' => 'ec_partner_supplier', 'label' => 'Partner Supplier', 'description' => 'Access to Partner Supplier'],
            ['key' => 'ec_service_provider', 'label' => 'Service Provider', 'description' => 'Access to Service Provider'],
            ['key' => 'ec_reviews', 'label' => 'Reviews', 'description' => 'Access to Reviews'],
            ['key' => 'ec_promotions', 'label' => 'Promotions', 'description' => 'Access to Promotions'],
            ['key' => 'ec_reports', 'label' => 'Reports', 'description' => 'Access to Reports'],
            ['key' => 'ec_messages', 'label' => 'Messages', 'description' => 'Access to Messages']
        ];
    }

    /**
     * Update accessibility for a position (supports CRUD granular control).
     */
    public static function updateForPosition(Position $position, array $accessibilityData): void
    {
        // Delete existing accessibility for this position to replace fresh
        self::where('position_id', $position->id)->delete();

        if (!in_array($position->department, Position::RBAC_DEPARTMENTS) || empty($accessibilityData)) {
            return;
        }

        $permissionOptions = self::getPermissionOptions();
        $permissionMap = collect($permissionOptions)->keyBy('key')->map->label->toArray();
        
        foreach ($accessibilityData as $key => $value) {
            // Backward compatibility for standard flat arrays ['dashboard', 'employee_list']
            if (is_numeric($key) && is_string($value)) {
                $permissionKey = $value;
                $crud = ['view' => true, 'create' => true, 'update' => true, 'delete' => true]; // Fallback to all access
            } 
            // New object-based format: 'dashboard' => ['view' => true, 'create' => false, ...]
            else {
                $permissionKey = $key;
                $crud = $value;
            }

            if (isset($permissionMap[$permissionKey])) {
                self::create([
                    'position_id' => $position->id,
                    'permission_key' => $permissionKey,
                    'permission_label' => $permissionMap[$permissionKey],
                    'is_granted' => true,
                    'can_view' => $crud['can_view'] ?? $crud['view'] ?? false,
                    'can_create' => $crud['can_create'] ?? $crud['create'] ?? false,
                    'can_update' => $crud['can_update'] ?? $crud['update'] ?? false,
                    'can_delete' => $crud['can_delete'] ?? $crud['delete'] ?? false,
                ]);
            }
        }
    }

    /**
     * Get accessibility for a position.
     */
    public static function getForPosition(Position $position): array
    {
        if (!in_array($position->department, Position::RBAC_DEPARTMENTS)) {
            return [];
        }

        return self::where('position_id', $position->id)
            ->granted()
            ->pluck('permission_key')
            ->toArray();
    }

    /**
     * Check if a position has specific permission.
     */
    public static function hasPermission(Position $position, string $permissionKey): bool
    {
        if (!in_array($position->department, Position::RBAC_DEPARTMENTS)) {
            return false;
        }

        return self::where('position_id', $position->id)
            ->byPermissionKey($permissionKey)
            ->granted()
            ->exists();
    }
}