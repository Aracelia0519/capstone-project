<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\ServiceProvider\SpOrder; // Added for Service Provider Orders
use App\Models\User; // Added to fetch Service Provider details
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;

class ECOrderController extends Controller
{
    /**
     * Check RBAC Permissions for E-Commerce Orders Module (Level-Based)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        // Admin
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        // Distributor
        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        // HR Manager
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager && $hrManager->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        } 
        
        // Operational Distributor
        elseif ($user->role === 'operational_distributor') {
            $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first(); 
            if ($opDist && $opDist->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $opDist->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        }
        
        // Employee with specific RBAC
        elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'ec_orders') // Permission key for this module
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_manage' => (bool)$access->can_manage,
                                    'can_approve' => (bool)$access->can_approve,
                                ]
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'has_access' => false,
            'distributor_id' => null,
            'permissions' => ['can_view' => false, 'can_manage' => false, 'can_approve' => false]
        ];
    }

    public function index(Request $request)
    {
        // Authenticated user
        $user = Auth::user();

        // Get permissions and check RBAC Read Access
        $accessData = $this->checkAccess($user, 'can_view');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view orders.'], 403);
        }
        
        $distributorId = $accessData['distributor_id'];

        // ===============================================
        // 1. FETCH CLIENT ORDERS
        // ===============================================
        $clientQuery = ClientOrder::with(['client', 'items.product']);

        if ($distributorId) {
            $clientQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }

        $clientOrders = $clientQuery->get();

        $formattedClientOrders = $clientOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'order_type' => 'client', // Distinguish origin
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

        // ===============================================
        // 2. FETCH SERVICE PROVIDER ORDERS
        // ===============================================
        $spQuery = SpOrder::with(['items.product']);

        if ($distributorId) {
            $spQuery->whereHas('items', function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId);
            })->with(['items' => function($q) use ($distributorId) {
                $q->where('distributor_id', $distributorId)->with('product');
            }]);
        }

        $spOrders = $spQuery->get();

        // Get Service Provider User records manually to append names
        $spUserIds = $spOrders->pluck('service_provider_id')->unique();
        $spUsers = User::whereIn('id', $spUserIds)->get()->keyBy('id');

        $formattedSpOrders = $spOrders->map(function ($order) use ($spUsers) {
            $spUser = $spUsers->get($order->service_provider_id);
            return [
                'id' => $order->id,
                'order_type' => 'service_provider', // Distinguish origin
                'order_number' => $order->order_number,
                'status' => $order->status,
                'order_date' => $order->created_at->toIso8601String(),
                'total_amount' => $order->total_amount,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'payment_method' => $order->payment_method,
                'delivery_address' => $order->delivery_address,
                'client_name' => $spUser ? $spUser->full_name : 'Unknown Service Provider', // Fallback to 'client_name' property for Vue UI ease
                'client_phone' => $spUser ? $spUser->phone : 'N/A',
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

        // ===============================================
        // 3. MERGE & SORT BY DATE
        // ===============================================
        $allOrders = collect($formattedClientOrders)
            ->concat($formattedSpOrders)
            ->sortByDesc('order_date')
            ->values();

        // Return standardized wrapper matching ProcurementRequests
        return response()->json([
            'success' => true,
            'data' => $allOrders,
            'permissions' => $accessData['permissions']
        ]);
    }

    public function confirmOrder(Request $request, $id)
    {
        // Require explicit Approve level to confirm workflow
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_approve');
        
        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to confirm orders.'], 403);
        }

        // Distinct table based on who placed the order
        $type = $request->input('type', 'client');

        if ($type === 'service_provider') {
            $order = SpOrder::findOrFail($id);
        } else {
            $order = ClientOrder::findOrFail($id);
        }

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