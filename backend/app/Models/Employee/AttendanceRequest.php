<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HR\Employee;
use App\Models\User;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $table = 'attendance_requests';

    protected $fillable = [
        'employee_id',
        'distributor_id',
        'type',
        'requested_time',
        'photo_proof',
        'reason',
        'latitude',
        'longitude',
        'status',
        'admin_remarks',
        'approved_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}