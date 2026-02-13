<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FinancePayroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

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
        'payment_method',     
        'payment_reference',
        'account_name',       // Added
        'account_number',     // Added
        'admin_notes',        
        'paid_at',           
        'created_by',
        'approved_by'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'paid_at' => 'datetime', 
        'basic_salary' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}