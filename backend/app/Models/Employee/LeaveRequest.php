<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HR\Employee;
use App\Models\User;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_requests';

    protected $fillable = [
        'employee_id',
        'distributor_id',
        'type',
        'start_date',
        'end_date',
        'duration',
        'reason',
        'status',
        'is_paid', // Added
        'rejection_reason',
        'approved_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration' => 'float',
        'is_paid' => 'boolean', // Added
    ];

    // Relationship to the Employee (The person filing)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relationship to the Distributor (The business owner)
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    // Relationship to the Approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    // Helper to format date range
    public function getDateRangeAttribute()
    {
        if ($this->start_date->equalTo($this->end_date)) {
            return $this->start_date->format('M d, Y');
        }
        return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }
}