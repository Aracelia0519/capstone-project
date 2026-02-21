<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ClientRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the client requirement.
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
        return $this->hasOne(ClientAddress::class, 'client_requirements_id');
    }

    /**
     * Get the display name for ID type
     */
    public function getIdTypeNameAttribute()
    {
        $idTypes = [
            'philid' => 'Philippine National ID (PhilID/ePhilID)',
            'passport' => 'Passport',
            'driver_license' => 'Driver\'s License',
            'umid' => 'UMID (SSS/GSIS)',
            'prc' => 'PRC ID',
            'voter' => 'Voter\'s ID',
            'postal' => 'Postal ID',
            'philhealth' => 'PhilHealth ID',
            'nbi' => 'NBI Clearance',
            'senior_citizen' => 'Senior Citizen ID',
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