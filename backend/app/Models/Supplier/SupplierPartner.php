<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\DistributorAddress;

class SupplierPartner extends Model
{
    use HasFactory;

    protected $table = 'supplier_partners';

    protected $fillable = [
        'supplier_id',
        'distributor_id',
        'request_id',
        'status',
        'partnership_start_date',
        'partnership_end_date',
        'total_sales',
        'total_orders'
    ];

    protected $casts = [
        'partnership_start_date' => 'date',
        'partnership_end_date' => 'date',
    ];

    /**
     * Get the distributor user account.
     */
    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    /**
     * Get the supplier user account.
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get the distributor's business details via the user ID relation.
     */
    public function distributorRequirement()
    {
        return $this->hasOne(DistributorRequirements::class, 'user_id', 'distributor_id');
    }
}