<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Supplier\ProcurementFulfillment;
use App\Models\Supplier\SupplierRequirements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupplierOrderProcessingController extends Controller
{
    /**
     * Display a listing of the processing orders for the logged-in user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Determine the relevant supplier ID based on the logged-in user's role
        $supplierId = $user->id; // Default assumption for 'supplier' role

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) {
                $supplierId = $personnel->supplier_id;
            }
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) {
                $supplierId = $officer->supplier_id;
            }
        }

        // Fetch supplier business details for authentic receipt generation
        $supplierReq = SupplierRequirements::with('address')->where('user_id', $supplierId)->first();
        $supplierData = [
            'company_name' => $supplierReq->company_name ?? 'Unknown Company',
            'business_registration_number' => $supplierReq->business_registration_number ?? '000-000-000-000',
            'address' => 'Address not provided'
        ];

        if ($supplierReq && $supplierReq->address) {
            $addr = $supplierReq->address;
            // Format address neatly
            $addressParts = array_filter([$addr->block_address, $addr->barangay, $addr->city, $addr->province]);
            $supplierData['address'] = implode(', ', $addressParts);
        }

        // Fetch requests assigned to this supplier with status 'processing'
        $orders = ProcurementRequest::where('supplier_id', $supplierId)
            ->where('status', 'processing')
            ->with(['distributor', 'distributor.distributorRequirement', 'product'])
            ->orderBy('request_date', 'asc')
            ->get();

        // Transform data to match frontend interface
        $formattedOrders = $orders->map(function ($order) {
            
            // Construct address logic
            $distributorAddress = 'Address not available';
            if ($order->delivery_address) {
                $distributorAddress = $order->delivery_address;
            } elseif ($order->distributor && $order->distributor->address) {
                $distributorAddress = $order->distributor->address;
            }

            return [
                'id' => $order->id,
                'request_code' => $order->request_code,
                'status' => $order->status,
                'order_date' => $order->request_date->format('Y-m-d'),
                'distributor_name' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                'distributor_address' => $distributorAddress,
                'total_amount' => (float)$order->total_cost,
                // Wrap single product in array for frontend compatibility
                'items' => [
                    [
                        'id' => $order->product_id,
                        'name' => $order->product_name,
                        'quantity' => $order->quantity,
                        'unit_price' => (float)$order->unit_price,
                        'total' => (float)$order->total_cost
                    ]
                ]
            ];
        });

        // Return both the orders array and the supplier details object
        return response()->json([
            'orders' => $formattedOrders,
            'supplier' => $supplierData
        ]);
    }

    /**
     * Mark an order as prepared and upload documents.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'receipt_file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx,html,txt|max:5120',
            'proof_file' => 'required|file|mimes:jpeg,png,jpg|max:5120',
            'notes' => 'nullable|string'
        ]);

        $user = $request->user();

        // Determine the relevant supplier ID based on the logged-in user's role
        $supplierId = $user->id; // Default assumption for 'supplier' role

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) {
                $supplierId = $personnel->supplier_id;
            }
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) {
                $supplierId = $officer->supplier_id;
            }
        }

        // Find the order using the resolved parent supplier ID
        $order = ProcurementRequest::where('id', $id)
            ->where('supplier_id', $supplierId)
            ->where('status', 'processing')
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or not in processing status.'], 404);
        }

        DB::beginTransaction();

        try {
            // 1. Upload Files
            $receiptPath = $request->file('receipt_file')->store('supplier/fulfillments/receipts', 'public');
            $proofPath = $request->file('proof_file')->store('supplier/fulfillments/proofs', 'public');

            // 2. Create Fulfillment Record
            ProcurementFulfillment::create([
                'procurement_request_id' => $order->id,
                'receipt_file_path' => $receiptPath,
                'proof_file_path' => $proofPath,
                'notes' => $request->notes,
                'prepared_at' => now(),
            ]);

            // 3. Update Procurement Request Status using the existing model method
            $order->updateStatus('prepared');

            DB::commit();

            return response()->json([
                'message' => 'Order prepared successfully.',
                'order_id' => $order->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to prepare order. ' . $e->getMessage()], 500);
        }
    }
}