<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product;
use App\Models\User; // Import the User model for the distributor

class SpOrderItem extends Model
{
    protected $table = 'sp_order_items';
    
    protected $fillable = [
        'sp_order_id', 
        'distributor_id', 
        'product_id', 
        'quantity', 
        'price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // ADDED THIS RELATIONSHIP to fix the error
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}