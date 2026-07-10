<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\ServiceProvider\SpOrder;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\Ecommerce\DeliveryUpdated;
use App\Events\Ecommerce\OrderUpdated;

class ECPrepareOrderController extends Controller
{
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        if ($user->role === 'admin' || $user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_manage' => true,
                'can_approve' => true
            ];
        }

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_manage' => (bool) $access->can_manage,
                    'can_approve' => (bool) $access->can_approve,
                ];
            }
        }

        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action)
    {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $permissions = $this->getPermissions($user, 'ec_prepare_order');
        
        if (!$permissions['can_view']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view prepare orders.'], 403);
        }

        $distributorId = null;
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            $distributorId = $employee ? $employee->parent_distributor_id : null;
        } elseif ($user->role === 'operational_distributor') {
            $opDistributor = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            if ($opDistributor) {
                $distributorId = $opDistributor->parent_distributor_id;
            }
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        }

        // ---- 1. Prepared Orders (status = confirmed) ----
        $clientOrdersQuery = ClientOrder::with(['client', 'items.product'])->where('status', 'confirmed');
        if ($distributorId) {
            $clientOrdersQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }
        $clientOrders = $clientOrdersQuery->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_type' => 'client',
                'order_number' => $order->order_number,
                'status' => $order->status,
                'order_date' => $order->created_at,
                'total_amount' => $order->total_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'payment_method' => $order->payment_method,
                'delivery_address' => $order->delivery_address,
                'client_name' => $order->client ? $order->client->full_name : 'Unknown Client',
                'client_phone' => $order->client ? $order->client->phone : 'No Contact Provided',
                'rejection_reason' => $order->rejection_reason,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'category' => $item->product ? $item->product->category : 'Uncategorized',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->quantity * $item->price,
                        'weight' => $item->product ? $item->product->weight : 10.00,
                    ];
                })
            ];
        });

        $spOrdersQuery = SpOrder::with(['items.product'])->where('status', 'confirmed');
        if ($distributorId) {
            $spOrdersQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }
        $spOrders = $spOrdersQuery->get()->map(function ($order) {
            $spUser = DB::table('users')->where('id', $order->service_provider_id)->first();
            return [
                'id' => $order->id,
                'order_type' => 'sp',
                'order_number' => $order->order_number,
                'status' => $order->status,
                'order_date' => $order->created_at,
                'total_amount' => $order->total_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'payment_method' => $order->payment_method,
                'delivery_address' => $order->delivery_address,
                'client_name' => $spUser ? ($spUser->first_name . ' ' . $spUser->last_name) : 'Unknown Provider',
                'client_phone' => $spUser ? $spUser->phone : 'No Contact Provided',
                'rejection_reason' => $order->rejection_reason,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'category' => $item->product ? $item->product->category : 'Uncategorized',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->quantity * $item->price,
                        'weight' => $item->product ? $item->product->weight : 10.00,
                    ];
                })
            ];
        });

        $preparedOrders = $clientOrders->concat($spOrders)->sortByDesc('order_date')->values();

        // ---- 2. Delivery Personnel ----
        $personnelQuery = Employee::where('status', 'active')
            ->where(function($q) {
                $q->where('position', 'LIKE', '%Delivery Personnel%')
                  ->orWhere('position', 'LIKE', '%Delivery%');
            });
        if ($distributorId) {
            $personnelQuery->where('parent_distributor_id', $distributorId);
        }
        $deliveryPersonnel = $personnelQuery->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => trim($emp->first_name . ' ' . $emp->last_name)
            ];
        });

        // ---- 3. Vehicles with live staging capacity ----
        // Calculate current staging weight per vehicle (assigned deliveries not yet ready)
        $stagingDeliveries = DB::table('ec_order_deliveries')
            ->where('status', 'assigned')
            ->where('is_ready_to_go', false)
            ->get();

        $stagingWeights = [];
        foreach ($stagingDeliveries as $sd) {
            $weight = 0;
            if ($sd->order_id) {
                $order = ClientOrder::with('items.product')->find($sd->order_id);
                if ($order) {
                    foreach ($order->items as $item) {
                        $weight += $item->quantity * ($item->product->weight ?? 10.00);
                    }
                }
            } elseif ($sd->sp_order_id) {
                $order = SpOrder::with('items.product')->find($sd->sp_order_id);
                if ($order) {
                    foreach ($order->items as $item) {
                        $weight += $item->quantity * ($item->product->weight ?? 10.00);
                    }
                }
            }
            $stagingWeights[$sd->vehicle_id] = ($stagingWeights[$sd->vehicle_id] ?? 0) + $weight;
        }

        $vehicles = DB::table('ecommerce_vehicles')
            ->where('status', 'Active')
            ->get()
            ->map(function ($v) use ($stagingWeights) {
                $baseCapacity = $v->paint_capacity ?? 0;
                $usedCapacity = $stagingWeights[$v->id] ?? 0;
                return [
                    'id' => $v->id,
                    'name' => $v->plate_number . ' - ' . $v->model,
                    'capacity' => max(0, $baseCapacity - $usedCapacity),
                    'max_capacity' => $baseCapacity,
                    'used_capacity' => $usedCapacity,
                ];
            });

        // ---- 4. Shipped/Assigned Orders with is_ready_to_go ----
        $shippedOrders = [];
        $allDeliveries = DB::table('ec_order_deliveries')
            ->whereIn('status', ['assigned', 'in_transit', 'delivered', 'remitting', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($allDeliveries as $delivery) {
            $orderData = null;
            $orderType = null;
            if ($delivery->order_id) {
                $order = ClientOrder::find($delivery->order_id);
                if ($order) {
                    $orderData = $order;
                    $orderType = 'client';
                }
            } elseif ($delivery->sp_order_id) {
                $order = SpOrder::find($delivery->sp_order_id);
                if ($order) {
                    $orderData = $order;
                    $orderType = 'sp';
                }
            }
            if (!$orderData) continue;

            $customer = 'Unknown';
            if ($orderType === 'client' && $orderData->client) {
                $customer = $orderData->client->full_name;
            } elseif ($orderType === 'sp') {
                $spUser = DB::table('users')->find($orderData->service_provider_id);
                if ($spUser) {
                    $customer = $spUser->first_name . ' ' . $spUser->last_name;
                }
            }

            $shippedOrders[] = [
                'id' => $orderData->id,
                'delivery_id' => $delivery->id,
                'order_type' => $orderType,
                'order_number' => $orderData->order_number,
                'status' => $orderData->status,
                'customer' => $customer,
                'items' => $orderData->items ? $orderData->items->sum('quantity') . ' items' : 'N/A',
                'is_ready_to_go' => (bool)$delivery->is_ready_to_go,
                'vehicle_name' => $delivery->vehicle_id 
                    ? DB::table('ecommerce_vehicles')->where('id', $delivery->vehicle_id)->value('plate_number') ?? 'Unknown' 
                    : 'Unknown',
                'shipped_at' => $delivery->created_at,
                'updated_at' => $delivery->updated_at,
                'delivery_status' => $delivery->status,
                'vehicle_id' => $delivery->vehicle_id,
                'delivery_personnel_id' => $delivery->delivery_personnel_id, // included for batching
            ];
        }
        $shippedOrders = collect($shippedOrders)->sortByDesc('updated_at')->values();

        // ---- 5. Pending ready orders (assigned and not ready) ----
        $pendingReadyOrders = collect($shippedOrders)->filter(function ($order) {
            return $order['delivery_status'] === 'assigned' && !$order['is_ready_to_go'];
        })->values();

        return response()->json([
            'success' => true,
            'prepared_orders' => $preparedOrders,
            'shipped_orders' => $shippedOrders,
            'pending_ready_orders' => $pendingReadyOrders,
            'delivery_personnel' => $deliveryPersonnel,
            'vehicles' => $vehicles,
            'permissions' => $permissions,
            'distributor_id' => $distributorId,
            'is_admin' => $user->role === 'admin'
        ]);
    }

    public function deliveryPersonnel(Request $request)
    {
        $user = $request->user();

        if (!$this->checkRbacAccess($user, 'ec_prepare_order', 'can_view') && !$this->checkRbacAccess($user, 'ec_prepare_order', 'can_manage')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distributorId = null;

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            $distributorId = $employee ? $employee->parent_distributor_id : null;
        } elseif ($user->role === 'operational_distributor' && $user->operationalDistributor) {
            $distributorId = $user->operationalDistributor->parent_distributor_id;
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        }

        $query = Employee::where('status', 'active')
            ->where(function($q) {
                $q->where('position', 'LIKE', '%Delivery Personnel%')
                  ->orWhere('position', 'LIKE', '%Delivery%');
            });

        if ($distributorId) {
            $query->where('parent_distributor_id', $distributorId);
        }

        $personnel = $query->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => trim($emp->first_name . ' ' . $emp->last_name)
            ];
        });

        return response()->json($personnel);
    }

    public function dispatchOrder(Request $request, $id)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_prepare_order', 'can_manage')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to dispatch orders.'], 403);
        }

        $rules = [
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'order_type' => 'required|in:client,sp',
            'vehicle_id' => 'required|exists:ecommerce_vehicles,id',
        ];

        $orderType = $request->input('order_type', 'client');

        if ($orderType === 'sp') {
            $order = SpOrder::with('items.product')->findOrFail($id);
        } else {
            $order = ClientOrder::with('items.product')->findOrFail($id);
        }
        
        $isPickUp = in_array(strtolower($order->payment_method), ['pick-up', 'pickup']);

        if (!$isPickUp) {
            $rules['delivery_personnel_id'] = 'required|exists:hr_employees,id';
        }

        $request->validate($rules);

        // --- Weight Validation ---
        $vehicleId = $request->vehicle_id;
        $vehicle = DB::table('ecommerce_vehicles')->where('id', $vehicleId)->first();
        if (!$vehicle) {
            return response()->json(['message' => 'Selected vehicle not found.'], 404);
        }

        $baseCapacity = $vehicle->paint_capacity ?? 0;

        // Calculate current staging weight for this vehicle (assigned, not yet ready)
        $stagingDeliveries = DB::table('ec_order_deliveries')
            ->where('vehicle_id', $vehicleId)
            ->where('status', 'assigned')
            ->where('is_ready_to_go', false)
            ->get();

        $usedWeight = 0;
        foreach ($stagingDeliveries as $sd) {
            if ($sd->order_id) {
                $o = ClientOrder::with('items.product')->find($sd->order_id);
                if ($o) {
                    foreach ($o->items as $item) {
                        $usedWeight += $item->quantity * ($item->product->weight ?? 10.00);
                    }
                }
            } elseif ($sd->sp_order_id) {
                $o = SpOrder::with('items.product')->find($sd->sp_order_id);
                if ($o) {
                    foreach ($o->items as $item) {
                        $usedWeight += $item->quantity * ($item->product->weight ?? 10.00);
                    }
                }
            }
        }

        // Calculate total weight of the current order
        $totalWeight = 0;
        foreach ($order->items as $item) {
            $totalWeight += $item->quantity * ($item->product->weight ?? 10.00);
        }

        $availableCapacity = $baseCapacity - $usedWeight;

        if ($totalWeight > $availableCapacity) {
            return response()->json([
                'message' => "Capacity Exceeded! This order adds ({$totalWeight}kg). Vehicle only has ({$availableCapacity}kg) remaining out of ({$baseCapacity}kg) max capacity."
            ], 422);
        }

        // Proceed with dispatch
        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'ec_preparation_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('ec_preparations', $filename, 'public');

            DB::table('ec_order_deliveries')->insert([
                'order_id' => $orderType === 'client' ? $order->id : null,
                'sp_order_id' => $orderType === 'sp' ? $order->id : null,
                'delivery_personnel_id' => $isPickUp ? null : $request->delivery_personnel_id,
                'vehicle_id' => $request->vehicle_id,
                'preparation_proof_path' => 'storage/' . $path,
                'status' => $isPickUp ? 'ready_for_pickup' : 'assigned',
                'is_ready_to_go' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newStatus = $isPickUp ? 'ready_for_pickup' : 'prepared';
            $order->update(['status' => $newStatus, 'rejection_reason' => null]);

            $orderItem = DB::table($orderType === 'sp' ? 'sp_order_items' : 'client_order_items')
                ->where($orderType === 'sp' ? 'sp_order_id' : 'order_id', $order->id)
                ->first();
                
            $distributorId = $orderItem ? $orderItem->distributor_id : null;

            if ($distributorId) {
                event(new DeliveryUpdated($distributorId, $request->delivery_personnel_id));
            }

            if ($orderType === 'client') {
                event(new OrderUpdated($order->client_id, null));
            } else {
                event(new OrderUpdated(null, $order->service_provider_id));
            }

            return response()->json(['message' => 'Order assigned to vehicle successfully. Awaiting Ready signal.']);
        }

        return response()->json(['message' => 'Proof image is required.'], 400);
    }

    public function markReady(Request $request, $deliveryId)
    {
        $delivery = DB::table('ec_order_deliveries')->where('id', $deliveryId)->first();
        if (!$delivery) {
            return response()->json(['message' => 'Delivery not found.'], 404);
        }

        // Batched update: all deliveries with same vehicle_id, delivery_personnel_id, status='assigned', and not yet ready
        $updatedCount = DB::table('ec_order_deliveries')
            ->where('vehicle_id', $delivery->vehicle_id)
            ->where('delivery_personnel_id', $delivery->delivery_personnel_id)
            ->where('status', 'assigned')
            ->where('is_ready_to_go', false)
            ->update([
                'is_ready_to_go' => true,
                'updated_at' => now()
            ]);

        // Fire event for real-time updates
        event(new DeliveryUpdated($delivery->distributor_id ?? null));

        return response()->json([
            'message' => "Ready signal dispatched to driver for {$updatedCount} delivery(s) on this vehicle."
        ]);
    }
}