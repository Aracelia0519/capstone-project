<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product; // Updated namespace

class ClientOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'distributor_id',
        'product_id',
        'quantity',
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // Updated reference
    }
}