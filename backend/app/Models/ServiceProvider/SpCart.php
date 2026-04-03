<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product;

class SpCart extends Model
{
    protected $table = 'sp_carts';
    protected $fillable = ['service_provider_id', 'distributor_id', 'product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}