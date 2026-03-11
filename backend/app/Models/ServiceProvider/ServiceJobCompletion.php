<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EcommerceClient\ClientServiceRequest;

class ServiceJobCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id',
        'proof_images',
        'rejection_reason',
        'status'
    ];

    protected $casts = [
        'proof_images' => 'array'
    ];

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }
}