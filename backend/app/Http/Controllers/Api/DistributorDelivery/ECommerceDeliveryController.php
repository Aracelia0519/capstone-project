<?php

namespace App\Http\Controllers\Api\DistributorDelivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\ServiceProvider\SpOrder;
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

        $deliveries = ECOrderDelivery::where('delivery_personnel_id', $employee->id)
            ->whereIn('status', ['assigned', 'in_transit', 'remitting'])
            ->get()
            ->map(function ($delivery) use ($distLat, $distLng) {
                
                $lat = null;
                $lng = null;
                $clientName = 'Unknown';
                $clientPhone = 'N/A';
                $fullAddress = 'No Address Provided';
                $orderNumber = 'N/A';
                $totalAmount = 0;
                $paymentMethod = 'N/A';
                $items = collect();
                $orderType = 'client';

                // 1. Process Client Order
                if ($delivery->order_id) {
                    $order = ClientOrder::with(['client.clientRequirement.address', 'items.product'])->find($delivery->order_id);
                    if ($order) {
                        $orderType = 'client';
                        $orderNumber = $order->order_number;
                        $clientName = $order->client->full_name ?? 'Unknown Client';
                        $clientPhone = $order->client->phone ?? 'N/A';
                        $fullAddress = $order->delivery_address;
                        $totalAmount = $order->grand_total;
                        $paymentMethod = $order->payment_method;
                        
                        $items = $order->items->map(function ($item) {
                            return [
                                'name' => $item->product ? $item->product->name : 'Unknown Product',
                                'quantity' => $item->quantity,
                            ];
                        });

                        if ($order->client && $order->client->clientRequirement && $order->client->clientRequirement->address) {
                            $lat = $order->client->clientRequirement->address->latitude;
                            $lng = $order->client->clientRequirement->address->longitude;
                        }
                    }
                } 
                // 2. Process Service Provider Order
                elseif ($delivery->sp_order_id) {
                    $order = SpOrder::with(['items.product'])->find($delivery->sp_order_id);
                    if ($order) {
                        $orderType = 'sp';
                        $orderNumber = $order->order_number;
                        $fullAddress = $order->delivery_address;
                        $totalAmount = $order->grand_total;
                        $paymentMethod = $order->payment_method;

                        $spUser = DB::table('users')->where('id', $order->service_provider_id)->first();
                        $clientName = $spUser ? ($spUser->first_name . ' ' . $spUser->last_name) : 'Unknown Provider';
                        $clientPhone = $spUser->phone ?? 'N/A';

                        $items = $order->items->map(function ($item) {
                            return [
                                'name' => $item->product ? $item->product->name : 'Unknown Product',
                                'quantity' => $item->quantity,
                            ];
                        });

                        $spReq = DB::table('service_provider_requirements')->where('user_id', $order->service_provider_id)->first();
                        if ($spReq) {
                            $spAddr = DB::table('service_provider_addresses')->where('service_provider_requirements_id', $spReq->id)->first();
                            if ($spAddr) {
                                $lat = $spAddr->latitude;
                                $lng = $spAddr->longitude;
                            }
                        }
                    }
                }

                return [
                    'id' => $delivery->id,
                    'order_type' => $orderType,
                    'order_id' => $delivery->order_id,
                    'sp_order_id' => $delivery->sp_order_id,
                    'order_number' => $orderNumber,
                    'status' => $delivery->status,
                    'client_name' => $clientName,
                    'client_phone' => $clientPhone,
                    'delivery_address' => $fullAddress,
                    'target_lat' => $lat,
                    'target_lng' => $lng,
                    'distributor_lat' => $distLat,
                    'distributor_lng' => $distLng,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'items' => $items
                ];
            });

        return response()->json($deliveries);
    }

    // Start the delivery trip
    public function startDelivery(Request $request, $id)
    {
        $delivery = ECOrderDelivery::findOrFail($id);
        
        $delivery->update(['status' => 'in_transit']);
        
        if ($delivery->order_id) {
            DB::table('client_orders')->where('id', $delivery->order_id)->update(['status' => 'shipped']);
        } elseif ($delivery->sp_order_id) {
            DB::table('sp_orders')->where('id', $delivery->sp_order_id)->update(['status' => 'shipped']);
        }

        return response()->json(['message' => 'Delivery started. Status updated to in transit.']);
    }

    // Process arrival and complete the delivery
    public function arrive(Request $request, $id)
    {
        $delivery = ECOrderDelivery::findOrFail($id);
        
        $paymentMethod = 'N/A';
        $targetLat = null;
        $targetLng = null;

        if ($delivery->order_id) {
            $order = ClientOrder::with('client.clientRequirement.address')->find($delivery->order_id);
            if ($order) {
                $paymentMethod = $order->payment_method;
                if ($order->client && $order->client->clientRequirement && $order->client->clientRequirement->address) {
                    $targetLat = $order->client->clientRequirement->address->latitude;
                    $targetLng = $order->client->clientRequirement->address->longitude;
                }
            }
        } elseif ($delivery->sp_order_id) {
            $order = SpOrder::find($delivery->sp_order_id);
            if ($order) {
                $paymentMethod = $order->payment_method;
                $spReq = DB::table('service_provider_requirements')->where('user_id', $order->service_provider_id)->first();
                if ($spReq) {
                    $spAddr = DB::table('service_provider_addresses')->where('service_provider_requirements_id', $spReq->id)->first();
                    if ($spAddr) {
                        $targetLat = $spAddr->latitude;
                        $targetLng = $spAddr->longitude;
                    }
                }
            }
        }

        $isCOD = (strtolower($paymentMethod) === 'cod');

        $rules = [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ];

        if ($isCOD) {
            $rules['payment_file'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
        }

        $request->validate($rules);

        // Fetch bypass flag (default to false if not provided)
        $bypassLocation = filter_var($request->input('bypass_location', false), FILTER_VALIDATE_BOOLEAN);

        // Distance validation (Must be within 500 meters / 0.5km unless bypassed)
        if (!$bypassLocation && $targetLat && $targetLng) {
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

            if ($delivery->order_id) {
                DB::table('client_orders')->where('id', $delivery->order_id)->update(['status' => 'delivered']);
            } elseif ($delivery->sp_order_id) {
                DB::table('sp_orders')->where('id', $delivery->sp_order_id)->update(['status' => 'delivered']);
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

        $delivery = ECOrderDelivery::findOrFail($id);

        if ($delivery->status !== 'remitting') {
            return response()->json(['message' => 'Delivery is not in remitting status.'], 400);
        }

        $bypassLocation = filter_var($request->input('bypass_location', false), FILTER_VALIDATE_BOOLEAN);

        // Validate proximity to Distributor HQ
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        $distReq = DB::table('distributor_requirements')->where('user_id', $employee->parent_distributor_id)->first();
        if ($distReq) {
            $distAddr = DB::table('distributor_addresses')->where('distributor_requirements_id', $distReq->id)->first();
            
            // Distance validation (Must be within 500 meters unless bypassed)
            if (!$bypassLocation && $distAddr && $distAddr->latitude && $distAddr->longitude) {
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

            $distGrandTotal = 0;
            if ($delivery->order_id) {
                $order = DB::table('client_orders')->where('id', $delivery->order_id)->first();
                if ($order) $distGrandTotal = $order->grand_total;
            } elseif ($delivery->sp_order_id) {
                $order = DB::table('sp_orders')->where('id', $delivery->sp_order_id)->first();
                if ($order) $distGrandTotal = $order->grand_total;
            }

            $distributorId = $employee->parent_distributor_id;
            $distVatableSales = round($distGrandTotal / 1.12, 2);
            $distVatAmount = round($distGrandTotal - $distVatableSales, 2);

            // Insert using raw queries to easily handle conditional SP/Client insertion
            DB::table('ec_delivery_remittances')->insert([
                'distributor_id' => $distributorId,
                'delivery_personnel_id' => $employee->id,
                'order_id' => $delivery->order_id,
                'sp_order_id' => $delivery->sp_order_id,
                'amount' => $distGrandTotal,
                'remittance_proof_path' => $delivery->remittance_proof_path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ec_order_financials')->insert([
                'order_id' => $delivery->order_id,
                'sp_order_id' => $delivery->sp_order_id,
                'distributor_id' => $distributorId,
                'amount' => $distGrandTotal,
                'vat_deduction' => $distVatAmount,
                'total_sales' => $distVatableSales,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Keep running tally of distributor's cumulative sales
            $overallSales = DB::table('distributor_overall_sales')->where('distributor_id', $distributorId)->first();
            if ($overallSales) {
                DB::table('distributor_overall_sales')->where('distributor_id', $distributorId)->update([
                        'total_revenue' => $overallSales->total_revenue + $distVatableSales,
                        'total_sales_count' => $overallSales->total_sales_count + 1,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('distributor_overall_sales')->insert([
                    'distributor_id' => $distributorId,
                    'total_revenue' => $distVatableSales,
                    'total_sales_count' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Funds remitted and delivery cycle completed!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process remittance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delivery Personnel cancels/rejects the assigned delivery
     */
    public function rejectDelivery(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $delivery = ECOrderDelivery::find($id);

        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found.'], 404);
        }

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        $personnelName = $employee ? trim($employee->first_name . ' ' . $employee->last_name) : 'Unknown Delivery Personnel';

        DB::beginTransaction();

        try {
            $reasonString = "Rejected by {$personnelName}: " . $request->reason;

            if ($delivery->order_id) {
                DB::table('client_orders')->where('id', $delivery->order_id)->update([
                    'status' => 'confirmed',
                    'rejection_reason' => $reasonString
                ]);
            } elseif ($delivery->sp_order_id) {
                DB::table('sp_orders')->where('id', $delivery->sp_order_id)->update([
                    'status' => 'confirmed',
                    'rejection_reason' => $reasonString
                ]);
            }

            $delivery->delete();

            DB::commit();

            return response()->json(['message' => 'Delivery rejected successfully.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reject delivery: ' . $e->getMessage()], 500);
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