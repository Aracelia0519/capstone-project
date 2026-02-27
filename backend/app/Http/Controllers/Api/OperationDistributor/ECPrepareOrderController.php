<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientOrder;
use App\Models\HR\Employee;
use App\Models\OperationDistributor\ECOrderDelivery;

class ECPrepareOrderController extends Controller
{
    // Fetch all confirmed (and prepared to stay in processed history) orders
    public function index()
    {
        $orders = ClientOrder::with(['items.product', 'client'])
            ->whereIn('status', ['confirmed', 'prepared'])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
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

        return response()->json($orders);
    }

    // Fetch Delivery Personnels dynamically from hr_employees
    // Fetch Delivery Personnels dynamically from hr_employees
    public function deliveryPersonnel(Request $request)
    {
        $user = $request->user();
        $distributorId = null;

        // Ensure we only fetch employees belonging to the current distributor's company
        if ($user->role === 'operational_distributor' && $user->operationalDistributor) {
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
        $request->validate([
            'delivery_personnel_id' => 'required|exists:hr_employees,id',
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $order = ClientOrder::findOrFail($id);

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'ec_preparation_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('ec_preparations', $filename, 'public');

            // Create Delivery Record
            ECOrderDelivery::create([
                'order_id' => $order->id,
                'delivery_personnel_id' => $request->delivery_personnel_id,
                'preparation_proof_path' => 'storage/' . $path,
                'status' => 'assigned'
            ]);

            // Mark the order as prepared
            $order->update(['status' => 'prepared']);

            return response()->json(['message' => 'Order prepared and dispatched successfully.']);
        }

        return response()->json(['message' => 'Proof image is required.'], 400);
    }
}