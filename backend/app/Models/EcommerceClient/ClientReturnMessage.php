<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientReturnMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_request_id',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'file_path',
        'is_read'
    ];

    public function returnRequest()
    {
        return $this->belongsTo(ClientReturnRequest::class, 'return_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}