<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServiceOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'title',
        'category',
        'price',
        'price_type',
        'duration',
        'description',
        'image_paths',
        'is_active'
    ];

    protected $casts = [
        'image_paths' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}