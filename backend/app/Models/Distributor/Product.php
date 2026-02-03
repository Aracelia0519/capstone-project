<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'distributor_products';

    protected $fillable = [
        'distributor_id',
        'category',
        'type',
        'name',
        'sku_code',
        'size',
        'color_code',
        'price',
        'cost',
        'min_stock_level',
        'max_stock_level',
        'description',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'is_active' => true,
        'min_stock_level' => 10,
        'max_stock_level' => 100
    ];

    /**
     * Get the distributor that owns the product.
     */
    public function distributor()
    {
        return $this->belongsTo(\App\Models\User::class, 'distributor_id');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include products for a specific distributor.
     */
    public function scopeForDistributor($query, $distributorId)
    {
        return $query->where('distributor_id', $distributorId);
    }

    /**
     * Calculate profit margin percentage.
     */
    public function getProfitMarginAttribute()
    {
        if (!$this->cost || $this->cost == 0) {
            return 0;
        }
        return (($this->price - $this->cost) / $this->cost) * 100;
    }

    /**
     * Get profit margin formatted.
     */
    public function getProfitMarginFormattedAttribute()
    {
        return number_format($this->profit_margin, 1) . '%';
    }
}