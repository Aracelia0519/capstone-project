<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVatDeduction extends Model
{
    use HasFactory;

    protected $table = 'order_vat_deductions';

    protected $fillable = [
        'order_id',
        'vatable_sales',
        'vat_amount',
    ];

    public function order()
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }
}