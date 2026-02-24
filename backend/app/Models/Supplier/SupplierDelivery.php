<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\PersonnelOfficer\Personnel;

class SupplierDelivery extends Model
{
    use HasFactory;

    protected $table = 'supplier_deliveries';

    protected $fillable = [
        'procurement_request_id',
        'delivery_personnel_id',
        'shipping_proof_path',
        'arrival_proof_path',
        'payment_received_proof_path',
        'remittance_proof_path',
        'status',
        'notes',
        'assigned_at',
        'delivered_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function deliveryPersonnel()
    {
        return $this->belongsTo(Personnel::class, 'delivery_personnel_id');
    }
}