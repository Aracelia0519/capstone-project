<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceProvider\ServiceProviderDistributor;

class ServiceProviderAgreement extends Model
{
    use HasFactory;

    protected $table = 'service_provider_agreements';

    protected $fillable = [
        'service_provider_distributor_id',
        'document_path',
        'generated_at'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Get the partnership request this agreement belongs to.
     */
    public function serviceProviderDistributor()
    {
        return $this->belongsTo(ServiceProviderDistributor::class, 'service_provider_distributor_id');
    }
}