<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product; 
use App\Models\User;

class SpInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'distributor_id',
        'product_id',
        'quantity',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); 
    }

    public function serviceProvider()
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}