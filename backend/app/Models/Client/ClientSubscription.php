<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'plan_name',
        'features',
        'amount',
        'status',
        'start_date',
        'end_date',
        'reference_number',
        'paymongo_session_id',
        'admin_receiving_number'
    ];

    protected $casts = [
        'features' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}