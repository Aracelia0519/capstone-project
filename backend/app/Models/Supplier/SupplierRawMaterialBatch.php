<?php

namespace App\Models\Supplier;

use App\Casts\CalendarDate;
use App\Support\Inventory\BatchRules;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One physical delivery lot of a supplier's product.
 *
 * A product can have many batches. Each batch has its OWN quantity and its OWN
 * expiration date, which is what makes it possible to restock 40 cans today and
 * 60 cans in six months without losing track of which ones expire first.
 *
 * @property int         $id
 * @property int         $supplier_raw_material_id
 * @property int         $user_id
 * @property string      $batch_code
 * @property int         $quantity
 * @property int         $reserved_quantity
 * @property string|null $expiration_date  NULL = never expires
 * @property bool        $is_archived
 */
class SupplierRawMaterialBatch extends Model
{
    use HasFactory;

    protected $table = 'supplier_raw_material_batches';

    protected $fillable = [
        'supplier_raw_material_id',
        'user_id',
        'batch_code',
        'quantity',
        'reserved_quantity',
        'expiration_date',
        'received_at',
        'is_archived',
        'archived_at',
        'archive_reason',
        'archived_by',
    ];

    protected $casts = [
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'is_archived'       => 'boolean',
        'expiration_date' => CalendarDate::class,
        'received_at'       => 'datetime',
        'archived_at'       => 'datetime',
    ];

    protected $appends = ['available_quantity', 'is_expired', 'health', 'days_until_expiry'];

    // -----------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------

    public function material(): BelongsTo
    {
        return $this->belongsTo(SupplierRawMaterial::class, 'supplier_raw_material_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'archived_by');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    /** Batches that can still be sold to or procured by a distributor. */
    public function scopeLive($query)
    {
        return $query->where('is_archived', false);
    }

    /** Batches that are still within their usable life. */
    public function scopeSellable($query)
    {
        return $query->where('is_archived', false)
            ->whereRaw('quantity - reserved_quantity > 0')
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
     * Batches with no expiration date go last, not first. MySQL sorts NULL ahead of
     * every real value on ASC, so without the explicit `IS NULL` term a crate of
     * non-perishables would be consumed before the paint that actually has a date.
     */
    public function scopeFefo($query)
    {
        return $query->orderByRaw(BatchRules::fefoOrderBy());
    }

    // -----------------------------------------------------------------
    // Computed attributes
    // -----------------------------------------------------------------

    /** Units that are physically present and not already promised to a distributor. */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->reserved_quantity);
    }

    public function getIsExpiredAttribute(): bool
    {
        return BatchRules::isExpired($this->expiration_date);
    }

    public function getHealthAttribute(): string
    {
        return BatchRules::health($this->expiration_date);
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return BatchRules::daysUntilExpiry($this->expiration_date);
    }
}
