<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Distributor\Product;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\User;

class ServiceProductUseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id',
        'client_id',
        'provider_id',
        'sp_inventory_id',
        'product_id',
        'product_name',
        'quantity',
        'base_unit_price',
        'status',
        'rejection_reason',
        'offer_percentage',
        'offer_by',
        'approved_percentage',
        'approved_at',
        'material_expense_request_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'base_unit_price' => 'float',
        'offer_percentage' => 'float',
        'approved_percentage' => 'float',
        'approved_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
