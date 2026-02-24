<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'distributor_id',
        'product_id',
        'procurement_request_id',
        'quantity_added',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }
}