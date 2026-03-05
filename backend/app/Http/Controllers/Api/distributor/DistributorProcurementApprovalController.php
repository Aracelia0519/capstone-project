<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DistributorProcurementApprovalController extends Controller
{
    /**
     * Fetch all procurement requests for the logged-in distributor with status 'op-approved'
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure only distributors can access this
        if ($user->role !== 'distributor') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Fetch requests for this distributor that are 'op-approved' (and optionally 'd-approved' if you want them to see history)
        $requests = ProcurementRequest::with(['requester', 'product'])
            ->where('distributor_id', $user->id)
            ->whereIn('status', ['op-approved', 'd-approved']) // Including d-approved so they can see recently approved ones
            ->orderBy('updated_at', 'desc')
            ->get();

        $formatted = $requests->map(function ($req) {
            $department = 'General';
            if ($req->requester && method_exists($req->requester, 'employee') && $req->requester->employee) {
                $department = $req->requester->employee->department;
            }

            return [
                'id' => $req->request_code ?? 'REQ-' . $req->id,
                'db_id' => $req->id,
                'department' => $department, 
                'requester' => $req->requester ? $req->requester->full_name : 'Unknown',
                'date' => $req->request_date->format('Y-m-d'),
                'location' => $req->delivery_address ?? 'Main Office',
                'status' => $req->status,
                'priority' => ucfirst($req->priority),
                'totalAmount' => (float) $req->total_cost,
                'courier' => $req->shipping_method,
                'items' => [
                    [
                        'name' => $req->product_name,
                        'quantity' => $req->quantity,
                        'unitPrice' => (float) $req->unit_price
                    ]
                ]
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Mark a request as Distributor Approved (d-approved)
     */
    public function approve(Request $request, $id)
    {
        $procurement = ProcurementRequest::findOrFail($id);

        if ($procurement->distributor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to modify this request.'], 403);
        }

        if ($procurement->status !== 'op-approved') {
            return response()->json(['message' => 'Only Op. Approved requests can be approved by the Distributor.'], 400);
        }

        $procurement->updateStatus('d-approved');
        
        return response()->json([
            'message' => 'Request successfully approved by Distributor',
            'data' => $procurement
        ]);
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $procurement = ProcurementRequest::findOrFail($id);

        if ($procurement->distributor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to modify this request.'], 403);
        }

        $procurement->updateStatus('rejected', $request->reason);

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}