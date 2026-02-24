<?php

namespace App\Models\PersonnelOfficer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelAccessibility extends Model
{
    use HasFactory;

    protected $table = 'supplier_personnel_accessibilities';

    protected $fillable = [
        'personnel_id',
        'module_path',
        'module_name'
    ];

    /**
     * Get the personnel this accessibility belongs to.
     */
    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }
}