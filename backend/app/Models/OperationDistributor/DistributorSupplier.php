<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistributorSupplier extends Model
{
    use HasFactory;

    protected $table = 'distributor_suppliers';

    protected $fillable = [
        'distributor_id',
        'supplier_id',
        'created_by',
        'status',
        'request_message',
        'rejection_reason',
        'distributor_approved_at',
        'supplier_approved_at',
        'agreement_path',
        'distributor_signed_at',
        'distributor_signature_path',
        'supplier_signed_at',          // NEW: For supplier digital signature timestamp
        'supplier_signature_path'      // NEW: For supplier signature image
    ];

    protected $casts = [
        'distributor_approved_at' => 'datetime',
        'supplier_approved_at' => 'datetime',
        'distributor_signed_at' => 'datetime',
        'supplier_signed_at' => 'datetime',
    ];

    // Relationships
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}