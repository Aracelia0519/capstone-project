<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaintInventoryController extends Controller
{
    /**
     * Helper to get the correct distributor ID based on role
     */
    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    /**
     * Fetch all inventory items for the distributor
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $distributorId = $this->getDistributorId($user);

            // Fetch from distributor_inventories and join with distributor_products
            $inventory = DB::table('distributor_inventories')
                ->join('distributor_products', 'distributor_inventories.product_id', '=', 'distributor_products.id')
                ->where('distributor_inventories.distributor_id', $distributorId)
                ->whereNull('distributor_products.deleted_at') // Ensure product isn't soft-deleted
                ->select(
                    'distributor_products.id',
                    'distributor_products.name',
                    'distributor_products.category as brand',
                    'distributor_products.type as colorBase',
                    'distributor_products.color_code as colorHex',
                    'distributor_inventories.quantity',
                    'distributor_products.price'
                )
                ->orderBy('distributor_products.name', 'asc')
                ->get();

            // Map the data to match the frontend requirements
            $formattedInventory = $inventory->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand ?: 'Uncategorized',
                    'colorBase' => $item->colorBase ?: 'Standard',
                    'colorHex' => $item->colorHex ?: '#CCCCCC', // Default hex if null
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedInventory
            ]);

        } catch (\Exception $e) {
            Log::error('Paint Inventory Fetch Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch inventory data.'
            ], 500);
        }
    }
}