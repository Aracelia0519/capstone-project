<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_code',
        'user_id',
        'user_type',
        'department',
        'position',
        'period_start',
        'period_end',
        'pay_date',
        'basic_salary',
        'gross_pay',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'total_deductions',
        'net_pay',
        'status',
        'created_by',
        'approved_by'
    ];

    protected $dates = ['period_start', 'period_end', 'pay_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}