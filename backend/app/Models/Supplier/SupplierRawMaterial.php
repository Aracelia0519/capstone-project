<?php

namespace App\Models\Supplier;

use App\Casts\CalendarDate;
use App\Support\Inventory\BatchRules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * A supplier's product/variant.
 *
 * The `quantity`, `reserved_quantity` and `expiration_date` columns on this table
 * are CACHED ROLLUPS of `supplier_raw_material_batches`, kept in sync by
 * {@see \App\Support\Inventory\BatchInventoryService}. They exist so that the many
 * pre-existing read paths, reports and exports that read `quantity` directly keep
 * working without modification. Never write them by hand.
 */
class SupplierRawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category',
        'type',
        'name',
        'sku_code',
        'size',
        'weight',
        'color_code',
        'price',
        'min_order',
        'max_order',
        'description',
        'image_url',
        'is_active',

        // batch rollups + archiving
        'quantity',
        'reserved_quantity',
        'minimum_stock_level',
        'expiration_date',
        'is_archived',
        'archived_at',
        'archive_reason',
        'archived_by',
    ];

    protected $casts = [
        'quantity'            => 'integer',
        'reserved_quantity'   => 'integer',
        'minimum_stock_level' => 'integer',
        'weight'              => 'decimal:2',
        'price'               => 'decimal:2',
        'min_order'           => 'integer',
        'max_order'           => 'integer',
        'is_active'           => 'boolean',
        'is_archived'         => 'boolean',
        'expiration_date' => CalendarDate::class,
        'archived_at'         => 'datetime',
    ];

    protected $appends = [
        'available_quantity',
        'is_expired',
        'health',
        'days_until_expiry',
        'requires_expiration',
    ];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    /** The supplier account that owns this product. */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Every delivery lot ever added to this product. */
    public function batches(): HasMany
    {
        return $this->hasMany(SupplierRawMaterialBatch::class, 'supplier_raw_material_id');
    }

    /** Only the batches that are still in play. */
    public function liveBatches(): HasMany
    {
        return $this->hasMany(SupplierRawMaterialBatch::class, 'supplier_raw_material_id')
            ->where('is_archived', false);
    }

    /** Only the batches a distributor is still allowed to buy. */
    public function sellableBatches(): HasMany
    {
        return $this->hasMany(SupplierRawMaterialBatch::class, 'supplier_raw_material_id')
            ->sellable();
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    /** Products a distributor may still procure. */
    public function scopeProcurable($query)
    {
        return $query->where('is_active', true)
            ->where('is_archived', false)
            ->whereHas('sellableBatches');
    }

    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('user_id', $supplierId);
    }

    // -----------------------------------------------------------------
    // Computed attributes
    // -----------------------------------------------------------------

    /**
     * Units a distributor may still buy right now.
     *
     * Deliberately NOT `quantity - reserved_quantity`. Those two rollup columns
     * count every live lot, including lots whose expiration date has already
     * passed, so the difference overstates what is procurable. It is the figure
     * the catalogue screen shows next to the product name, and a supplier
     * promising 35 units when only 30 can actually be ordered is a stockout
     * waiting to happen at the checkout step.
     *
     * The same definition as
     * {@see \App\Support\Inventory\BatchInventoryService::supplierAvailableQuantity()},
     * which is what the procurement endpoint actually enforces — so the number on
     * screen is the number the server will honour.
     *
     * Computed from the eager-loaded `live_batches` when present (the index
     * endpoint loads them), and only falling back to a query for a single model
     * that was fetched on its own, so listing a catalogue stays at one query
     * rather than one per product.
     */
    public function getAvailableQuantityAttribute(): int
    {
        if ($this->relationLoaded('liveBatches')) {
            return (int) $this->liveBatches->sum(
                fn ($batch) => $batch->is_archived || $batch->is_expired
                    ? 0
                    : max(0, (int) $batch->quantity - (int) $batch->reserved_quantity)
            );
        }

        return max(0, (int) $this->sellableBatches()->sum(
            DB::raw('quantity - reserved_quantity')
        ));
    }

    public function getIsExpiredAttribute(): bool
    {
        // A product is only "expired" when it has no live batch left with usable
        // stock. A single healthy batch makes the product procurable again, which
        // is why this deliberately does not just look at the earliest date.
        if ($this->is_archived) {
            return true;
        }

        return ! $this->sellableBatches()->exists();
    }

    public function getHealthAttribute(): string
    {
        return BatchRules::health($this->expiration_date);
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return BatchRules::daysUntilExpiry($this->expiration_date);
    }

    public function getRequiresExpirationAttribute(): bool
    {
        return BatchRules::requiresExpiration($this->category);
    }

    // -----------------------------------------------------------------
    // Behaviour
    // -----------------------------------------------------------------

    /**
     * Re-derive the cached rollups from the live batches.
     *
     * Called by the service layer after every quantity change. Cheap enough to
     * call liberally; doing it in one place beats trusting every caller to keep
     * three columns in sync.
     */
    public function syncBatchRollups(): void
    {
        $rollup = $this->batches()
            ->where('is_archived', false)
            ->selectRaw('COALESCE(SUM(quantity), 0) AS qty')
            ->selectRaw('COALESCE(SUM(reserved_quantity), 0) AS reserved')
            ->selectRaw('MIN(expiration_date) AS earliest')
            ->first();

        // `earliest` comes straight out of MIN(expiration_date), so it is already
        // a `Y-m-d` string; the CalendarDate cast normalises it on the way in.
        $this->forceFill([
            'quantity'          => (int) ($rollup->qty ?? 0),
            'reserved_quantity' => (int) ($rollup->reserved ?? 0),
            'expiration_date'   => ! empty($rollup->earliest) ? (string) $rollup->earliest : null,
        ])->saveQuietly();
    }
}
