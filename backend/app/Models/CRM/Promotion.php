<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Promotion extends Model
{
    use HasFactory;

    protected $table = 'crm_promotions';

    protected $fillable = [
        'user_id', 
        'distributor_id', 
        'product_id', 
        'name', 
        'type', 
        'discount_value',
        'code', 
        'usage_limit', 
        'used_count', 
        'start_date', 
        'end_date', 
        'status'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Updated relationship to point to the correct namespace and class
    public function product()
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }
}