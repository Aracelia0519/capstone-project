<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServiceProviderDistributor extends Model
{
    use HasFactory;

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
        'distributor_signature_path'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'sp_signed_at' => 'datetime',
        'distributor_signed_at' => 'datetime',
    ];

    /**
     * Get the service provider who initiated the request.
     */
    public function serviceProvider()
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }

    /**
     * Get the distributor receiving the request.
     */
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }
}