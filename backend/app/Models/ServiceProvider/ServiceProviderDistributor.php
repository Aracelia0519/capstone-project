<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServiceProviderDistributor extends Model
{
    protected $table = 'service_provider_distributors';

    protected $fillable = [
        'service_provider_id',
        'distributor_id',
        'status',
        'request_message',
        'rejection_reason',
        'approved_at',
        'agreement_path',
        'sp_signed_at',
        'sp_signature_path',
        'distributor_signed_at',
        'distributor_signature_path',
        'termination_path',
        'distributor_termination_signed_at',
        'distributor_termination_signature_path',
        'sp_termination_signed_at',
        'sp_termination_signature_path',
        'contract_end_date',
        'proposed_end_date',
        'last_proposed_by',
        'terms',
    ];

    protected $casts = [
        'terms' => 'array',
        'sp_signed_at' => 'datetime',
        'distributor_signed_at' => 'datetime',
        'approved_at' => 'datetime',
        'contract_end_date' => 'date',
        'proposed_end_date' => 'date',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}