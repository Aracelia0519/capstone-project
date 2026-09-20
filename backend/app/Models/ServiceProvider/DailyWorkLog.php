<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EcommerceClient\ClientServiceRequest;

class DailyWorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id',
        'work_date',
        'worked'
    ];

    protected $casts = [
        'worked' => 'boolean'
    ];

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }
}