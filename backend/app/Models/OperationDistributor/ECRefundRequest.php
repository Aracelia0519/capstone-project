<?php

namespace App\Models\OperationDistributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EcommerceClient\ClientReturnRequest;
use App\Models\User;

class ECRefundRequest extends Model
{
    use HasFactory;

    protected $table = 'ec_refund_requests';

    protected $fillable = [
        'return_request_id',
        'distributor_id',
        'requested_by',
        'amount',
        'receipt_proof_path',
        'client_gcash_number',
        'status',
        'approved_by'
    ];

    public function returnRequest()
    {
        return $this->belongsTo(ClientReturnRequest::class, 'return_request_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}