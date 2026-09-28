<?php

namespace App\Models\EcommerceClient;

use App\Casts\CalendarDate;
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
        'price',
        // The batch this line was actually fulfilled from, so a shipment can be
        // traced back to a lot with a known expiration date.
        'batch_code',
        'expiration_date'
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'expiration_date' => CalendarDate::class,
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