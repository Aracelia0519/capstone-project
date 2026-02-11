<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class HRManager extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_managers';

    protected $fillable = [
        'parent_distributor_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'employment_type',
        'hire_date',
        'salary',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'resume',
        'employment_contract',
        'status'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent distributor (user who created this HR manager)
     */
    public function parentDistributor()
    {
        return $this->belongsTo(User::class, 'parent_distributor_id');
    }

    /**
     * Get the HR manager's user account
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the full name of the HR manager
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the display name for ID type
     */
    public function getIdTypeNameAttribute()
    {
        $idTypes = [
            'passport' => 'Passport',
            'driver_license' => 'Driver\'s License',
            'umid' => 'UMID (SSS/GSIS)',
            'prc' => 'PRC ID',
            'postal' => 'Postal ID',
            'voter' => 'Voter\'s ID',
            'tin' => 'TIN ID',
            'sss' => 'SSS ID',
            'philhealth' => 'PhilHealth ID',
            'other' => 'Other Government ID'
        ];

        return $idTypes[$this->valid_id_type] ?? $this->valid_id_type;
    }

    /**
     * Get the display name for employment type
     */
    public function getEmploymentTypeNameAttribute()
    {
        $types = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'temporary' => 'Temporary'
        ];

        return $types[$this->employment_type] ?? $this->employment_type;
    }

    /**
     * Get the status with color class
     */
    public function getStatusClassAttribute()
    {
        $classes = [
            'pending' => 'warning',
            'active' => 'success',
            'inactive' => 'danger',
            'on_leave' => 'info'
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Get the photo URL for valid ID
     */
    public function getValidIdPhotoUrlAttribute()
    {
        if (!$this->valid_id_photo) {
            return null;
        }
        
        return asset('storage/' . $this->valid_id_photo);
    }

    /**
     * Get the resume URL
     */
    public function getResumeUrlAttribute()
    {
        if (!$this->resume) {
            return null;
        }
        
        return asset('storage/' . $this->resume);
    }

    /**
     * Get the employment contract URL
     */
    public function getEmploymentContractUrlAttribute()
    {
        if (!$this->employment_contract) {
            return null;
        }
        
        return asset('storage/' . $this->employment_contract);
    }

    /**
     * Check if HR manager has a user account
     */
    public function hasUserAccount()
    {
        return !is_null($this->user_id);
    }

    /**
     * Scope for active HR managers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending HR managers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for HR managers by parent distributor
     */
    public function scopeByParent($query, $parentDistributorId)
    {
        return $query->where('parent_distributor_id', $parentDistributorId);
    }

    /**
     * Activate HR manager
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();

        if ($this->hasUserAccount()) {
            $this->user->update(['status' => 'active']);
        }
    }

    /**
     * Deactivate HR manager
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();

        if ($this->hasUserAccount()) {
            $this->user->update(['status' => 'inactive']);
        }
    }

    /**
     * Put HR manager on leave
     */
    public function putOnLeave()
    {
        $this->status = 'on_leave';
        $this->save();

        if ($this->hasUserAccount()) {
            $this->user->update(['status' => 'inactive']);
        }
    }

    /**
     * Get formatted salary
     */
    public function getFormattedSalaryAttribute()
    {
        if (!$this->salary) {
            return 'Not set';
        }
        
        return '₱' . number_format($this->salary, 2);
    }

    /**
     * Get formatted hire date
     */
    public function getFormattedHireDateAttribute()
    {
        if (!$this->hire_date) {
            return 'Not set';
        }
        
        return $this->hire_date->format('M d, Y');
    }
}