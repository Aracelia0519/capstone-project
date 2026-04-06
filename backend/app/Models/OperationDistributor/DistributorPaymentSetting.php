<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistributorPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'distributor_payment_settings';

    protected $fillable = [
        'distributor_id',
        'is_cod_enabled',
        'is_gcash_enabled',
        'is_pickup_enabled',
        'is_bank_enabled',
        'gcash_number',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
    ];

    protected $casts = [
        'is_cod_enabled' => 'boolean',
        'is_gcash_enabled' => 'boolean',
        'is_pickup_enabled' => 'boolean',
        'is_bank_enabled' => 'boolean',
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}