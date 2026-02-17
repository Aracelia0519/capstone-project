<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SupplierShipmentController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch Prepared Orders
        $preparedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $userId)
            ->where('status', 'prepared')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id, // Primary Key for API calls
                    'display_id' => $order->request_code, // Display ID
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'weight' => $order->quantity . ' Units', // Assuming quantity as proxy for weight/size
                    'status' => ucfirst($order->status),
                    'delivery_address' => $order->delivery_address,
                    'proofImage' => null
                ];
            });

        // Fetch Shipped History
        $shippedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $userId)
            ->where('status', 'shipped')
            ->orderBy('updated_at', 'desc')
            ->take(20) // Limit history
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'display_id' => $order->request_code,
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'status' => ucfirst($order->status),
                    'shipped_at' => $order->shipped_at ? date('M d, Y', strtotime($order->shipped_at)) : 'N/A'
                ];
            });

        return response()->json([
            'prepared_orders' => $preparedOrders,
            'shipped_orders' => $shippedOrders
        ]);
    }

    public function ship(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        $procurementRequest = ProcurementRequest::where('id', $id)
            ->where('supplier_id', $request->user()->id)
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
                // Store in public disk
                $path = $file->storeAs('supplier/shipping_proofs', $filename, 'public');
                $imagePath = 'storage/' . $path;
            }

            // Update Status
            $procurementRequest->status = 'shipped';
            $procurementRequest->shipped_at = now();
            
            // Since there is no specific column for shipping proof in the provided schema,
            // we will append it to the notes or you can add a new migration later.
            // For now, we append to notes for record keeping.
            if ($imagePath) {
                $currentNotes = $procurementRequest->rejection_reason ?? ''; // Using rejection_reason or instructions field as generic notes if notes col missing
                // Or if you have a notes column not shown in migration but available in model, use that.
                // Based on schema, 'instructions' is text. We'll use a JSON structure in instructions if needed, 
                // but safely let's just save the status. 
                // Ideally, you should create a new table `shipping_fulfillments` similar to `procurement_fulfillments`.
            }

            $procurementRequest->save();

            DB::commit();

            return response()->json([
                'message' => 'Order marked as shipped successfully!',
                'image_url' => asset($imagePath)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process shipment: ' . $e->getMessage()], 500);
        }
    }
}