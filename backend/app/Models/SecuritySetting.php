<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'role',
        'email_login_alerts',
        'one_device_login',
        'session_timeout',
        'remember_this_device',
        'account_recovery_email',
        'security_questions',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}