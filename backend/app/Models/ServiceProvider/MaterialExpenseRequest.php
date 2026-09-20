<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EcommerceClient\ClientServiceRequest;

class MaterialExpenseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_service_request_id',
        'provider_id',
        'proof_photo_path',
        'status',
        'rejection_reason',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(MaterialExpenseItem::class, 'material_expense_request_id');
    }

    public function clientServiceRequest()
    {
        return $this->belongsTo(ClientServiceRequest::class, 'client_service_request_id');
    }

    /**
     * Sum of the line-item totals inside this batch.
     * Falls back to a query when items are not eager loaded.
     */
    public function getItemsTotalAttribute()
    {
        return $this->items ? (float) $this->items->sum('total_price') : 0;
    }
}