<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Distributor\Product; // Updated namespace

class ClientCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'distributor_id',
        'product_id',
        'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); // Updated reference
    }
}