<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Support\Documents\DocumentFile;

class SupplierRequirements extends Model
{
    use HasFactory;

    protected $table = 'supplier_requirements';

    protected $fillable = [
        'user_id',
        'company_name',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'dti_certificate_photo',
        'dti_certificate_expiration',
        'mayor_permit_photo',
        'mayor_permit_expiration',
        'barangay_clearance_photo',
        'business_registration_number',
        'business_registration_photo',
        'status',
        'rejection_reason',
        'documents_verified_at',
        'documents_verified_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dti_certificate_expiration' => 'date',
        'mayor_permit_expiration'   => 'date',
        'documents_verified_at'     => 'datetime',
    ];

    /**
     * Admin reviews of this account's renewed DTI Certificate / Mayor's Permit.
     *
     * Newest first, so the pending item an admin has to act on is the first row
     * rather than the last.
     */
    public function documentReviews()
    {
        return $this->morphMany(\App\Models\DocumentReview::class, 'reviewable')
            ->latest('id');
    }

    /**
     * Documents the supplier attached beyond the fixed set.
     */
    public function relatedDocuments()
    {
        return $this->morphMany(\App\Models\RelatedDocument::class, 'documentable')
            ->latest('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->hasOne(SupplierAddress::class, 'supplier_requirements_id');
    }

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

    public function getStatusClassAttribute()
    {
        $classes = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'draft'    => 'secondary'
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * An absolute URL for one uploaded photo.
     *
     * DocumentFile rather than asset(): asset() builds from APP_URL, which is
     * http://localhost with no port, so the browser would request port 80 while
     * the API that serves storage listens on :8000.
     */
    public function getPhotoUrl($field)
    {
        if (!$this->$field) {
            return null;
        }

        return DocumentFile::url($this->$field);
    }

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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}