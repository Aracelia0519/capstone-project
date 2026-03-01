<?php

namespace App\Http\Controllers\Api\DistributorDelivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ECOrderDelivery;
use App\Models\DistributorDelivery\ECDeliveryRemittance;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ECommerceDeliveryController extends Controller
{
    // Fetch all deliveries assigned to the logged-in personnel
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Find the employee record linked to this user account
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found for this user.'], 404);
        }

        // Fetch Distributor's Location for the Remittance Step
        $distLat = null;
        $distLng = null;
        $distReq = DB::table('distributor_requirements')->where('user_id', $employee->parent_distributor_id)->first();
        if ($distReq) {
            $distAddr = DB::table('distributor_addresses')->where('distributor_requirements_id', $distReq->id)->first();
            if ($distAddr) {
                $distLat = $distAddr->latitude;
                $distLng = $distAddr->longitude;
            }
        }

        $deliveries = ECOrderDelivery::with([
            'order.items.product',
            'order.client.clientRequirement.address'
        ])
        ->where('delivery_personnel_id', $employee->id)
        ->whereIn('status', ['assigned', 'in_transit', 'remitting']) // ADDED remitting
        ->get()
        ->map(function ($delivery) use ($distLat, $distLng) {
            $lat = null;
            $lng = null;
            $fullAddress = $delivery->order->delivery_address ?? 'No Address Provided';

            // Attempt to get exact coordinates from the client's verified address
            if ($delivery->order && $delivery->order->client && $delivery->order->client->clientRequirement && $delivery->order->client->clientRequirement->address) {
                $addressData = $delivery->order->client->clientRequirement->address;
                $lat = $addressData->latitude;
                $lng = $addressData->longitude;
            }

            return [
                'id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'order_number' => $delivery->order->order_number ?? 'N/A',
                'status' => $delivery->status,
                'client_name' => $delivery->order->client->full_name ?? 'Unknown',
                'client_phone' => $delivery->order->client->phone ?? 'N/A',
                'delivery_address' => $fullAddress,
                'target_lat' => $lat,
                'target_lng' => $lng,
                'distributor_lat' => $distLat,
                'distributor_lng' => $distLng,
                'total_amount' => $delivery->order->grand_total ?? 0,
                'payment_method' => $delivery->order->payment_method ?? 'N/A',
                'items' => $delivery->order->items->map(function ($item) {
                    return [
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'quantity' => $item->quantity,
                    ];
                })
            ];
        });

        return response()->json($deliveries);
    }

    // Start the delivery trip
    public function startDelivery(Request $request, $id)
    {
        $delivery = ECOrderDelivery::findOrFail($id);
        
        $delivery->update(['status' => 'in_transit']);
        
        if ($delivery->order) {
            $delivery->order->update(['status' => 'shipped']);
        }

        return response()->json(['message' => 'Delivery started. Status updated to in transit.']);
    }

    // Process arrival and complete the delivery
    public function arrive(Request $request, $id)
    {
        $delivery = ECOrderDelivery::with('order.client.clientRequirement.address')->findOrFail($id);
        $isCOD = ($delivery->order && strtolower($delivery->order->payment_method) === 'cod');

        $rules = [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ];

        if ($isCOD) {
            $rules['payment_file'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
        }

        $request->validate($rules);

        // Target Coordinates Validation
        $targetLat = null;
        $targetLng = null;
        if ($delivery->order && $delivery->order->client && $delivery->order->client->clientRequirement && $delivery->order->client->clientRequirement->address) {
            $targetLat = $delivery->order->client->clientRequirement->address->latitude;
            $targetLng = $delivery->order->client->clientRequirement->address->longitude;
        }

        // Distance validation (Must be within 500 meters / 0.5km)
        if ($targetLat && $targetLng) {
            $distance = $this->calculateDistance($request->latitude, $request->longitude, $targetLat, $targetLng);
            if ($distance > 0.5) { 
                return response()->json(['message' => 'You are too far from the delivery address to complete this order. Distance: ' . round($distance * 1000) . ' meters'], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Handle Package Proof Upload
            if ($request->hasFile('proof_file')) {
                $file = $request->file('proof_file');
                $filename = 'delivery_proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('ec_deliveries_proof', $filename, 'public');
                $delivery->delivery_proof_path = 'storage/' . $path;
            }

            // Handle Payment Proof Upload if COD
            if ($isCOD && $request->hasFile('payment_file')) {
                $file = $request->file('payment_file');
                $filename = 'payment_rcvd_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('ec_deliveries_payment', $filename, 'public');
                $delivery->payment_received_proof_path = 'storage/' . $path;
            }

            // Update statuses
            $delivery->status = $isCOD ? 'remitting' : 'delivered';
            $delivery->save();

            if ($delivery->order) {
                // To the client, the order is delivered whether COD or not. 
                $delivery->order->update(['status' => 'delivered']);
            }

            DB::commit();
            return response()->json(['message' => 'Order delivered successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process arrival: ' . $e->getMessage()], 500);
        }
    }

    // Remit Funds to Distributor HQ (for COD orders)
    public function remit(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'remittance_file' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $delivery = ECOrderDelivery::with('order')->findOrFail($id);

        if ($delivery->status !== 'remitting') {
            return response()->json(['message' => 'Delivery is not in remitting status.'], 400);
        }

        // Validate proximity to Distributor HQ
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        $distReq = DB::table('distributor_requirements')->where('user_id', $employee->parent_distributor_id)->first();
        if ($distReq) {
            $distAddr = DB::table('distributor_addresses')->where('distributor_requirements_id', $distReq->id)->first();
            
            if ($distAddr && $distAddr->latitude && $distAddr->longitude) {
                // Use kilometer based calculation to match arrive method (0.5km = 500m)
                $distance = $this->calculateDistance($request->latitude, $request->longitude, $distAddr->latitude, $distAddr->longitude);
                if ($distance > 0.5) {
                    return response()->json(['message' => 'You are too far from the Distributor HQ to remit funds. Distance: ' . round($distance * 1000) . ' meters'], 400);
                }
            }
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('remittance_file')) {
                $file = $request->file('remittance_file');
                $filename = 'remittance_proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('ec_deliveries_remittance', $filename, 'public');
                $delivery->remittance_proof_path = 'storage/' . $path;
            }

            $delivery->status = 'delivered'; // Final state for EC Delivery tracking
            $delivery->save();

            // Insert into the new Remittance table for the Distributor's tracking
            ECDeliveryRemittance::create([
                'distributor_id' => $employee->parent_distributor_id,
                'delivery_personnel_id' => $employee->id,
                'order_id' => $delivery->order_id,
                'amount' => $delivery->order ? $delivery->order->grand_total : 0,
                'remittance_proof_path' => $delivery->remittance_proof_path,
            ]);

            DB::commit();
            return response()->json(['message' => 'Funds remitted and delivery cycle completed!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process remittance: ' . $e->getMessage()], 500);
        }
    }

    // Haversine formula for backend distance validation (returns kilometers)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}