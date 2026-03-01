<?php

namespace App\Models\DistributorDelivery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\HR\Employee;
use App\Models\EcommerceClient\ClientOrder;

class ECDeliveryRemittance extends Model
{
    use HasFactory;

    protected $table = 'ec_delivery_remittances';

    protected $fillable = [
        'distributor_id',
        'delivery_personnel_id',
        'order_id',
        'amount',
        'remittance_proof_path',
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function deliveryPersonnel()
    {
        return $this->belongsTo(Employee::class, 'delivery_personnel_id');
    }

    public function order()
    {
        return $this->belongsTo(ClientOrder::class, 'order_id');
    }
}