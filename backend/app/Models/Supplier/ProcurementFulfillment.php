<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OperationDistributor\ProcurementRequest;

class ProcurementFulfillment extends Model
{
    use HasFactory;

    protected $table = 'procurement_fulfillments';

    protected $fillable = [
        'procurement_request_id',
        'receipt_file_path',
        'proof_file_path',
        'notes',
        'prepared_at'
    ];

    protected $casts = [
        'prepared_at' => 'datetime',
    ];

    /**
     * Get the procurement request associated with this fulfillment.
     */
    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }
}