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

            // Format image URLs if necessary before sending to frontend
            $inventories->transform(function ($inventory) {
                if ($inventory->product && $inventory->product->image_url) {
                    if (!filter_var($inventory->product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($inventory->product->image_url, 'data:')) {
                        $inventory->product->image_url = asset('storage/' . ltrim($inventory->product->image_url, '/'));
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
    public function deploy($id)
    {
        try {
            $inventory = DistributorInventory::where('distributor_id', Auth::id())->findOrFail($id);
            $inventory->ecommerce_status = 'deployed';
            $inventory->save();

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