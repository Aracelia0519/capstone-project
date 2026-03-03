<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EcommerceClient\ClientServiceRequest;

class SPMessage extends Model
{
    use HasFactory;

    protected $table = 'sp_messages';

    protected $fillable = [
        'sender_id', 
        'receiver_id', 
        'service_request_id', 
        'message', 
        'type', 
        'payload', 
        'is_read'
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'service_request_id');
    }
}