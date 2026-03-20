<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProcurementReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'distributor_id',
        'supplier_id',
        'quantity_returned',
        'reason',
        'proof_image_path',
        'status',
        'rejection_reason',
        'replacement_receipt_path',
        'replacement_proof_path'
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}