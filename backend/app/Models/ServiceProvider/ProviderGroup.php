<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'description',
        'leader_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members()
    {
        return $this->hasMany(ProviderGroupMember::class, 'group_id');
    }

    public function acceptedMembers()
    {
        return $this->members()->where('status', 'accepted');
    }
}