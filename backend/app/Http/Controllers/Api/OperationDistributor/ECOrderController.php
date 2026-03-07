<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ECOrderController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module.
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
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
                    'can_create' => (bool) $access->can_create,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
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
        // Authenticated user
        $user = Auth::user();

        // Get permissions and check RBAC Read Access
        $permissions = $this->getPermissions($user, 'ec_orders');
        
        if (!$permissions['can_view']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view orders.'], 403);
        }
        
        // Find distributor context based on user role
        $distributorId = null;
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            $distributorId = $employee ? $employee->parent_distributor_id : null;
        } elseif ($user->role === 'operational_distributor') {
            $opDistributor = DB::table('operational_distributors')
                ->where('user_id', $user->id)
                ->first();
            if ($opDistributor) {
                $distributorId = $opDistributor->parent_distributor_id;
            }
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        }

        $query = ClientOrder::with(['client', 'items.product']);

        // Scope to distributor if context is found, else fetch all
        if ($distributorId) {
            $query->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'order_date' => $order->created_at->toIso8601String(),
                'total_amount' => $order->total_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'payment_method' => $order->payment_method,
                'delivery_address' => $order->delivery_address,
                'client_name' => $order->client ? $order->client->full_name : 'Unknown Client',
                'client_phone' => $order->client ? $order->client->phone : 'N/A',
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->product ? $item->product->name : 'Unknown Product',
                        'category' => $item->product ? $item->product->category : 'N/A',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->quantity * $item->price,
                    ];
                })
            ];
        });

        // Return standardized wrapper matching ProcurementRequests
        return response()->json([
            'success' => true,
            'data' => $formattedOrders,
            'permissions' => $permissions
        ]);
    }

    public function confirmOrder(Request $request, $id)
    {
        // Hard backend check to prevent bypass
        if (!$this->checkRbacAccess($request->user(), 'ec_orders', 'can_update')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to confirm orders.'], 403);
        }

        $order = ClientOrder::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be confirmed'], 400);
        }

        $order->status = 'confirmed';
        $order->save();

        return response()->json([
            'message' => 'Order confirmed successfully',
            'order' => $order
        ]);
    }
}