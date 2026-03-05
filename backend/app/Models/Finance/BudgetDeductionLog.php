<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BudgetDeductionLog extends Model
{
    use HasFactory;

    protected $table = 'budget_deduction_logs';

    protected $fillable = [
        'distributor_id',
        'procurement_request_id',
        'amount',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function distributor()
    {
        return $this->belongsTo(\App\Models\User::class, 'distributor_id');
    }

    public function procurementRequest()
    {
        return $this->belongsTo(\App\Models\OperationDistributor\ProcurementRequest::class, 'procurement_request_id');
    }
}