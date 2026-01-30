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
        'accessibility' // New appended attribute
    ];

    /**
     * Get the distributor (company) that owns this position.
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    /**
     * Get the distributor's requirements for company name.
     */
    public function distributorRequirements()
    {
        return $this->hasOneThrough(
            \App\Models\Distributor\DistributorRequirements::class,
            User::class,
            'id', // Foreign key on users table
            'user_id', // Foreign key on distributor_requirements table
            'distributor_id', // Local key on positions table
            'id' // Local key on users table
        );
    }

    /**
     * Get the accessibility settings for this position.
     */
    public function accessibilitySettings(): HasMany
    {
        return $this->hasMany(PositionAccessibility::class, 'position_id');
    }

    /**
     * Get the creator of the position.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater of the position.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the department color attribute.
     */
    public function getDepartmentColorAttribute(): array
    {
        $colors = [
            'Administration' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'badge' => 'bg-blue-100 text-blue-800'],
            'Human Resources' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'badge' => 'bg-green-100 text-green-800'],
            'Finance' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'badge' => 'bg-purple-100 text-purple-800'],
            'Distributor Assistant' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-800'],
            'Operations' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'badge' => 'bg-red-100 text-red-800'],
            'Logistics' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-800'],
            'IT' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-600', 'badge' => 'bg-teal-100 text-teal-800'],
            'Sales' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600', 'badge' => 'bg-pink-100 text-pink-800'],
            'Marketing' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'badge' => 'bg-orange-100 text-orange-800'],
        ];

        return $colors[$this->department] ?? 
               ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'badge' => 'bg-gray-100 text-gray-800'];
    }

    /**
     * Get the company name attribute.
     */
    public function getCompanyNameAttribute(): string
    {
        if ($this->distributorRequirements && $this->distributorRequirements->company_name) {
            return $this->distributorRequirements->company_name;
        }
        
        return $this->distributor ? $this->distributor->full_name . ' (Individual)' : 'No Company';
    }

    /**
     * Get the distributor name attribute.
     */
    public function getDistributorNameAttribute(): string
    {
        return $this->distributor ? $this->distributor->full_name : 'Unknown';
    }

    /**
     * Get the accessibility attribute.
     */
    public function getAccessibilityAttribute(): array
    {
        if ($this->department !== 'Human Resources') {
            return [];
        }

        // Try to get from requirements first (for backward compatibility)
        if ($this->requirements && isset($this->requirements['accessibility'])) {
            return $this->requirements['accessibility'];
        }

        // Get from position_accessibilities table
        return $this->accessibilitySettings()
            ->granted()
            ->pluck('permission_key')
            ->toArray();
    }

    /**
     * Check if position has specific permission.
     */
    public function hasPermission(string $permissionKey): bool
    {
        if ($this->department !== 'Human Resources') {
            return false;
        }

        // Check in requirements (backward compatibility)
        if ($this->requirements && isset($this->requirements['accessibility'])) {
            return in_array($permissionKey, $this->requirements['accessibility']);
        }

        // Check in position_accessibilities table
        return $this->accessibilitySettings()
            ->where('permission_key', $permissionKey)
            ->granted()
            ->exists();
    }

    /**
     * Update accessibility for this position.
     */
    public function updateAccessibility(array $accessibilityData): void
    {
        PositionAccessibility::updateForPosition($this, $accessibilityData);
        
        // Also update requirements for backward compatibility
        $requirements = $this->requirements ?? [];
        $requirements['accessibility'] = $accessibilityData;
        $this->requirements = $requirements;
        $this->save();
    }

    /**
     * Scope a query to only include active positions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by distributor.
     */
    public function scopeByDistributor($query, $distributorId)
    {
        return $query->where('distributor_id', $distributorId);
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to search by title or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%");
        });
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::deleting(function ($position) {
            // Delete associated accessibility records
            $position->accessibilitySettings()->delete();
        });
    }
}