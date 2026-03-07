<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SPPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'service_provider_payment_settings';

    protected $fillable = [
        'service_provider_id',
        'is_on_hand_enabled',
        'is_gcash_enabled',
        'gcash_number',
    ];

    protected $casts = [
        'is_on_hand_enabled' => 'boolean',
        'is_gcash_enabled' => 'boolean',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(User::class, 'service_provider_id');
    }
}