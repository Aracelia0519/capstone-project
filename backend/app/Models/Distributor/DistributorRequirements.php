<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DistributorRequirements extends Model
{
    use HasFactory;

    protected $table = 'distributor_requirements';

    protected $fillable = [
        'user_id',
        'company_name',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'dti_certificate_photo',
        'mayor_permit_photo',
        'barangay_clearance_photo',
        'business_registration_number',
        'business_registration_photo',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the distributor requirement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * NEW: Get the address record associated with the requirement.
     */
    public function address()
    {
        return $this->hasOne(DistributorAddress::class, 'distributor_requirements_id');
    }

    /**
     * Get the display name for ID type
     */
    public function getIdTypeNameAttribute()
    {
        $idTypes = [
            'passport' => 'Passport',
            'driver_license' => 'Driver\'s License',
            'umid' => 'UMID (SSS/GSIS)',
            'prc' => 'PRC ID',
            'postal' => 'Postal ID',
            'voter' => 'Voter\'s ID',
            'tin' => 'TIN ID',
            'sss' => 'SSS ID',
            'philhealth' => 'PhilHealth ID',
            'other' => 'Other Government ID'
        ];

        return $idTypes[$this->valid_id_type] ?? $this->valid_id_type;
    }

    /**
     * Get the status with color class
     */
    public function getStatusClassAttribute()
    {
        $classes = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Get the photo URL for a given field
     */
    public function getPhotoUrl($field)
    {
        if (!$this->$field) {
            return null;
        }
        
        return asset('storage/' . $this->$field);
    }

    /**
     * Get all photo URLs
     */
    public function getAllPhotoUrls()
    {
        $photos = [];
        $photoFields = [
            'valid_id_photo',
            'dti_certificate_photo',
            'mayor_permit_photo',
            'barangay_clearance_photo',
            'business_registration_photo'
        ];

        foreach ($photoFields as $field) {
            $photos[$field] = $this->getPhotoUrl($field);
        }

        return $photos;
    }

    /**
     * Check if all required documents are uploaded
     */
    public function getIsCompleteAttribute()
    {
        return !empty($this->company_name) &&
               !empty($this->valid_id_type) &&
               !empty($this->id_number) &&
               !empty($this->valid_id_photo) &&
               !empty($this->dti_certificate_photo) &&
               !empty($this->mayor_permit_photo) &&
               !empty($this->barangay_clearance_photo) &&
               !empty($this->business_registration_number) &&
               !empty($this->business_registration_photo);
    }

    /**
     * Scope for pending requirements
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requirements
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requirements
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}