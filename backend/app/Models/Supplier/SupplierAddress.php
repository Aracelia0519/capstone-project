<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierAddress extends Model
{
    use HasFactory;

    protected $table = 'supplier_addresses';

    protected $fillable = [
        'supplier_requirements_id',
        'province',
        'city',
        'barangay',
        'block_address',
        'latitude',
        'longitude',
    ];

    public function requirement()
    {
        return $this->belongsTo(SupplierRequirements::class, 'supplier_requirements_id');
    }

    public function getFullAddressAttribute()
    {
        return "{$this->block_address}, {$this->barangay}, {$this->city}, {$this->province}";
    }
}