<?php

namespace App\Models\OperationDistributor;

use App\Casts\CalendarDate;
use App\Support\Inventory\BatchRules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PRODUCT-LEVEL stock row for a distributor: one per (distributor_id, product_id).
 *
 * IMPORTANT — what `quantity` means here
 * ──────────────────────────────────────
 * `quantity` is the TOTAL across every batch of this product. It is a cached
 * rollup of `distributor_inventory_batches`, not a batch quantity.
 *
 * This was left deliberately unchanged. Seventeen controllers and reports across
 * the codebase read this column directly, and the unique key
 * (distributor_id, product_id) still guarantees one row per product. The
 * per-lot detail lives in {@see DistributorInventoryBatch} instead.
 *
 * The `batch_code` / `expiration_date` columns here are a summary of the batch
 * that expires soonest, so list screens can sort and colour rows without a join.
 */
class DistributorInventory extends Model
{
    use HasFactory;

    protected $table = 'distributor_inventories';

    protected $fillable = [
        'distributor_id',
        'product_id',
        'quantity',
        'ecommerce_status',
        'batch_code',
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

    protected $appends = ['is_expired', 'health', 'days_until_expiry', 'batch_count'];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    /** Every lot of this product. */
    public function batches(): HasMany
    {
        return $this->hasMany(DistributorInventoryBatch::class, 'distributor_inventory_id');
    }

    /** Only the lots still in play. */
    public function liveBatches(): HasMany
    {
        return $this->hasMany(DistributorInventoryBatch::class, 'distributor_inventory_id')
            ->where('is_archived', false)
            ->where('quantity', '>', 0);
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

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'inventory_id');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    public function scopeLive($query)
    {
        return $query->where('is_archived', false)->where('quantity', '>', 0);
    }

    /**
     * Products with at least one batch a customer may buy.
     *
     * The expiry test runs against the CHILD batches, not this row's cached
     * `expiration_date`, so a product whose newest batch is still good stays
     * sellable even while an older batch is expiring.
     */
    public function scopeSellable($query, ?int $distributorId = null)
    {
        $query->where('is_archived', false)
            ->where('quantity', '>', 0)
            ->whereHas('batches', function ($b) {
                $b->where('is_archived', false)
                    ->where('quantity', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('expiration_date')
                            ->orWhereDate('expiration_date', '>=', now()->toDateString());
                    });
            });

        if ($distributorId !== null) {
            $query->where('distributor_id', $distributorId);
        }

        return $query;
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

    public function getBatchCountAttribute(): int
    {
        return $this->batches()->count();
    }

    // -----------------------------------------------------------------
    // Behaviour
    // -----------------------------------------------------------------

    /**
     * Write the sum of the live child batches back onto this row, together with a
     * summary of whichever batch expires soonest.
     *
     * Called by BatchInventoryService after every movement so the flat `quantity`
     * that the rest of the application reads can never drift from reality.
     */
    public function syncBatchTotals(): void
    {
        $rollup = $this->batches()
            ->where('is_archived', false)
            ->selectRaw('COALESCE(SUM(quantity), 0) AS qty')
            ->selectRaw('COUNT(*) AS batches')
            ->first();

        // The summary batch is the one that will be consumed NEXT, so it is chosen
        // with the same FEFO ordering the checkout uses. Restricted to lots that
        // still hold stock, otherwise a drained lot with an old date would keep
        // being reported as the soonest expiry.
        $soonest = $this->batches()
            ->where('is_archived', false)
            ->where('quantity', '>', 0)
            ->orderByRaw(BatchRules::fefoOrderBy())
            ->first();

        $this->forceFill([
            'quantity'        => (int) ($rollup->qty ?? 0),
            'batch_code'      => $soonest?->batch_code,
            // The CalendarDate cast formats this back to `Y-m-d`, so passing the
            // Carbon through as-is is both correct and cheaper than string surgery.
            'expiration_date' => $soonest?->expiration_date,
            // Once every batch is gone the product itself leaves the supply chain.
            'is_archived'     => (int) ($rollup->qty ?? 0) <= 0 && (int) ($rollup->batches ?? 0) > 0,
        ])->saveQuietly();
    }
}
