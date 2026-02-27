<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ECOrderController extends Controller
{
    public function index(Request $request)
    {
        // Authenticated user
        $user = Auth::user();
        
        // Find distributor context if operational distributor
        $distributorId = null;
        if ($user->role === 'operational_distributor') {
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

        // Scope to distributor if context is found, else fetch all (allowing any logged-in user to manage)
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

        return response()->json($formattedOrders);
    }

    public function confirmOrder(Request $request, $id)
    {
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