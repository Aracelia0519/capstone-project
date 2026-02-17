<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Http\Request;

class ProcurementReadyController extends Controller
{
    /**
     * Fetch requests that are Approved, Ready, or further along.
     */
    public function index()
    {
        // Added 'ready' to the list of statuses to fetch
        $requests = ProcurementRequest::with(['requester', 'product'])
            ->whereIn('status', ['approved', 'ready', 'processing', 'shipped', 'delivered'])
            ->orderBy('approved_at', 'desc')
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
                'status' => ucfirst($req->status), // Will output 'Ready', 'Approved', etc.
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
     * Mark a request as Ready (Prepared)
     */
    public function markAsReady(Request $request, $id)
    {
        $procurement = ProcurementRequest::findOrFail($id);

        if ($procurement->status !== 'approved') {
            return response()->json(['message' => 'Only approved requests can be prepared.'], 400);
        }

        // Update status to 'ready' explicitly
        // NOTE: Ensure your database ENUM includes 'ready'
        $procurement->updateStatus('ready');
        
        return response()->json([
            'message' => 'Order marked as Ready',
            'data' => $procurement
        ]);
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $procurement = ProcurementRequest::findOrFail($id);

        $procurement->updateStatus('rejected', $request->reason);

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}