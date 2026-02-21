<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderAddress extends Model
{
    use HasFactory;

    protected $table = 'service_provider_addresses';

    protected $fillable = [
        'service_provider_requirements_id',
        'province',
        'city',
        'barangay',
        'block_address',
        'latitude',
        'longitude',
    ];

    /**
     * Get the requirement that owns the address.
     */
    public function requirement()
    {
        return $this->belongsTo(ServiceProviderRequirement::class, 'service_provider_requirements_id');
    }

    /**
     * Get full formatted address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->block_address}, {$this->barangay}, {$this->city}, {$this->province}";
    }
}