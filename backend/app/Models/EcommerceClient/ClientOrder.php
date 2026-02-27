<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_number',
        'total_amount',
        'shipping_fee',
        'grand_total',
        'payment_method',
        'status',
        'delivery_address'
    ];

    public function items()
    {
        return $this->hasMany(ClientOrderItem::class, 'order_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}