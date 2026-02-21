<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderAddress;

class ServiceProviderRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'selfie_with_id_photo',
        'rejection_reason',
        'status',
        'submitted_at',
        'reviewed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the requirement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address record associated with the requirement.
     */
    public function address()
    {
        return $this->hasOne(ServiceProviderAddress::class, 'service_provider_requirements_id');
    }

    /**
     * Check if requirement is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if requirement is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if requirement is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if requirement is submitted.
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['pending', 'verified', 'rejected']);
    }

    /**
     * Mark as pending.
     */
    public function markAsPending(): void
    {
        $this->update([
            'status' => 'pending',
            'submitted_at' => now(),
            'reviewed_at' => null,
            'rejection_reason' => null
        ]);
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'reviewed_at' => now(),
            'rejection_reason' => null
        ]);
    }

    /**
     * Mark as rejected.
     */
    public function markAsRejected(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Get the ID photo URL.
     */
    public function getIdPhotoUrl(): ?string
    {
        return $this->valid_id_photo ? asset('storage/' . $this->valid_id_photo) : null;
    }

    /**
     * Get the selfie photo URL.
     */
    public function getSelfiePhotoUrl(): ?string
    {
        return $this->selfie_with_id_photo ? asset('storage/' . $this->selfie_with_id_photo) : null;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        switch ($this->status) {
            case 'verified':
                return 'badge-success';
            case 'pending':
                return 'badge-warning';
            case 'rejected':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get the readable ID type.
     */
    public function getReadableIdType(): string
    {
        $types = [
            'phil_id' => 'Philippine National ID (PhilID/ePhilID)',
            'passport' => 'Passport',
            'driver_license' => "Driver's License",
            'umid' => 'UMID (SSS/GSIS)',
            'prc_id' => 'PRC ID',
            'voter_id' => "Voter's ID",
            'postal_id' => 'Postal ID',
            'philhealth' => 'PhilHealth ID',
            'nbi' => 'NBI Clearance',
            'senior_citizen' => 'Senior Citizen ID',
            'other' => 'Other Valid ID'
        ];

        return $types[$this->valid_id_type] ?? $this->valid_id_type;
    }
}