<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product; // Updated namespace
use App\Models\User; // Added to resolve the distributor relationship

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

    // Added missing relationship to fix the 500 error
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}