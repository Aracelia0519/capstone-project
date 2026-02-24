<?php

namespace App\Models\PersonnelOfficer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supplier_personnels';

    protected $guarded = ['id'];

    /**
     * Get the associated user account.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the supplier this personnel belongs to.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get the personnel officer who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the assigned accessibilities for this personnel.
     */
    public function accessibilities()
    {
        return $this->hasMany(PersonnelAccessibility::class, 'personnel_id');
    }
}