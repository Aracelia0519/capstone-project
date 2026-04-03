<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;

class SpOrder extends Model
{
    protected $table = 'sp_orders';
    protected $fillable = [
        'service_provider_id', 'order_number', 'total_amount', 'shipping_fee', 
        'grand_total', 'payment_method', 'status', 'rejection_reason', 'delivery_address'
    ];

    public function items()
    {
        return $this->hasMany(SpOrderItem::class, 'sp_order_id');
    }
}