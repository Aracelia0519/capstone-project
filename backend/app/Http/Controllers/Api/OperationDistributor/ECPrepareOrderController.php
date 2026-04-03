<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\ServiceProvider\SpOrder; // Added for Service Provider Orders
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ECPrepareOrderController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module (Level-Based).
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'admin' || $user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_manage' => true,
                'can_approve' => true
            ];
        }

        // Check RBAC for standard employees
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

    // Fetch all confirmed (and prepared to stay in processed history) orders
    public function index(Request $request)
    {
        $user = $request->user();

        // Get permissions and check RBAC Read Access using the key 'ec_prepare_order'
        $permissions = $this->getPermissions($user, 'ec_prepare_order');
        
        if (!$permissions['can_view']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view prepare orders.'], 403);
        }

        // Find distributor context based on user role
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

        // ==========================================
        // 1. FETCH CLIENT ORDERS
        // ==========================================
        $clientOrdersQuery = ClientOrder::with(['client', 'items.product'])->whereIn('status', ['confirmed', 'prepared', 'ready_for_pickup']);

        if ($distributorId) {
            $clientOrdersQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }

        $clientOrders = $clientOrdersQuery->get()->map(function ($order) {
            $latestDelivery = DB::table('ec_order_deliveries')
                ->where('order_id', $order->id)
                ->latest('id')
                ->first();
                
            $isDeliveryRejected = false;
            if ($latestDelivery && ($latestDelivery->status === 'rejected' || $latestDelivery->status === 'cancelled')) {
                $isDeliveryRejected = true;
            }

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
                'is_delivery_rejected' => $isDeliveryRejected,
                'latest_delivery_status' => $latestDelivery ? $latestDelivery->status : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'category' => $item->product ? $item->product->category : 'Uncategorized',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->quantity * $item->price,
                    ];
                })
            ];
        });

        // ==========================================
        // 2. FETCH SERVICE PROVIDER ORDERS
        // ==========================================
        $spOrdersQuery = SpOrder::with(['items.product'])->whereIn('status', ['confirmed', 'prepared', 'ready_for_pickup']);

        if ($distributorId) {
            $spOrdersQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }

        $spOrders = $spOrdersQuery->get()->map(function ($order) {
            $latestDelivery = DB::table('ec_order_deliveries')
                ->where('sp_order_id', $order->id)
                ->latest('id')
                ->first();
                
            $isDeliveryRejected = false;
            if ($latestDelivery && ($latestDelivery->status === 'rejected' || $latestDelivery->status === 'cancelled')) {
                $isDeliveryRejected = true;
            }

            // Fetch SP User via DB to avoid explicit relationship requirements
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
                'is_delivery_rejected' => $isDeliveryRejected,
                'latest_delivery_status' => $latestDelivery ? $latestDelivery->status : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'category' => $item->product ? $item->product->category : 'Uncategorized',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->quantity * $item->price,
                    ];
                })
            ];
        });

        // Merge both order lists and sort by date descending
        $orders = $clientOrders->concat($spOrders)->sortByDesc('order_date')->values();

        // Return standardized wrapper matching RBAC structure
        return response()->json([
            'success' => true,
            'data' => $orders,
            'permissions' => $permissions
        ]);
    }

    // Fetch Delivery Personnels dynamically from hr_employees
    public function deliveryPersonnel(Request $request)
    {
        $user = $request->user();

        // Quick check to ensure user has any sort of access to this module
        if (!$this->checkRbacAccess($user, 'ec_prepare_order', 'can_view') && !$this->checkRbacAccess($user, 'ec_prepare_order', 'can_manage')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distributorId = null;

        // Ensure we only fetch employees belonging to the current distributor's company
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

    // Assign the Delivery Personnel and update Status
    public function dispatchOrder(Request $request, $id)
    {
        // Hard backend RBAC check for performing the update (Requires Manage level)
        if (!$this->checkRbacAccess($request->user(), 'ec_prepare_order', 'can_manage')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to dispatch orders.'], 403);
        }

        $rules = [
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'order_type' => 'required|in:client,sp' // Distinguish order source
        ];

        $orderType = $request->input('order_type', 'client');

        if ($orderType === 'sp') {
            $order = SpOrder::findOrFail($id);
        } else {
            $order = ClientOrder::findOrFail($id);
        }
        
        // Determine if it's a Pick-Up Order
        $isPickUp = in_array(strtolower($order->payment_method), ['pick-up', 'pickup']);

        // Conditionally require delivery personnel if it is NOT a pick-up
        if (!$isPickUp) {
            $rules['delivery_personnel_id'] = 'required|exists:hr_employees,id';
        }

        $request->validate($rules);

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'ec_preparation_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('ec_preparations', $filename, 'public');

            // Insert using DB facade to prevent MassAssignment or Model mapping conflicts safely
            DB::table('ec_order_deliveries')->insert([
                'order_id' => $orderType === 'client' ? $order->id : null,
                'sp_order_id' => $orderType === 'sp' ? $order->id : null,
                'delivery_personnel_id' => $isPickUp ? null : $request->delivery_personnel_id,
                'preparation_proof_path' => 'storage/' . $path,
                'status' => $isPickUp ? 'ready_for_pickup' : 'assigned',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mark the order as prepared or ready for pickup
            $newStatus = $isPickUp ? 'ready_for_pickup' : 'prepared';
            $order->update(['status' => $newStatus, 'rejection_reason' => null]); // Clear any past rejection reasons

            return response()->json(['message' => 'Order processed successfully.']);
        }

        return response()->json(['message' => 'Proof image is required.'], 400);
    }
}