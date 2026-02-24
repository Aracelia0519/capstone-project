<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRule extends Model
{
    use HasFactory;

    protected $table = 'shipping_rules';

    protected $fillable = [
        'base_rate_per_km',
        'rate_per_item',
        'free_shipping_threshold',
    ];
}