<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityVerificationResult extends Model
{
    use HasFactory;

    protected $table = 'identity_verification_results';

    protected $fillable = [
        'user_id',
        'role',
        'requirement_type',
        'requirement_id',
        'selfie_photo',
        'face_detected',
        'face_match',
        'face_similarity',
        'name_match',
        'id_number_match',
        'credentials_matched',
        'failure_reason',
        'extracted_text',
        'extracted_id_number',
        'manual_review_requested',
        'manual_review_status',
        'manual_review_reason',
        'manual_reviewed_by',
        'manual_reviewed_at',
    ];

    protected $casts = [
        'face_detected' => 'boolean',
        'face_match' => 'boolean',
        'face_similarity' => 'float',
        'name_match' => 'boolean',
        'id_number_match' => 'boolean',
        'credentials_matched' => 'boolean',
        'manual_review_requested' => 'boolean',
        'manual_reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the selfie photo URL.
     */
    public function getSelfiePhotoUrlAttribute()
    {
        return $this->selfie_photo ? asset('storage/' . $this->selfie_photo) : null;
    }
}