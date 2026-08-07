<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_user_id',
        'reported_by_id',
        'reporter_role',
        'reported_user_role',
        'reason',
        'description',
        'incident_date',
        'evidence_path',
        'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}