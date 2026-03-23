<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\OperationDistributor\ProcurementReturn;
use App\Models\Supplier\SupplierDelivery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SupplierShipmentController extends Controller
{
    /**
     * Helper method to resolve the correct supplier ID based on the user's role.
     */
    private function resolveSupplierId($user)
    {
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

        return $supplierId;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $supplierId = $this->resolveSupplierId($user);

        // Fetch Delivery Personnel
        $deliveryPersonnel = DB::table('supplier_personnels')
            ->where('supplier_id', $supplierId)
            ->where('personnel_type', 'Delivery Personnel')
            ->where('status', 'active')
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->first_name . ' ' . $person->last_name
                ];
            });

        // 1. FETCH PREPARED ORDERS
        $preparedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->where('status', 'prepared')
            ->get()
            ->map(function ($order) {
                return [
                    'unique_id' => 'order_' . $order->id,
                    'id' => $order->id,
                    'type' => 'order',
                    'display_id' => $order->request_code, 
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'weight' => $order->quantity . ' Units', 
                    'status' => ucfirst($order->status),
                    'delivery_address' => $order->delivery_address,
                    'rejection_reason' => $order->rejection_reason, // ADDED REJECTION REASON FETCHING
                    'proofImage' => null,
                    'updated_at' => $order->updated_at
                ];
            });

        // 1b. FETCH PREPARED RETURNS
        $preparedReturns = ProcurementReturn::with(['procurementRequest', 'distributor'])
            ->where('supplier_id', $supplierId)
            ->where('status', 'prepared')
            ->get()
            ->map(function ($ret) {
                return [
                    'unique_id' => 'return_' . $ret->id,
                    'id' => $ret->id,
                    'type' => 'return',
                    'display_id' => $ret->procurementRequest ? $ret->procurementRequest->request_code . '-REP' : 'REP-' . $ret->id, 
                    'customer' => $ret->distributor ? $ret->distributor->full_name : 'Unknown Distributor',
                    'items' => $ret->quantity_returned . 'x ' . ($ret->procurementRequest ? $ret->procurementRequest->product_name : 'Unknown Product'),
                    'weight' => $ret->quantity_returned . ' Units', 
                    'status' => 'Replacement Prepared',
                    'delivery_address' => $ret->procurementRequest ? $ret->procurementRequest->delivery_address : null,
                    'rejection_reason' => $ret->rejection_reason ?? ($ret->procurementRequest ? $ret->procurementRequest->rejection_reason : null), // ADDED REJECTION REASON FETCHING
                    'proofImage' => null,
                    'updated_at' => $ret->updated_at
                ];
            });

        // Combine and Sort Prepared
        $allPrepared = $preparedOrders->concat($preparedReturns)->sortByDesc('updated_at')->values();

        // 2. FETCH SHIPPED ORDERS
        $shippedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['shipped', 'in_transit', 'delivered'])
            ->take(20) 
            ->get()
            ->map(function ($order) {
                return [
                    'unique_id' => 'order_' . $order->id,
                    'id' => $order->id,
                    'type' => 'order',
                    'display_id' => $order->request_code,
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'status' => ucwords(str_replace('_', ' ', $order->status)), 
                    'shipped_at' => $order->shipped_at ? date('M d, Y', strtotime($order->shipped_at)) : 'N/A',
                    'updated_at' => $order->updated_at
                ];
            });

        // 2b. FETCH SHIPPED RETURNS
        $shippedReturns = ProcurementReturn::with(['procurementRequest', 'distributor'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['shipped', 'in_transit', 'delivered'])
            ->take(20) 
            ->get()
            ->map(function ($ret) {
                return [
                    'unique_id' => 'return_' . $ret->id,
                    'id' => $ret->id,
                    'type' => 'return',
                    'display_id' => $ret->procurementRequest ? $ret->procurementRequest->request_code . '-REP' : 'REP-' . $ret->id,
                    'customer' => $ret->distributor ? $ret->distributor->full_name : 'Unknown Distributor',
                    'items' => $ret->quantity_returned . 'x ' . ($ret->procurementRequest ? $ret->procurementRequest->product_name : 'Unknown Product'),
                    'status' => ucwords(str_replace('_', ' ', $ret->status)), 
                    'shipped_at' => $ret->updated_at ? date('M d, Y', strtotime($ret->updated_at)) : 'N/A',
                    'updated_at' => $ret->updated_at
                ];
            });

        // Combine and Sort Shipped
        $allShipped = $shippedOrders->concat($shippedReturns)->sortByDesc('updated_at')->take(20)->values();

        return response()->json([
            'prepared_orders' => $allPrepared,
            'shipped_orders' => $allShipped,
            'delivery_personnel' => $deliveryPersonnel
        ]);
    }

    public function ship(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'delivery_personnel_id' => 'required|exists:supplier_personnels,id',
            'type' => 'required|string|in:order,return'
        ]);

        $user = $request->user();
        $supplierId = $this->resolveSupplierId($user);
        $type = $request->input('type');

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'shipping_proof_' . $type . '_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('supplier/shipping_proofs', $filename, 'public');
                $imagePath = 'storage/' . $path;
            }

            if ($type === 'order') {
                $procurementRequest = ProcurementRequest::where('id', $id)
                    ->where('supplier_id', $supplierId)
                    ->firstOrFail();

                if ($procurementRequest->status !== 'prepared') {
                    return response()->json(['message' => 'Order is not in prepared status.'], 400);
                }

                SupplierDelivery::create([
                    'procurement_request_id' => $procurementRequest->id,
                    'delivery_personnel_id' => $request->delivery_personnel_id,
                    'shipping_proof_path' => $imagePath,
                    'status' => 'assigned',
                    'assigned_at' => now(),
                ]);

                $procurementRequest->status = 'in_transit';
                $procurementRequest->shipped_at = now();
                $procurementRequest->rejection_reason = null; // CLEAR REJECTION REASON UPON SUCCESSFUL REASSIGNMENT
                $procurementRequest->save();

            } elseif ($type === 'return') {
                $returnReq = ProcurementReturn::where('id', $id)
                    ->where('supplier_id', $supplierId)
                    ->firstOrFail();

                if ($returnReq->status !== 'prepared') {
                    return response()->json(['message' => 'Return is not in prepared status.'], 400);
                }

                // Explicit DB insertion to bypass mass-assignment issues if 'notes' isn't fillable
                DB::table('supplier_deliveries')->insert([
                    'procurement_request_id' => $returnReq->procurement_request_id,
                    'delivery_personnel_id' => $request->delivery_personnel_id,
                    'shipping_proof_path' => $imagePath,
                    'status' => 'assigned',
                    'notes' => '[REPLACEMENT DELIVERY] For Return Request #' . $returnReq->id,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $returnReq->status = 'in_transit';
                $returnReq->rejection_reason = null; // CLEAR REJECTION REASON UPON SUCCESSFUL REASSIGNMENT
                $returnReq->save();

                // Clear from base request if it got appended there
                if ($returnReq->procurement_request_id) {
                    $parentReq = ProcurementRequest::find($returnReq->procurement_request_id);
                    if ($parentReq) {
                        $parentReq->rejection_reason = null;
                        $parentReq->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => ucfirst($type) . ' assigned to delivery personnel and is now in transit!',
                'image_url' => asset($imagePath)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process shipment: ' . $e->getMessage()], 500);
        }
    }
}