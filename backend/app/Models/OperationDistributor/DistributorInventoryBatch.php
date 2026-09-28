<?php

namespace App\Models\OperationDistributor;

use App\Casts\CalendarDate;
use App\Support\Inventory\BatchRules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One lot of a product held by a distributor.
 *
 * A product may have many batches with different quantities and different
 * expiration dates. Sales consume them FEFO (First Expiring, First Out) via
 * {@see \App\Support\Inventory\BatchInventoryService::sellFefo()}.
 */
class DistributorInventoryBatch extends Model
{
    use HasFactory;

    protected $table = 'distributor_inventory_batches';

    protected $fillable = [
        'distributor_inventory_id',
        'distributor_id',
        'product_id',
        'batch_code',
        'quantity',
        'expiration_date',
        'source_procurement_request_id',
        'source_raw_material_id',
        'received_at',
        'is_archived',
        'archived_at',
        'archive_reason',
        'archived_by',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'is_archived'     => 'boolean',
        'expiration_date' => CalendarDate::class,
        'received_at'     => 'datetime',
        'archived_at'     => 'datetime',
    ];

    protected $appends = ['is_expired', 'health', 'days_until_expiry', 'is_sellable'];

    // -----------------------------------------------------------------

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(DistributorInventory::class, 'distributor_inventory_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'distributor_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'archived_by');
    }

    public function procurementRequest(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'source_procurement_request_id');
    }

    public function sourceRawMaterial(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier\SupplierRawMaterial::class, 'source_raw_material_id');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeLive($query)
    {
        return $query->where('is_archived', false)->where('quantity', '>', 0);
    }

    /** In stock, not archived, and not past its expiration date. */
    public function scopeSellable($query)
    {
        return $query->where('is_archived', false)
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString());
    }

    /**
     * First-Expiring-First-Out.
     *
     * Batches with no expiration date sort LAST. MySQL would otherwise place every
     * NULL ahead of every real date on an ASC sort, so non-perishables would be
     * sold before the paint that actually has a deadline.
     */
    public function scopeFefo($query)
    {
        return $query->orderByRaw(BatchRules::fefoOrderBy());
    }

    // -----------------------------------------------------------------
    // Computed attributes
    // -----------------------------------------------------------------

    public function getIsExpiredAttribute(): bool
    {
        return BatchRules::isExpired($this->expiration_date);
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return BatchRules::daysUntilExpiry($this->expiration_date);
    }

    public function getHealthAttribute(): string
    {
        return BatchRules::health($this->expiration_date);
    }

    public function getIsSellableAttribute(): bool
    {
        return ! $this->is_archived
            && (int) $this->quantity > 0
            && ! $this->is_expired;
    }
}
