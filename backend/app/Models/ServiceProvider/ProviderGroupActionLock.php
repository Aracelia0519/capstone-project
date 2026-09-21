<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroupActionLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'entity_type',
        'entity_id',
        'action',
        'member_id',
        'locked_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(ProviderGroup::class, 'group_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}