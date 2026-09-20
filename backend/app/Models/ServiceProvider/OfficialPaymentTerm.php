<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OfficialPaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'official_deal_id',
        'provider_id',
        'client_id',
        'payment_method',
        'payment_term',
        'status',
        'proof_rejection_reason',
        'is_materials_term',
        'amount'
    ];

    protected $casts = [
        'is_materials_term' => 'boolean',
        'amount' => 'float'
    ];

    public function deal()
    {
        return $this->belongsTo(OfficialDeal::class, 'official_deal_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}