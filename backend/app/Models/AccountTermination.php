<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTermination extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id', 
        'role', 
        'terminated_by', 
        'termination_type',
        'reason', 
        'status', 
        'terminated_at', 
        'reversed_at',
        'reversed_by', 
        'reversal_reason'
    ];

    public function account()
    {
        return $this->belongsTo(User::class, 'account_id');
    }
}