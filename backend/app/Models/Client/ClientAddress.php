<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    use HasFactory;

    protected $table = 'client_addresses';

    protected $fillable = [
        'client_requirements_id',
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
        return $this->belongsTo(ClientRequirement::class, 'client_requirements_id');
    }

    /**
     * Get full formatted address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->block_address}, {$this->barangay}, {$this->city}, {$this->province}";
    }
}