<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EcommerceClient\ClientServiceRequest;

class ServiceSurveyAgreement extends Model
{
    use HasFactory;

    protected $table = 'service_survey_agreements';

    protected $fillable = [
        'client_service_request_id',
        'client_id',
        'provider_id',
        'agreement_text',
        'storage_path',
        'status',
        'provider_signed_at',
        'client_signed_at',
        'survey_started_at',
        'survey_completed_at',
        'provider_signature', // Included for Canvas Base64 save
        'client_signature',   // Included for Canvas Base64 save
    ];

    protected $casts = [
        'provider_signed_at' => 'datetime',
        'client_signed_at' => 'datetime',
        'survey_started_at' => 'datetime',
        'survey_completed_at' => 'datetime',
    ];

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}