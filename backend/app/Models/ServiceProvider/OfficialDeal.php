<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EcommerceClient\ClientServiceRequest;

class OfficialDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id', 
        'service_offering_id', 
        'provider_id', 
        'client_id',
        'price', 
        'preferred_date', 
        'time_preference', 
        'contact_number', 
        'address', 
        'description', 
        'status'
    ];

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }

    public function serviceOffering()
    {
        return $this->belongsTo(ServiceOffering::class, 'service_offering_id');
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