<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;

class SpReturnRequest extends Model
{
    protected $table = 'sp_return_requests';
    
    protected $fillable = [
        'order_item_id', 
        'sp_id', 
        'distributor_id', 
        'reason', 
        'proof_image_path', 
        'status', 
        'tracking_number', 
        'tracking_proof_path'
    ];

    public function orderItem()
    {
        return $this->belongsTo(\App\Models\ServiceProvider\SpOrderItem::class, 'order_item_id');
    }

    public function messages()
    {
        return $this->hasMany(SpReturnMessage::class, 'return_request_id');
    }
}