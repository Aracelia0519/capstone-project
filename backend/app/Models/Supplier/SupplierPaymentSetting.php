<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SupplierPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'supplier_payment_settings';

    protected $fillable = [
        'supplier_id',
        'is_cod_enabled',
        'is_gcash_enabled',
        'gcash_number',
    ];

    protected $casts = [
        'is_cod_enabled' => 'boolean',
        'is_gcash_enabled' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}