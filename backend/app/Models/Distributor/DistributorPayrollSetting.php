<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistributorPayrollSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'frequency',
        'day_of_week',
        'bi_monthly_schedule',
        'day_of_month',
        // New fields for late deduction
        'late_deduction_policy',
        'late_deduction_amount',
        'late_deduction_minutes',
    ];

    /**
     * Get the distributor that owns the payroll settings.
     */
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}