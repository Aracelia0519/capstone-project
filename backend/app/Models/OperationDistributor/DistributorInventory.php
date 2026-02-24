<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorInventory extends Model
{
    use HasFactory;

    protected $table = 'distributor_inventories';

    protected $fillable = [
        'distributor_id',
        'product_id',
        'quantity',
        'ecommerce_status',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }

    public function distributor()
    {
        return $this->belongsTo(\App\Models\User::class, 'distributor_id');
    }
}