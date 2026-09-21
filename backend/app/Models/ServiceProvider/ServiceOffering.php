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
        'group_id',
        'title',
        'category',
        'price',
        'price_type',
        'duration',
        'description',
        'image_paths',
        'is_active',
        'is_published'
    ];

    protected $casts = [
        'image_paths' => 'array',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function group()
    {
        return $this->belongsTo(ProviderGroup::class, 'group_id');
    }

    public function groupApprovals()
    {
        return $this->hasMany(ProviderGroupServiceApproval::class, 'service_offering_id');
    }
}