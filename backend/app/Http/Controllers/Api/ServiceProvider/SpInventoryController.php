<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider\SpInventory;
use App\Models\ServiceProvider\SpOrderItem;
use Illuminate\Support\Facades\Auth;

class SpInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $inventory = SpInventory::with('product')
            ->where('service_provider_id', $user->id)
            ->get();
            
        $inventory->each(function ($item) {
            if ($item->product && $item->product->image_url) {
                if (!filter_var($item->product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($item->product->image_url, 'data:')) {
                    $item->product->image_url = asset('storage/' . ltrim($item->product->image_url, '/'));
                }
            }
        });

        return response()->json(['success' => true, 'data' => $inventory]);
    }

    public function addFromOrder(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:sp_order_items,id'
        ]);

        $user = $request->user();
        $orderItem = SpOrderItem::findOrFail($request->order_item_id);

        if ($orderItem->added_to_inventory) {
            return response()->json(['success' => false, 'message' => 'Already added to inventory'], 400);
        }

        $hasReturn = \App\Models\ServiceProvider\SpReturnRequest::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();

        if ($hasReturn) {
            return response()->json(['success' => false, 'message' => 'Cannot add to inventory while on return process'], 400);
        }

        $inventory = SpInventory::firstOrCreate(
            [
                'service_provider_id' => $user->id,
                'distributor_id' => $orderItem->distributor_id,
                'product_id' => $orderItem->product_id,
            ],
            ['quantity' => 0]
        );

        $inventory->quantity += $orderItem->quantity;
        $inventory->save();

        $orderItem->added_to_inventory = true;
        $orderItem->save();

        return response()->json(['success' => true, 'message' => 'Added to inventory successfully']);
    }

    public function useProduct(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();
        $inventory = SpInventory::where('id', $id)->where('service_provider_id', $user->id)->firstOrFail();

        if ($inventory->quantity < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient quantity in inventory'], 400);
        }

        $inventory->quantity -= $request->quantity;
        $inventory->save();

        return response()->json(['success' => true, 'message' => 'Product quantity updated successfully']);
    }
}