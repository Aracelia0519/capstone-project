<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductDeploymentController extends Controller
{
    /**
     * Fetch all deployment requests (Pending and Deployed items)
     */
    public function index()
    {
        try {
            // Assuming the logged-in user is the distributor
            $distributorId = Auth::id();

            // Fetch inventories that have an e-commerce interaction
            $inventories = DistributorInventory::with(['product', 'distributor'])
                ->where('distributor_id', $distributorId)
                ->whereIn('ecommerce_status', ['pending', 'deployed', 'not_deployed'])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Format image URLs and prepare price data for frontend display
            $inventories->transform(function ($inventory) {
                if ($inventory->product) {
                    if ($inventory->product->image_url) {
                        if (!filter_var($inventory->product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($inventory->product->image_url, 'data:')) {
                            $inventory->product->image_url = asset('storage/' . ltrim($inventory->product->image_url, '/'));
                        }
                    }
                    
                    // If cost is null, it hasn't been deployed/marked up yet in the DB.
                    // We send projected prices to the frontend so the user knows what will happen.
                    if (is_null($inventory->product->cost) || $inventory->product->cost <= 0) {
                        $inventory->product->original_cost = $inventory->product->price;
                        // Frontend will handle dynamic projection, but we send a base fallback
                        $inventory->product->projected_price = $inventory->product->price; 
                    } else {
                        // It has been deployed, the DB already holds the true cost and marked-up price
                        $inventory->product->original_cost = $inventory->product->cost;
                        $inventory->product->projected_price = $inventory->product->price;
                    }
                }
                return $inventory;
            });

            return response()->json([
                'success' => true,
                'data' => $inventories
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading deployments:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load deployment requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deploy the product to e-commerce
     */
    public function deploy(Request $request, $id)
    {
        // Validate the incoming markup percentage (defaults to 30 if none provided)
        $request->validate([
            'markup_percentage' => 'nullable|numeric|min:0'
        ]);

        $markupPercentage = $request->input('markup_percentage', 30);

        try {
            // Eager load the product so we can modify its columns
            $inventory = DistributorInventory::with('product')->where('distributor_id', Auth::id())->findOrFail($id);
            $inventory->ecommerce_status = 'deployed';
            $inventory->save();

            // Permanently alter the price and cost in the distributor_products table
            if ($inventory->product) {
                // Check if cost is already set to prevent double markups if deployed multiple times
                if (is_null($inventory->product->cost) || $inventory->product->cost <= 0) {
                    $inventory->product->cost = $inventory->product->price; // Save old price to cost
                    
                    // Apply the dynamic markup percentage to the price
                    $multiplier = 1 + ($markupPercentage / 100);
                    $inventory->product->price = round($inventory->product->price * $multiplier, 2); 
                    
                    $inventory->product->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product deployed to e-commerce successfully.',
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deploy product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject the deployment request
     */
    public function reject($id)
    {
        try {
            $inventory = DistributorInventory::where('distributor_id', Auth::id())->findOrFail($id);
            $inventory->ecommerce_status = 'not_deployed';
            $inventory->save();

            return response()->json([
                'success' => true,
                'message' => 'Deployment request rejected.',
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject deployment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}