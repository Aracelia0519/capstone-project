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
        'is_granted'
    ];

    protected $casts = [
        'is_granted' => 'boolean'
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
     * Get all available permission options.
     */
    public static function getPermissionOptions(): array
    {
        return [
            // Human Resources Options
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'description' => 'Access to HR dashboard'
            ],
            [
                'key' => 'employee_list',
                'label' => 'Employee List',
                'description' => 'Access to employee list and management'
            ],
            [
                'key' => 'positions_roles',
                'label' => 'Position & Roles',
                'description' => 'Access to position and role management'
            ],
            [
                'key' => 'departments',
                'label' => 'Departments',
                'description' => 'Access to department management'
            ],
            [
                'key' => 'employee_status',
                'label' => 'Employee Status',
                'description' => 'Access to employee status tracking'
            ],
            [
                'key' => 'recruitment_application',
                'label' => 'Recruitment Application',
                'description' => 'Access to recruitment and applicant tracking'
            ],
            [
                'key' => 'payroll_management',
                'label' => 'Payroll Management',
                'description' => 'Access to payroll and compensation management'
            ],
            
            // Operational Distributor Options
            [
                'key' => 'ec_dashboard',
                'label' => 'Dashboard',
                'description' => 'Access to Operational Distributor Dashboard'
            ],
            [
                'key' => 'ec_procurement',
                'label' => 'Procurement',
                'description' => 'Access to Catalog & Inventory Procurement'
            ],
            [
                'key' => 'ec_categories',
                'label' => 'Categories',
                'description' => 'Access to Catalog & Inventory Categories'
            ],
            [
                'key' => 'ec_process_procurement',
                'label' => 'Process Request',
                'description' => 'Access to Process Request'
            ],
            [
                'key' => 'ec_track_procurement',
                'label' => 'Track Procurement',
                'description' => 'Access to Track Procurement'
            ],
            [
                'key' => 'ec_arrived_item',
                'label' => 'Arrived Item',
                'description' => 'Access to Arrived Item'
            ],
            [
                'key' => 'ec_inventory',
                'label' => 'Inventory',
                'description' => 'Access to Inventory'
            ],
            [
                'key' => 'ec_orders',
                'label' => 'Orders',
                'description' => 'Access to Orders'
            ],
            [
                'key' => 'ec_prepare_order',
                'label' => 'Prepare Order',
                'description' => 'Access to Prepare Order'
            ],
            [
                'key' => 'ec_payment',
                'label' => 'Payments',
                'description' => 'Access to Payments'
            ],
            [
                'key' => 'ec_delivery',
                'label' => 'Delivery',
                'description' => 'Access to Delivery'
            ],
            [
                'key' => 'ec_returns',
                'label' => 'Returns',
                'description' => 'Access to Returns'
            ],
            [
                'key' => 'ec_promo_approval',
                'label' => 'Promo Approval',
                'description' => 'Access to Promo Approval'
            ],
            [
                'key' => 'ec_partner_supplier',
                'label' => 'Partner Supplier',
                'description' => 'Access to Partner Supplier'
            ],
            [
                'key' => 'ec_service_provider',
                'label' => 'Service Provider',
                'description' => 'Access to Service Provider'
            ],
            [
                'key' => 'ec_reviews',
                'label' => 'Reviews',
                'description' => 'Access to Reviews'
            ],
            [
                'key' => 'ec_promotions',
                'label' => 'Promotions',
                'description' => 'Access to Promotions'
            ],
            [
                'key' => 'ec_reports',
                'label' => 'Reports',
                'description' => 'Access to Reports'
            ],
            [
                'key' => 'ec_messages',
                'label' => 'Messages',
                'description' => 'Access to Messages'
            ]
        ];
    }

    /**
     * Update accessibility for a position (only store selected items).
     */
    public static function updateForPosition(Position $position, array $accessibilityData): void
    {
        // Delete existing accessibility for this position
        self::where('position_id', $position->id)->delete();

        // Only create for specific departments
        if (!in_array($position->department, ['Human Resources', 'Operational Distributor']) || empty($accessibilityData)) {
            return;
        }

        $permissionOptions = self::getPermissionOptions();
        $permissionMap = [];
        
        foreach ($permissionOptions as $option) {
            $permissionMap[$option['key']] = $option['label'];
        }
        
        // Only store the selected accessibility items
        foreach ($accessibilityData as $permissionKey) {
            if (isset($permissionMap[$permissionKey])) {
                self::create([
                    'position_id' => $position->id,
                    'permission_key' => $permissionKey,
                    'permission_label' => $permissionMap[$permissionKey],
                    'is_granted' => true
                ]);
            }
        }
    }

    /**
     * Get accessibility for a position.
     */
    public static function getForPosition(Position $position): array
    {
        if (!in_array($position->department, ['Human Resources', 'Operational Distributor'])) {
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
        if (!in_array($position->department, ['Human Resources', 'Operational Distributor'])) {
            return false;
        }

        return self::where('position_id', $position->id)
            ->byPermissionKey($permissionKey)
            ->granted()
            ->exists();
    }
}