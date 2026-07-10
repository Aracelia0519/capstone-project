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
    private function resolveSupplierId($user)
    {
        $supplierId = $user->id;

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

        // LOCKOUT LOGIC: Only lock vehicles/drivers that are ALREADY en route OR remitting OR have been given the "Ready to go" signal
        $activeDeliveries = DB::table('supplier_deliveries')
            ->whereIn('status', ['in_transit', 'remitting'])
            ->orWhere(function($query) {
                $query->where('status', 'assigned')->where('is_ready_to_go', true);
            })
            ->get();
            
        $busyPersonnelIds = $activeDeliveries->pluck('delivery_personnel_id')->filter()->toArray();
        $busyVehicleIds = $activeDeliveries->pluck('vehicle_id')->filter()->toArray();

        // Fetch Delivery Personnel (Allows staging drivers)
        $deliveryPersonnel = DB::table('supplier_personnels')
            ->where('supplier_id', $supplierId)
            ->where('personnel_type', 'Delivery Personnel')
            ->where('status', 'active')
            ->whereNotIn('id', $busyPersonnelIds)
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->first_name . ' ' . $person->last_name
                ];
            });

        // CALCULATE LIVE STAGING WEIGHTS
        $stagingDeliveries = DB::table('supplier_deliveries')
            ->where('status', 'assigned')
            ->where('is_ready_to_go', false)
            ->get();

        $stagingWeights = [];
        foreach($stagingDeliveries as $sd) {
            $weight = 0;
            if (str_contains($sd->notes ?? '', '[REPLACEMENT DELIVERY]')) {
                $ret = ProcurementReturn::with('procurementRequest.product')->where('procurement_request_id', $sd->procurement_request_id)->first();
                if ($ret) {
                    $weight = $ret->quantity_returned * ($ret->procurementRequest->product->weight ?? 10.00);
                }
            } else {
                $req = ProcurementRequest::with('product')->find($sd->procurement_request_id);
                if ($req) {
                    $weight = $req->quantity * ($req->product->weight ?? 10.00);
                }
            }
            $stagingWeights[$sd->vehicle_id] = ($stagingWeights[$sd->vehicle_id] ?? 0) + $weight;
        }

        // FETCH VEHICLES
        try {
            $vehiclesRaw = DB::table('supplier_vehicles')
                ->where('supplier_id', $supplierId)
                ->whereNotIn('id', $busyVehicleIds)
                ->get();
        } catch (\Exception $e) {
            try {
                $vehiclesRaw = DB::table('supplier_vehicles')
                    ->where('user_id', $supplierId)
                    ->whereNotIn('id', $busyVehicleIds)
                    ->get();
            } catch (\Exception $e2) {
                $vehiclesRaw = DB::table('supplier_vehicles')
                    ->whereNotIn('id', $busyVehicleIds)
                    ->get();
            }
        }

        $vehicles = $vehiclesRaw->map(function ($v) use ($stagingWeights) {
            $name = $v->vehicle_name ?? $v->vehicle_type ?? $v->plate_number ?? $v->model ?? $v->make ?? $v->name ?? ('Vehicle #' . $v->id);
            $baseCapacity = $v->paint_capacity ?? $v->capacity ?? 0;
            $usedCapacity = $stagingWeights[$v->id] ?? 0;
            
            return [
                'id' => $v->id,
                'name' => $name,
                'capacity' => max(0, $baseCapacity - $usedCapacity),
                'max_capacity' => $baseCapacity,
                'used_capacity' => $usedCapacity
            ];
        });

        // 1. FETCH PREPARED ORDERS
        $preparedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->where('status', 'prepared')
            ->get()
            ->map(function ($order) {
                $productWeight = $order->product?->weight ?? 10.00;
                $totalWeight = $order->quantity * $productWeight;

                return [
                    'unique_id' => 'order_' . $order->id,
                    'id' => $order->id,
                    'type' => 'order',
                    'display_id' => $order->request_code, 
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'weight' => $order->quantity . ' Units', 
                    'totalWeight' => $totalWeight,
                    'status' => ucfirst($order->status),
                    'delivery_address' => $order->delivery_address,
                    'rejection_reason' => $order->rejection_reason, 
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
                $productWeight = $ret->procurementRequest?->product?->weight ?? 10.00;
                $totalWeight = $ret->quantity_returned * $productWeight;

                return [
                    'unique_id' => 'return_' . $ret->id,
                    'id' => $ret->id,
                    'type' => 'return',
                    'display_id' => $ret->procurementRequest ? $ret->procurementRequest->request_code . '-REP' : 'REP-' . $ret->id, 
                    'customer' => $ret->distributor ? $ret->distributor->full_name : 'Unknown Distributor',
                    'items' => $ret->quantity_returned . 'x ' . ($ret->procurementRequest ? $ret->procurementRequest->product_name : 'Unknown Product'),
                    'weight' => $ret->quantity_returned . ' Units', 
                    'totalWeight' => $totalWeight,
                    'status' => 'Replacement Prepared',
                    'delivery_address' => $ret->procurementRequest ? $ret->procurementRequest->delivery_address : null,
                    'rejection_reason' => $ret->rejection_reason ?? ($ret->procurementRequest ? $ret->procurementRequest->rejection_reason : null), 
                    'proofImage' => null,
                    'updated_at' => $ret->updated_at
                ];
            });

        $allPrepared = $preparedOrders->concat($preparedReturns)->sortByDesc('updated_at')->values();

        // 2. FETCH SHIPPED/ASSIGNED ORDERS – ORDER BY LATEST UPDATED, LIMIT 100
        $shippedOrders = ProcurementRequest::with(['distributor', 'product'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['assigned', 'shipped', 'in_transit', 'delivered'])
            ->orderBy('updated_at', 'desc')
            ->take(100) // Increased limit to ensure new entries appear
            ->get()
            ->map(function ($order) {
                $delivery = DB::table('supplier_deliveries')->where('procurement_request_id', $order->id)->orderBy('id', 'desc')->first();
                $vehicleName = 'Unknown Vehicle';
                if ($delivery && $delivery->vehicle_id) {
                    $veh = DB::table('supplier_vehicles')->where('id', $delivery->vehicle_id)->first();
                    if ($veh) {
                        $vehicleName = $veh->vehicle_name ?? $veh->vehicle_type ?? $veh->plate_number ?? $veh->model ?? $veh->make ?? $veh->name ?? ('Vehicle #' . $veh->id);
                    }
                }

                // Default to false if no delivery record (shouldn't happen but safe)
                $isReady = $delivery ? (bool)$delivery->is_ready_to_go : false;

                return [
                    'unique_id' => 'order_' . $order->id,
                    'id' => $order->id,
                    'type' => 'order',
                    'delivery_id' => $delivery ? $delivery->id : null,
                    'is_ready_to_go' => $isReady,
                    'vehicle_name' => $vehicleName,
                    'display_id' => $order->request_code,
                    'customer' => $order->distributor ? $order->distributor->full_name : 'Unknown Distributor',
                    'items' => $order->quantity . 'x ' . $order->product_name,
                    'status' => ucwords(str_replace('_', ' ', $order->status)),
                    'shipped_at' => $order->shipped_at ? date('M d, Y', strtotime($order->shipped_at)) : 'N/A',
                    'updated_at' => $order->updated_at
                ];
            });

        // 2b. FETCH SHIPPED/ASSIGNED RETURNS – ORDER BY LATEST UPDATED, LIMIT 100
        $shippedReturns = ProcurementReturn::with(['procurementRequest', 'distributor'])
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['assigned', 'shipped', 'in_transit', 'delivered'])
            ->orderBy('updated_at', 'desc')
            ->take(100) // Increased limit
            ->get()
            ->map(function ($ret) {
                $delivery = DB::table('supplier_deliveries')->where('procurement_request_id', $ret->procurement_request_id)->orderBy('id', 'desc')->first();
                $vehicleName = 'Unknown Vehicle';
                if ($delivery && $delivery->vehicle_id) {
                    $veh = DB::table('supplier_vehicles')->where('id', $delivery->vehicle_id)->first();
                    if ($veh) {
                        $vehicleName = $veh->vehicle_name ?? $veh->vehicle_type ?? $veh->plate_number ?? $veh->model ?? $veh->make ?? $veh->name ?? ('Vehicle #' . $veh->id);
                    }
                }

                $isReady = $delivery ? (bool)$delivery->is_ready_to_go : false;

                return [
                    'unique_id' => 'return_' . $ret->id,
                    'id' => $ret->id,
                    'type' => 'return',
                    'delivery_id' => $delivery ? $delivery->id : null,
                    'is_ready_to_go' => $isReady,
                    'vehicle_name' => $vehicleName,
                    'display_id' => $ret->procurementRequest ? $ret->procurementRequest->request_code . '-REP' : 'REP-' . $ret->id,
                    'customer' => $ret->distributor ? $ret->distributor->full_name : 'Unknown Distributor',
                    'items' => $ret->quantity_returned . 'x ' . ($ret->procurementRequest ? $ret->procurementRequest->product_name : 'Unknown Product'),
                    'status' => ucwords(str_replace('_', ' ', $ret->status)),
                    'shipped_at' => $ret->updated_at ? date('M d, Y', strtotime($ret->updated_at)) : 'N/A',
                    'updated_at' => $ret->updated_at
                ];
            });

        $allShipped = $shippedOrders->concat($shippedReturns)->sortByDesc('updated_at')->values();

        return response()->json([
            'prepared_orders' => $allPrepared,
            'shipped_orders' => $allShipped,
            'delivery_personnel' => $deliveryPersonnel,
            'vehicles' => $vehicles
        ]);
    }

    public function ship(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', 
            'delivery_personnel_id' => 'required|exists:supplier_personnels,id',
            'vehicle_id' => 'required', 
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

            $vehicle = DB::table('supplier_vehicles')->where('id', $request->vehicle_id)->first();
            $baseCapacity = $vehicle->paint_capacity ?? $vehicle->capacity ?? 0;

            // Recalculate live staging capacity locally to ensure server-side protection
            $stagingDeliveries = DB::table('supplier_deliveries')
                ->where('vehicle_id', $request->vehicle_id)
                ->where('status', 'assigned')
                ->where('is_ready_to_go', false)
                ->get();

            $usedCapacity = 0;
            foreach($stagingDeliveries as $sd) {
                if (str_contains($sd->notes ?? '', '[REPLACEMENT DELIVERY]')) {
                    $ret = ProcurementReturn::with('procurementRequest.product')->where('procurement_request_id', $sd->procurement_request_id)->first();
                    if ($ret) $usedCapacity += $ret->quantity_returned * ($ret->procurementRequest->product->weight ?? 10.00);
                } else {
                    $r = ProcurementRequest::with('product')->find($sd->procurement_request_id);
                    if ($r) $usedCapacity += $r->quantity * ($r->product->weight ?? 10.00);
                }
            }

            $availableCapacity = max(0, $baseCapacity - $usedCapacity);
            $totalWeight = 0;

            if ($type === 'order') {
                $procurementRequest = ProcurementRequest::with('product')->where('id', $id)
                    ->where('supplier_id', $supplierId)
                    ->firstOrFail();

                if ($procurementRequest->status !== 'prepared') {
                    return response()->json(['message' => 'Order is not in prepared status.'], 400);
                }

                $weightPerUnit = $procurementRequest->product?->weight ?? 10.00;
                $totalWeight = $procurementRequest->quantity * $weightPerUnit;

                if ($totalWeight > $availableCapacity) {
                    DB::rollBack();
                    return response()->json(['message' => "Capacity Exceeded! This request adds ({$totalWeight}kg). Vehicle only has ({$availableCapacity}kg) remaining out of ({$baseCapacity}kg) max capacity."], 422);
                }

                DB::table('supplier_deliveries')->insert([
                    'procurement_request_id' => $procurementRequest->id,
                    'delivery_personnel_id' => $request->delivery_personnel_id,
                    'vehicle_id' => $request->vehicle_id,
                    'shipping_proof_path' => $imagePath,
                    'status' => 'assigned',
                    'is_ready_to_go' => false, 
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $procurementRequest->status = 'shipped'; 
                $procurementRequest->rejection_reason = null; 
                $procurementRequest->save();

            } elseif ($type === 'return') {
                $returnReq = ProcurementReturn::with('procurementRequest.product')->where('id', $id)
                    ->where('supplier_id', $supplierId)
                    ->firstOrFail();

                if ($returnReq->status !== 'prepared') {
                    return response()->json(['message' => 'Return is not in prepared status.'], 400);
                }

                $weightPerUnit = $returnReq->procurementRequest?->product?->weight ?? 10.00;
                $totalWeight = $returnReq->quantity_returned * $weightPerUnit;

                if ($totalWeight > $availableCapacity) {
                    DB::rollBack();
                    return response()->json(['message' => "Capacity Exceeded! This request adds ({$totalWeight}kg). Vehicle only has ({$availableCapacity}kg) remaining out of ({$baseCapacity}kg) max capacity."], 422);
                }

                DB::table('supplier_deliveries')->insert([
                    'procurement_request_id' => $returnReq->procurement_request_id,
                    'delivery_personnel_id' => $request->delivery_personnel_id,
                    'vehicle_id' => $request->vehicle_id,
                    'shipping_proof_path' => $imagePath,
                    'status' => 'assigned',
                    'is_ready_to_go' => false,
                    'notes' => '[REPLACEMENT DELIVERY] For Return Request #' . $returnReq->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $returnReq->status = 'shipped';
                $returnReq->rejection_reason = null; 
                $returnReq->save();

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
                'message' => ucfirst($type) . ' appended successfully. Waiting for dispatch Ready Signal.',
                'image_url' => asset($imagePath)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process shipment assignment: ' . $e->getMessage()], 500);
        }
    }

    public function markReady(Request $request, $id)
    {
        $delivery = DB::table('supplier_deliveries')->where('id', $id)->first();
        
        if ($delivery) {
            // Batched dispatch: update ALL staging deliveries assigned to this specific driver/vehicle pair
            DB::table('supplier_deliveries')
                ->where('vehicle_id', $delivery->vehicle_id)
                ->where('delivery_personnel_id', $delivery->delivery_personnel_id)
                ->where('status', 'assigned')
                ->where('is_ready_to_go', false)
                ->update([
                    'is_ready_to_go' => true,
                    'updated_at' => now()
                ]);
        }

        return response()->json(['message' => 'Ready signal successfully dispatched to Driver Application!']);
    }
}