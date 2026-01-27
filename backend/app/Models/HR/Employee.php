<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Distributor\DistributorRequirements;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_employees';

    protected $fillable = [
        'parent_distributor_id',
        'hr_manager_id',
        'created_by_user_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'emergency_contact',
        'address',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'department',
        'position',
        'job_title',
        'employment_type',
        'employment_status',
        'hire_date',
        'probation_end_date',
        'regularization_date',
        'salary',
        'salary_currency',
        'payment_frequency',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'educational_attainment',
        'school_graduated',
        'year_graduated',
        'course',
        'resume',
        'employment_contract',
        'medical_certificate',
        'nbi_clearance',
        'police_clearance',
        'status',
        'notes'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'regularization_date' => 'date',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function parentDistributor()
    {
        return $this->belongsTo(User::class, 'parent_distributor_id');
    }

    public function distributorRequirement()
    {
        return $this->belongsTo(DistributorRequirements::class, 'parent_distributor_id', 'user_id');
    }

    public function hrManager()
    {
        return $this->belongsTo(\App\Models\Distributor\HRManager::class, 'hr_manager_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeProbationary($query)
    {
        return $query->where('employment_status', 'probationary');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByDistributor($query, $distributorId)
    {
        return $query->where('parent_distributor_id', $distributorId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $names = [$this->first_name];
        if ($this->middle_name) {
            $names[] = $this->middle_name;
        }
        $names[] = $this->last_name;
        return implode(' ', $names);
    }

    public function getFormattedSalaryAttribute()
    {
        return number_format($this->salary, 2) . ' ' . $this->salary_currency;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getYearsOfServiceAttribute()
    {
        return $this->hire_date ? $this->hire_date->diffInYears(now()) : null;
    }

    // Business Logic Methods
    public function isProbationary()
    {
        return $this->employment_status === 'probationary';
    }

    public function isRegular()
    {
        return $this->employment_status === 'regular';
    }

    public function canBeRegularized()
    {
        return $this->isProbationary() && 
               $this->probation_end_date && 
               now()->greaterThanOrEqualTo($this->probation_end_date);
    }

    public function regularize()
    {
        if ($this->canBeRegularized()) {
            $this->update([
                'employment_status' => 'regular',
                'regularization_date' => now()->toDateString()
            ]);
            return true;
        }
        return false;
    }

    public function terminate($reason = '')
    {
        $this->update([
            'status' => 'inactive',
            'employment_status' => 'terminated',
            'notes' => $this->notes . "\nTermination: " . now()->format('Y-m-d') . " - " . $reason
        ]);
    }

    public function getDistributorCompanyName()
    {
        if ($this->distributorRequirement) {
            return $this->distributorRequirement->company_name;
        }
        return $this->parentDistributor->full_name . ' (Distributor)';
    }

    // Generate employee code
    public static function generateEmployeeCode($distributorId)
    {
        $year = date('Y');
        $month = date('m');
        $distributorCode = strtoupper(substr(md5($distributorId), 0, 4));
        
        $lastEmployee = self::where('parent_distributor_id', $distributorId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('employee_code', 'desc')
            ->first();

        if ($lastEmployee && preg_match('/EMP-(\d+)$/', $lastEmployee->employee_code, $matches)) {
            $sequence = str_pad((int)$matches[1] + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "EMP-{$distributorCode}-{$year}{$month}-{$sequence}";
    }
}