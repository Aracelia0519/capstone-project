<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EcommerceClient\ClientServiceRequest;

class ServiceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id',
        'provider_id',
        'client_id',
        'rating',
        'comment',
        'reply',
        'is_hidden',
        'client_reply'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
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