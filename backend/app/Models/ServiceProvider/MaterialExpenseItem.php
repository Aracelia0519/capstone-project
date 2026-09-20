<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialExpenseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_expense_request_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'total_price' => 'float'
    ];

    public function request()
    {
        return $this->belongsTo(MaterialExpenseRequest::class, 'material_expense_request_id');
    }
}