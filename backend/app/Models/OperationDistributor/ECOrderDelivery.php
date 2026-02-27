<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\HR\Employee;

class ECOrderDelivery extends Model
{
    use HasFactory;

    protected $table = 'ec_order_deliveries';

    protected $fillable = [
        'order_id',
        'delivery_personnel_id',
        'preparation_proof_path',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Employee::class, 'delivery_personnel_id');
    }
}