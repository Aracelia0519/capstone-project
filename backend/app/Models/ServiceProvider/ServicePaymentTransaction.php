<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ServicePaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_term_id',
        'client_id',
        'provider_id',
        'amount',
        'payment_method',
        'reference_number',
        'status'
    ];

    public function paymentTerm()
    {
        return $this->belongsTo(OfficialPaymentTerm::class, 'payment_term_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}