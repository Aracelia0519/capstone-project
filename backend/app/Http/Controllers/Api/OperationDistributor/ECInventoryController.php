<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ECInventoryController extends Controller
{
    /**
     * Display a listing of the inventory for the Operational Distributor.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Find the parent distributor ID for the logged-in operational distributor
            $opDistributor = DB::table('operational_distributors')
                ->where('user_id', $user->id)
                ->first();
                
            $distributorId = $opDistributor ? $opDistributor->parent_distributor_id : $user->id;

            // Fetch inventories alongside the product details
            $inventories = DistributorInventory::where('distributor_id', $distributorId)
                ->with('product')
                ->get();

            $formatted = $inventories->map(function ($inv) {
                $product = $inv->product;
                
                // Format image URL properly
                $imageUrl = $product->image_url;
                if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL) && !str_starts_with($imageUrl, 'data:')) {
                    $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                }

                return [
                    'id' => $inv->id, // Use inventory ID for row tracking
                    'product_id' => $inv->product_id,
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'category' => $product->category,
                    'type' => $product->type,
                    'size' => $product->size,
                    'color_code' => $product->color_code,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'quantity' => $inv->quantity,
                    'min_stock_level' => $product->min_stock_level,
                    'max_stock_level' => $product->max_stock_level,
                    'description' => $product->description,
                    'image_url' => $imageUrl,
                    'ecommerce_status' => $inv->ecommerce_status ?? 'not_deployed',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading EC Inventory:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request a product to be deployed to the E-Commerce store.
     */
    public function requestDeployment($id)
    {
        try {
            // Find the exact inventory record
            $inventory = DistributorInventory::findOrFail($id);

            // Update status to pending ON THE INVENTORY TABLE
            $inventory->update([
                'ecommerce_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deployment requested successfully. Waiting for Business Owner approval.',
                'data' => [
                    'ecommerce_status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error requesting deployment:', [
                'inventory_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to request deployment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}