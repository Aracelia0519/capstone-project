<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ServiceProvider\ServiceOffering;

class ClientServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_offering_id',
        'provider_id',
        'description',
        'preferred_date',
        'time_preference',
        'contact_number',
        'address',
        'status'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function serviceOffering()
    {
        return $this->belongsTo(ServiceOffering::class, 'service_offering_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}