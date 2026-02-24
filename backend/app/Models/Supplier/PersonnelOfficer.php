<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class PersonnelOfficer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supplier_personnel_officers';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'employment_type',
        'hire_date',
        'position',
        'valid_id_type',
        'id_number',
        'valid_id_photo',
        'resume',
        'employment_contract',
        'status',
    ];

    /**
     * Get the supplier who owns this personnel officer.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get the user account tied to this personnel officer.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getValidIdPhotoUrlAttribute()
    {
        return $this->valid_id_photo ? asset('storage/' . $this->valid_id_photo) : null;
    }
}