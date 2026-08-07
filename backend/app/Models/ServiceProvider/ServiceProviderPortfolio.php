<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'motto',
        'bio',
        'experience_years',
        'specialties',
        'gallery_images'
    ];

    protected $casts = [
        'gallery_images' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(\App\Models\User::class, 'provider_id');
    }
}