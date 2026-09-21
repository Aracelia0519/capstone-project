<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroupServiceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_offering_id',
        'group_id',
        'member_id',
        'status',
        'rejection_reason',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function serviceOffering()
    {
        return $this->belongsTo(ServiceOffering::class, 'service_offering_id');
    }

    public function group()
    {
        return $this->belongsTo(ProviderGroup::class, 'group_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}