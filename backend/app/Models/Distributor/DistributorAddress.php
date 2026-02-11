<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorAddress extends Model
{
    use HasFactory;

    protected $table = 'distributor_addresses';

    protected $fillable = [
        'distributor_requirements_id',
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
        return $this->belongsTo(DistributorRequirements::class, 'distributor_requirements_id');
    }

    /**
     * Get full formatted address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->block_address}, {$this->barangay}, {$this->city}, {$this->province}";
    }
}