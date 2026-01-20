<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class OperationalDistributor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operational_distributors';

    protected $fillable = [
        'parent_distributor_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent distributor (user who created this operational distributor)
     */
    public function parentDistributor()
    {
        return $this->belongsTo(User::class, 'parent_distributor_id');
    }

    /**
     * Get the operational distributor's user account
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the full name of the operational distributor
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
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
            'active' => 'success',
            'inactive' => 'danger'
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Get the photo URL for valid ID
     */
    public function getValidIdPhotoUrlAttribute()
    {
        if (!$this->valid_id_photo) {
            return null;
        }
        
        return asset('storage/' . $this->valid_id_photo);
    }

    /**
     * Check if operational distributor has a user account
     */
    public function hasUserAccount()
    {
        return !is_null($this->user_id);
    }

    /**
     * Scope for active operational distributors
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending operational distributors
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for operational distributors by parent distributor
     */
    public function scopeByParent($query, $parentDistributorId)
    {
        return $query->where('parent_distributor_id', $parentDistributorId);
    }

    /**
     * Activate operational distributor
     */
    public function activate()
    {
        $this->status = 'active';
        $this->save();

        if ($this->hasUserAccount()) {
            $this->user->update(['status' => 'active']);
        }
    }

    /**
     * Deactivate operational distributor
     */
    public function deactivate()
    {
        $this->status = 'inactive';
        $this->save();

        if ($this->hasUserAccount()) {
            $this->user->update(['status' => 'inactive']);
        }
    }
}