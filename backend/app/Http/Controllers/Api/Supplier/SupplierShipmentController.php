<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
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

        // Fetch Prepared Orders (Ready to be shipped out)
        $preparedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->where('status', 'prepared')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id, 
                    'display_id' => $order->request_code, 
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'weight' => $order->quantity . ' Units', 
                    'status' => ucfirst($order->status),
                    'delivery_address' => $order->delivery_address,
                    'proofImage' => null
                ];
            });

        // Fetch Shipped/In-Transit/Delivered History
        $shippedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['shipped', 'in_transit', 'delivered'])
            ->orderBy('updated_at', 'desc')
            ->take(20) 
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'display_id' => $order->request_code,
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    // Converts "in_transit" to "In Transit" visually
                    'status' => ucwords(str_replace('_', ' ', $order->status)), 
                    'shipped_at' => $order->shipped_at ? date('M d, Y', strtotime($order->shipped_at)) : 'N/A'
                ];
            });

        return response()->json([
            'prepared_orders' => $preparedOrders,
            'shipped_orders' => $shippedOrders,
            'delivery_personnel' => $deliveryPersonnel
        ]);
    }

    public function ship(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'delivery_personnel_id' => 'required|exists:supplier_personnels,id'
        ]);

        $user = $request->user();
        $supplierId = $this->resolveSupplierId($user);

        $procurementRequest = ProcurementRequest::where('id', $id)
            ->where('supplier_id', $supplierId)
            ->firstOrFail();

        if ($procurementRequest->status !== 'prepared') {
            return response()->json(['message' => 'Order is not in prepared status.'], 400);
        }

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'shipping_proof_' . $procurementRequest->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('supplier/shipping_proofs', $filename, 'public');
                $imagePath = 'storage/' . $path;
            }

            // Create Supplier Delivery Record
            SupplierDelivery::create([
                'procurement_request_id' => $procurementRequest->id,
                'delivery_personnel_id' => $request->delivery_personnel_id,
                'shipping_proof_path' => $imagePath,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            // Update Procurement Request Status to 'in_transit' instead of 'shipped'
            $procurementRequest->status = 'in_transit';
            $procurementRequest->shipped_at = now();
            $procurementRequest->save();

            DB::commit();

            return response()->json([
                'message' => 'Order assigned to delivery personnel and is now in transit!',
                'image_url' => asset($imagePath)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process shipment: ' . $e->getMessage()], 500);
        }
    }
}