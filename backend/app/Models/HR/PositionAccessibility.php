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

        // Only create for HR department positions
        if ($position->department !== 'Human Resources' || empty($accessibilityData)) {
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
        if ($position->department !== 'Human Resources') {
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
        if ($position->department !== 'Human Resources') {
            return false;
        }

        return self::where('position_id', $position->id)
            ->byPermissionKey($permissionKey)
            ->granted()
            ->exists();
    }
}