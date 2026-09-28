<?php

namespace App\Models\OperationDistributor;

use App\Casts\CalendarDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcurementRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'procurement_requests';

    protected $fillable = [
        'requester_id',
        'distributor_id',
        'supplier_id', 
        'product_id',
        'request_code',
        'product_name',
        'category',
        'supplier', 
        'quantity',
        'expiration_date',
        'unit_price',
        'total_cost',
        'priority',
        'status',
        'shipping_method',
        'payment_terms',
        'delivery_address',
        'instructions',
        'required_by_date',
        'request_date',
        'rejection_reason',
        'approved_at',
        'processed_at',
        'shipped_at',
        'delivered_at',
        // Reservation audit trail. These MUST be fillable, not merely castable:
        // they are written through `create()` when a request is raised, and mass
        // assignment silently discards anything missing from $fillable — so
        // without them the request would reserve stock while recording no trace
        // that it ever had.
        'stock_reserved_at',
        'stock_released_at',
        'stock_consumed_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        // The expiry of the supplier batch this request reserved. Carrying it on the
        // request is what lets the delivered batch be identified unambiguously even
        // if the supplier restocks the product in the meantime.
        'expiration_date' => CalendarDate::class,
        'stock_reserved_at' => 'datetime',
        'stock_released_at' => 'datetime',
        'stock_consumed_at' => 'datetime',
        'required_by_date' => 'date',
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    protected $attributes = [
        'priority' => 'medium',
        'status' => 'pending',
        'shipping_method' => 'standard',
        'payment_terms' => 'net30'
    ];

    // Automatically append this custom attribute to JSON responses
    protected $appends = ['raw_material_details', 'formatted_status', 'formatted_priority', 'total_cost_formatted'];

    /**
     * DYNAMICALLY FETCH THE SUPPLIER RAW MATERIAL DETAILS
     * Since product_id is null during the pending request phase, this fetches 
     * the image and exact specs directly from the supplier's active catalog.
     */
    public function getRawMaterialDetailsAttribute()
    {
        if ($this->supplier_id && $this->product_name) {
            return \App\Models\Supplier\SupplierRawMaterial::where('user_id', $this->supplier_id)
                ->where('name', $this->product_name)
                ->first();
        }
        return null;
    }

    /**
     * Get the requester user
     */
    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'requester_id');
    }

    /**
     * Get the distributor user
     */
    public function distributor()
    {
        return $this->belongsTo(\App\Models\User::class, 'distributor_id');
    }

    /**
     * Get the supplier user (The actual account associated with the ID)
     */
    public function selectedSupplier()
    {
        return $this->belongsTo(\App\Models\User::class, 'supplier_id');
    }

    /**
     * The supplier's material this request is for.
     *
     * NOTE: `product_id` was re-pointed to `supplier_raw_materials.id` by migration
     * 2026_07_07_021004. The old `product()` relation still claimed
     * `distributor_products` and therefore always resolved to null.
     *
     * @see rawMaterial() for the correctly-named accessor.
     */
    public function rawMaterial()
    {
        return $this->belongsTo(\App\Models\Supplier\SupplierRawMaterial::class, 'product_id');
    }

    /**
     * Kept for backwards compatibility with callers written against the old
     * (incorrect) relation. New code should use rawMaterial().
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Distributor\Product::class, 'product_id');
    }

    /**
     * Batches this distributor received because of this request.
     */
    public function receivedBatches()
    {
        return $this->hasMany(
            \App\Models\OperationDistributor\DistributorInventoryBatch::class,
            'source_procurement_request_id'
        );
    }

    /**
     * Generate a unique request code
     */
    public static function generateRequestCode()
    {
        $prefix = 'REQ-';
        $date = date('Ymd');
        $latest = self::where('request_code', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $number = intval(substr($latest->request_code, -4)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . $date . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope queries
     */
    public function scopeForRequester($query, $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    public function scopeForDistributor($query, $distributorId)
    {
        return $query->where('distributor_id', $distributorId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Format Attributes
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }

    public function getFormattedPriorityAttribute()
    {
        return ucfirst($this->priority);
    }

    public function getTotalCostFormattedAttribute()
    {
        return '₱' . number_format($this->total_cost, 2);
    }

    /**
     * Update request status
     *
     * This is the only place a procurement request changes state -- the
     * operational, distributor, finance and ready controllers all funnel through
     * here -- so it is also where a reservation has to be given back.
     *
     * Raising a request reserves the supplier's stock. If the request is then
     * rejected or cancelled and the units are never handed back, that stock stays
     * permanently invisible: the supplier's available figure is net of reserved
     * units, so a handful of dead requests quietly stop every distributor from
     * buying the same product.
     */
    public function updateStatus($status, $reason = null)
    {
        $this->status = $status;
        
        if ($reason) {
            $this->rejection_reason = $reason;
        }
        
        switch ($status) {
            case 'approved':
                $this->approved_at = now();
                break;
            case 'processing':
            case 'op-approved': 
            case 'd-approved': 
                $this->processed_at = now();
                break;
            case 'ready': 
                $this->processed_at = now();
                break;
            case 'shipped':
                $this->shipped_at = now();
                break;
            case 'delivered':
                $this->delivered_at = now();
                break;
        }
        
        $this->save();

        // After the save, deliberately: if the status write fails the reservation
        // is still held and the request is still live, which is recoverable. The
        // reverse -- stock returned against a request that is still pending --
        // would hand the same units to two buyers.
        if (in_array($status, ['rejected', 'cancelled'], true) && $this->holdsLiveReservation()) {
            app(\App\Support\Inventory\BatchInventoryService::class)->releaseReservation($this);
        }
    }

    /**
     * True while this request still has stock set aside for it.
     *
     * Guards against releasing twice, and against releasing stock that has
     * already been consumed by the delivery.
     */
    public function holdsLiveReservation(): bool
    {
        return (bool) $this->product_id
            && $this->stock_reserved_at !== null
            && $this->stock_consumed_at === null
            && $this->stock_released_at === null;
    }

    public function financeApprovedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'finance_approved_by');
    }

    public function financeRejectedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'finance_rejected_by');
    }
}