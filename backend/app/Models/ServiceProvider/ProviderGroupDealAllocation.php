<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroupDealAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_deal_id',
        'group_id',
        'member_id',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'float',
    ];

    public function deal()
    {
        return $this->belongsTo(OfficialDeal::class, 'official_deal_id');
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