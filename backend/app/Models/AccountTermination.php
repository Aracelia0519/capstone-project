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

    /**
     * Without these, terminated_at and reversed_at come back as raw strings and
     * any caller that treats them as dates -- comparing them against a
     * requirements row, or formatting them for the admin screen -- has to guess.
     */
    protected $casts = [
        'terminated_at' => 'datetime',
        'reversed_at'   => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(User::class, 'account_id');
    }
}