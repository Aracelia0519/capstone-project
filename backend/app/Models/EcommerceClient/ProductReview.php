<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Distributor\Product;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'response',
        'response_date'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }
}