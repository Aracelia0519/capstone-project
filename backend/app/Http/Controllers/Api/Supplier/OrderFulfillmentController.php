<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Support\Facades\DB;

class OrderFulfillmentController extends Controller
{
    /**
     * List all incoming orders for the logged-in supplier using supplier_id.
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

        // Fetch requests where 'supplier_id' column matches the authenticated user's ID
        // And status is 'ready' (for supplier to process)
        $orders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['ready'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Append distributor details for the UI
                return [
                    'id' => $order->id,
                    'request_code' => $order->request_code,
                    'status' => $order->status,
                    'order_date' => $order->created_at->format('Y-m-d'),
                    'required_date' => $order->required_by_date ? $order->required_by_date->format('Y-m-d') : 'N/A',
                    'total_amount' => $order->total_cost,
                    'notes' => $order->instructions ?? 'No additional instructions.',
                    'distributor_name' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'distributor_contact' => $order->distributor ? $order->distributor->email : 'N/A',
                    'payment_terms' => $order->payment_terms,
                    'shipping_method' => $order->shipping_method,
                    'items' => [
                        [
                            'id' => $order->product_id,
                            'name' => $order->product_name,
                            'category' => $order->category,
                            'quantity' => $order->quantity,
                            'unit_price' => $order->unit_price,
                            'total' => $order->total_cost
                        ]
                    ]
                ];
            });

        return response()->json($orders);
    }

    /**
     * Confirm an order (Change status to processing).
     */
    public function confirmOrder(Request $request, $id)
    {
        $order = ProcurementRequest::findOrFail($id);

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

        // Validate that this order actually belongs to this supplier
        if ($order->supplier_id !== $supplierId) {
            return response()->json(['message' => 'Unauthorized access to this order.'], 403);
        }

        if ($order->status !== 'ready') {
            return response()->json(['message' => 'Order cannot be confirmed. Current status: ' . $order->status], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = 'processing';
            $order->save();
            DB::commit();
            return response()->json(['message' => 'Order confirmed successfully', 'status' => 'processing']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to confirm order'], 500);
        }
    }
}