<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Position extends Model
{
    use SoftDeletes;

    // Supported Departments for Role-Based Access Control
    public const RBAC_DEPARTMENTS = [
        'Human Resources', 
        'Finance', 
        'Operational Distributor', 
        'Special RBAC'
    ];

    protected $table = 'positions';

    protected $fillable = [
        'distributor_id',
        'title',
        'department',
        'description',
        'status',
        'requirements',
        'min_salary',
        'max_salary',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'requirements' => 'array',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2'
    ];

    protected $appends = [
        'department_color',
        'company_name',
        'distributor_name',
        'accessibility'
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function distributorRequirements()
    {
        return $this->hasOneThrough(
            \App\Models\Distributor\DistributorRequirements::class,
            User::class,
            'id', 
            'user_id', 
            'distributor_id', 
            'id' 
        );
    }

    public function accessibilitySettings(): HasMany
    {
        return $this->hasMany(PositionAccessibility::class, 'position_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDepartmentColorAttribute(): array
    {
        $colors = [
            'Administration' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-800'],
            'Human Resources' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'badge' => 'bg-green-100 text-green-800'],
            'Finance' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-800'],
            'Operational Distributor' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'badge' => 'bg-red-100 text-red-800'],
            'Special RBAC' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-800'],
            'Logistics' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-800'],
            'IT' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-600', 'badge' => 'bg-teal-100 text-teal-800'],
            'Sales' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600', 'badge' => 'bg-pink-100 text-pink-800'],
            'Marketing' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'badge' => 'bg-orange-100 text-orange-800'],
        ];

        return $colors[$this->department] ?? 
               ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'badge' => 'bg-gray-100 text-gray-800'];
    }

    public function getCompanyNameAttribute(): string
    {
        if ($this->distributorRequirements && $this->distributorRequirements->company_name) {
            return $this->distributorRequirements->company_name;
        }
        return $this->distributor ? $this->distributor->full_name . ' (Individual)' : 'No Company';
    }

    public function getDistributorNameAttribute(): string
    {
        return $this->distributor ? $this->distributor->full_name : 'Unknown';
    }

    public function getAccessibilityAttribute(): array
    {
        if (!in_array($this->department, self::RBAC_DEPARTMENTS)) {
            return [];
        }

        // Return the detailed objects formatted per module containing CRUD values
        $settings = $this->accessibilitySettings()->granted()->get();
        
        if ($settings->isNotEmpty()) {
            return $settings->mapWithKeys(function ($item) {
                return [$item->permission_key => [
                    'view' => $item->can_view,
                    'create' => $item->can_create,
                    'update' => $item->can_update,
                    'delete' => $item->can_delete,
                ]];
            })->toArray();
        }

        // Backward fallback
        if ($this->requirements && isset($this->requirements['accessibility'])) {
            return $this->requirements['accessibility'];
        }

        return [];
    }

    public function hasPermission(string $permissionKey, string $action = null): bool
    {
        if (!in_array($this->department, self::RBAC_DEPARTMENTS)) {
            return false;
        }

        $query = $this->accessibilitySettings()
            ->where('permission_key', $permissionKey)
            ->granted();

        if ($action && in_array($action, ['view', 'create', 'update', 'delete'])) {
            $query->where("can_{$action}", true);
        }

        if ($query->exists()) {
            return true;
        }

        if ($this->requirements && isset($this->requirements['accessibility'])) {
            $access = $this->requirements['accessibility'];
            if (is_array($access) && in_array($permissionKey, $access)) {
                return true;
            }
        }

        return false;
    }

    public function updateAccessibility(array $accessibilityData): void
    {
        PositionAccessibility::updateForPosition($this, $accessibilityData);
        
        $requirements = $this->requirements ?? [];
        $keys = [];
        foreach($accessibilityData as $k => $v) {
            $keys[] = is_numeric($k) ? $v : $k;
        }

        $requirements['accessibility'] = $keys; // save keys for backwards compat
        $this->requirements = $requirements;
        $this->save();
    }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeByDistributor($query, $distributorId) { return $query->where('distributor_id', $distributorId); }
    public function scopeByDepartment($query, $department) { return $query->where('department', $department); }
    public function scopeSearch($query, $search) {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%");
        });
    }

    protected static function booted()
    {
        static::deleting(function ($position) {
            $position->accessibilitySettings()->delete();
        });
    }
}