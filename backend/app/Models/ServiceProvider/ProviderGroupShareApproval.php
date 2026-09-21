<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProviderGroupShareApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_request_id',
        'member_id',
        'status',
        'reason',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function shareRequest()
    {
        return $this->belongsTo(ProviderGroupShareRequest::class, 'share_request_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}