<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class SupplierRawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category',
        'type',
        'name',
        'sku_code',
        'size',
        'color_code',
        'price',
        'description',
        'image_url',
        'is_active'
    ];

    /**
     * Get the supplier (user) that owns the material.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}