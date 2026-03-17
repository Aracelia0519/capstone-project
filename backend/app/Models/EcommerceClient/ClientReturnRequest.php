<?php

namespace App\Models\EcommerceClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'client_id',
        'distributor_id',
        'reason',
        'proof_image_path',
        'status',
        'tracking_number',
        'tracking_proof_path'
    ];

    public function orderItem()
    {
        return $this->belongsTo(ClientOrderItem::class, 'order_item_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function messages()
    {
        return $this->hasMany(ClientReturnMessage::class, 'return_request_id');
    }
}