<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientPaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'client_payment_settings';

    protected $fillable = [
        'client_id',
        'gcash_number',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}