<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HR\Employee;
use App\Models\User;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendances';

    protected $fillable = [
        'employee_id',
        'distributor_id', // Added field
        'date',
        'time_in',
        'time_out',
        'lat_in',
        'long_in',
        'lat_out',
        'long_out',
        'hours_worked',
        'status',
        'notes'
    ];

    /**
     * Get the employee that owns the attendance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the distributor that this attendance belongs to.
     */
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}