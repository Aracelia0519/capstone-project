<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 
        'status', 
        'browser', 
        'failure_reason', 
        'logged_in_at', 
        'Fullname', 
        'role'
    ];
}