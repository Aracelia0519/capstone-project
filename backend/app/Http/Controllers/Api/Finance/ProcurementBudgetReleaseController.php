<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Finance\BudgetDeductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementBudgetReleaseController extends Controller
{
    /**
     * Fetch procurement requests that are 'd-approved' (Distributor Approved) or 'ready'
     */
    public function index(Request $request)
    {
        // Fetches requests for all users since the prompt requested "make sure all users logged in can manage"
        $requests = ProcurementRequest::with(['requester', 'distributor'])
            ->whereIn('status', ['d-approved', 'ready'])
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
                'distributor' => $req->distributor ? $req->distributor->full_name : 'Unknown',
                'date' => $req->request_date->format('Y-m-d'),
                'location' => $req->delivery_address ?? 'Main Office',
                'status' => $req->status,
                'priority' => ucfirst($req->priority),
                'totalAmount' => (float) $req->total_cost,
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
     * Approve and release budget (Marks status as 'ready' and logs deduction)
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $procurement = ProcurementRequest::findOrFail($id);

            if ($procurement->status !== 'd-approved') {
                return response()->json(['message' => 'Only Distributor Approved (d-approved) requests can have their budget released.'], 400);
            }

            // Update status to 'ready'
            $procurement->updateStatus('ready');

            // Log the budget deduction based on the total cost and distributor id
            BudgetDeductionLog::create([
                'distributor_id' => $procurement->distributor_id,
                'procurement_request_id' => $procurement->id,
                'amount' => $procurement->total_cost,
                'description' => 'Budget released and deducted for procurement request ' . ($procurement->request_code ?? $procurement->id)
            ]);
            
            DB::commit();

            return response()->json([
                'message' => 'Budget successfully released. Status is now Ready.',
                'data' => $procurement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to release budget: ' . $e->getMessage()], 500);
        }
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

        $procurement->updateStatus('rejected', $request->reason);

        return response()->json([
            'message' => 'Request rejected successfully',
            'data' => $procurement
        ]);
    }
}