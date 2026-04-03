<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;

class SpReturnMessage extends Model
{
    protected $table = 'sp_return_messages';
    
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
        return $this->belongsTo(SpReturnRequest::class, 'return_request_id');
    }

    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }
}