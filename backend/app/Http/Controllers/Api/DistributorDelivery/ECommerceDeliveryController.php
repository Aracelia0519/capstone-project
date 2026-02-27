<?php

namespace App\Http\Controllers\Api\DistributorDelivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ECOrderDelivery;
use App\Models\HR\Employee;

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

        $deliveries = ECOrderDelivery::with([
            'order.items.product',
            'order.client.clientRequirement.address'
        ])
        ->where('delivery_personnel_id', $employee->id)
        ->whereIn('status', ['assigned', 'in_transit'])
        ->get()
        ->map(function ($delivery) {
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
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $delivery = ECOrderDelivery::with('order.client.clientRequirement.address')->findOrFail($id);
        
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

        // Handle Proof Upload
        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'delivery_proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('ec_deliveries_proof', $filename, 'public');

            // Optionally, you can add a `delivery_proof_path` column to ec_order_deliveries to save this
            // $delivery->update(['delivery_proof_path' => 'storage/' . $path]);
        }

        // Update statuses to delivered
        $delivery->update(['status' => 'delivered']);

        if ($delivery->order) {
            $delivery->order->update(['status' => 'delivered']);
        }

        return response()->json(['message' => 'Order delivered successfully!']);
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