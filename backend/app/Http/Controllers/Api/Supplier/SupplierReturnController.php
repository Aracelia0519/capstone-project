<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementReturn;
use App\Models\Supplier\SupplierRequirements;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    /**
     * Get all pending and processing returns for the supplier
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $supplierId = $user->id;

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) $supplierId = $personnel->supplier_id;
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) $supplierId = $officer->supplier_id;
        }

        // Fetch supplier business details for receipt generation
        $supplierReq = SupplierRequirements::with('address')->where('user_id', $supplierId)->first();
        $supplierData = [
            'company_name' => $supplierReq->company_name ?? 'Unknown Company',
            'business_registration_number' => $supplierReq->business_registration_number ?? '000-000-000-000',
            'address' => 'Address not provided'
        ];

        if ($supplierReq && $supplierReq->address) {
            $addr = $supplierReq->address;
            $addressParts = array_filter([$addr->block_address, $addr->barangay, $addr->city, $addr->province]);
            $supplierData['address'] = implode(', ', $addressParts);
        }

        // Only fetch pending or processing. Once prepared, it moves to the logistics module.
        $returns = ProcurementReturn::with(['procurementRequest', 'distributor', 'distributor.distributorRequirement'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['pending', 'processing']) 
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedReturns = $returns->map(function ($ret) {
            $distributorAddress = 'Address not available';
            if ($ret->procurementRequest && $ret->procurementRequest->delivery_address) {
                $distributorAddress = $ret->procurementRequest->delivery_address;
            } elseif ($ret->distributor && $ret->distributor->address) {
                $distributorAddress = $ret->distributor->address;
            }

            return [
                'id' => $ret->id,
                'procurement_request_id' => $ret->procurement_request_id,
                'request_code' => $ret->procurementRequest ? $ret->procurementRequest->request_code : 'N/A',
                'product_name' => $ret->procurementRequest ? $ret->procurementRequest->product_name : 'Unknown Product',
                'category' => $ret->procurementRequest ? $ret->procurementRequest->category : 'Unknown',
                'unit_price' => $ret->procurementRequest ? (float)$ret->procurementRequest->unit_price : 0,
                'distributor_name' => $ret->distributor ? $ret->distributor->full_name : 'Unknown',
                'distributor_address' => $distributorAddress,
                'quantity_returned' => $ret->quantity_returned,
                'reason' => $ret->reason,
                'proof_image_path' => $ret->proof_image_path,
                'replacement_receipt_path' => $ret->replacement_receipt_path,
                'replacement_proof_path' => $ret->replacement_proof_path,
                'status' => $ret->status,
                'created_at' => $ret->created_at->format('Y-m-d'), 
            ];
        });

        return response()->json([
            'success' => true,
            'returns' => $formattedReturns,
            'supplier' => $supplierData
        ]);
    }

    /**
     * Accept the return, mark it as prepared, and save replacement paths natively
     */
    public function accept(Request $request, $id)
    {
        $request->validate([
            'receipt_file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx,html,txt|max:5120',
            'proof_file' => 'required|file|mimes:jpeg,png,jpg|max:5120',
            'notes' => 'nullable|string'
        ]);

        $user = $request->user();
        $supplierId = $user->id;

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) $supplierId = $personnel->supplier_id;
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) $supplierId = $officer->supplier_id;
        }

        $returnReq = ProcurementReturn::where('id', $id)
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$returnReq) {
            return response()->json(['message' => 'Return request not found or already processed.'], 404);
        }

        DB::beginTransaction();

        try {
            $receiptPath = $request->file('receipt_file')->store('supplier/fulfillments/return_receipts', 'public');
            $proofPath = $request->file('proof_file')->store('supplier/fulfillments/return_proofs', 'public');

            // Save directly to the Procurement Return table using the new columns!
            $returnReq->replacement_receipt_path = $receiptPath;
            $returnReq->replacement_proof_path = $proofPath;
            $returnReq->status = 'prepared';
            $returnReq->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Return accepted and replacement prepared successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to prepare replacement. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject the return with a reason
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $user = $request->user();
        $supplierId = $user->id;

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) $supplierId = $personnel->supplier_id;
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) $supplierId = $officer->supplier_id;
        }

        $returnReq = ProcurementReturn::where('id', $id)
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$returnReq) {
            return response()->json(['message' => 'Return request not found or already processed.'], 404);
        }

        $returnReq->status = 'rejected';
        $returnReq->rejection_reason = $request->rejection_reason;
        $returnReq->save();

        return response()->json([
            'success' => true,
            'message' => 'Return request has been officially rejected.'
        ], 200);
    }
}