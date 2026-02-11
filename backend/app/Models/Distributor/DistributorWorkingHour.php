<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorWorkingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'day_of_week',
        'is_open',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    // Helper to format time for frontend (removes seconds if needed)
    public function getStartTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }
}