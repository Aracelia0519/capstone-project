<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroupShareRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'official_deal_id',
        'member_id',
        'percentage',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'percentage' => 'float',
    ];

    public function group()
    {
        return $this->belongsTo(ProviderGroup::class, 'group_id');
    }

    public function deal()
    {
        return $this->belongsTo(OfficialDeal::class, 'official_deal_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function approvals()
    {
        return $this->hasMany(ProviderGroupShareApproval::class, 'share_request_id');
    }
}