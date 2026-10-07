<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail for every movement of distributor stock.
 *
 * Before batch tracking this only recorded stock ARRIVING. It now records both
 * directions and names the batch involved, so a quantity can always be explained:
 *
 *   procurement_receipt   stock arrived from a supplier delivery
 *   replacement_receipt   replacement units arrived against a return
 *   sale                 units sold to a client or service provider
 *   restock              units put back after a failed delivery
 *   manual_adjustment    a correction made by staff
 *   archive              units pulled out of the active supply chain
 *   deactivation         units moved to the inactive table
 *   reactivation         units returned from the inactive table
 */
class InventoryLog extends Model
{
    use HasFactory;

    protected $table = 'inventory_logs';

    protected $fillable = [
        'distributor_id',
        'product_id',
        'inventory_id',
        'batch_code',
        'expiration_date',
        'procurement_request_id',
        'quantity_added',
        'quantity_removed',
        'event_type',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity_added'   => 'integer',
        'quantity_removed' => 'integer',
        'expiration_date'  => 'date',
    ];

    // -----------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    /** The batch this movement touched. */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DistributorInventory::class, 'inventory_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
