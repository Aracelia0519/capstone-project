<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;

class ECInventoryController extends Controller
{
    /**
     * Check RBAC Permissions for Inventory Module (Level-Based)
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
                        ->where('permission_key', 'ec_inventory') // Permission key for this module
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

    /**
     * Display a listing of the inventory for the Operational Distributor.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');

            if (!$accessData['has_access']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to view inventory.'
                ], 403);
            }
            
            $distributorId = $accessData['distributor_id'];

            // Fetch inventories alongside the product details
            $query = DistributorInventory::with('product');

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $distributorId);
            }

            $inventories = $query->get();

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
                'data' => $formatted,
                'permissions' => $accessData['permissions']
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
     * Display a listing of INACTIVE inventory items.
     */
    public function getInactive()
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');

            if (!$accessData['has_access']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view inactive inventory.'
                ], 403);
            }
            
            $distributorId = $accessData['distributor_id'];

            $query = DB::table('inactive_distributor_inventories')
                ->join('distributor_products', 'inactive_distributor_inventories.product_id', '=', 'distributor_products.id')
                ->select(
                    'inactive_distributor_inventories.id',
                    'inactive_distributor_inventories.quantity',
                    'inactive_distributor_inventories.previous_ecommerce_status',
                    'distributor_products.id as product_id',
                    'distributor_products.name',
                    'distributor_products.sku_code',
                    'distributor_products.category',
                    'distributor_products.type',
                    'distributor_products.size',
                    'distributor_products.color_code',
                    'distributor_products.price',
                    'distributor_products.min_stock_level',
                    'distributor_products.max_stock_level',
                    'distributor_products.description',
                    'distributor_products.image_url'
                );

            if ($user->role !== 'admin') {
                $query->where('inactive_distributor_inventories.distributor_id', $distributorId);
            }

            $inactiveItems = $query->get()->map(function ($item) {
                // Format image URL properly
                $imageUrl = $item->image_url;
                if ($imageUrl && !filter_var($imageUrl, FILTER_VALIDATE_URL) && !str_starts_with($imageUrl, 'data:')) {
                    $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                }
                
                $item->image_url = $imageUrl;
                $item->ecommerce_status = 'inactive'; // Override status for UI consistency
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $inactiveItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load inactive inventory',
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
            $user = Auth::user();
            // Requires Manage level
            $accessData = $this->checkAccess($user, 'can_manage');

            if (!$accessData['has_access']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to request deployment.'
                ], 403);
            }

            // Find the exact inventory record
            $inventory = DistributorInventory::findOrFail($id);

            if ($user->role !== 'admin' && $inventory->distributor_id !== $accessData['distributor_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to deploy this inventory item.'
                ], 403);
            }

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

    /**
     * Move a specified quantity of an active inventory item to the Inactive table.
     */
    public function moveToInactive(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // Requires Manage level
            $accessData = $this->checkAccess($user, 'can_manage');

            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Access Denied.'], 403);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $qtyToDeactivate = $request->quantity;
            $inventory = DistributorInventory::findOrFail($id);

            if ($user->role !== 'admin' && $inventory->distributor_id !== $accessData['distributor_id']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to deactivate this item.'], 403);
            }

            if ($qtyToDeactivate > $inventory->quantity) {
                return response()->json(['success' => false, 'message' => 'Quantity exceeds available stock.'], 400);
            }

            DB::beginTransaction();

            // Check if an inactive record already exists for this specific product
            $existingInactive = DB::table('inactive_distributor_inventories')
                ->where('distributor_id', $inventory->distributor_id)
                ->where('product_id', $inventory->product_id)
                ->first();

            if ($existingInactive) {
                // Increment the quantity in the existing inactive record
                DB::table('inactive_distributor_inventories')
                    ->where('id', $existingInactive->id)
                    ->update([
                        'quantity' => $existingInactive->quantity + $qtyToDeactivate,
                        'updated_at' => now()
                    ]);
            } else {
                // Insert a new inactive record
                DB::table('inactive_distributor_inventories')->insert([
                    'distributor_id' => $inventory->distributor_id,
                    'product_id' => $inventory->product_id,
                    'quantity' => $qtyToDeactivate,
                    'previous_ecommerce_status' => $inventory->ecommerce_status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Deduct the quantity from active inventory
            $inventory->quantity -= $qtyToDeactivate;

            if ($inventory->quantity <= 0) {
                $inventory->delete();
            } else {
                $inventory->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product quantity moved to inactive successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to deactivate product', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Move a specified quantity of an inactive product back to the Active inventory.
     */
    public function reactivate(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // Requires Manage level
            $accessData = $this->checkAccess($user, 'can_manage');

            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Access Denied.'], 403);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $qtyToReactivate = $request->quantity;
            $inactiveItem = DB::table('inactive_distributor_inventories')->where('id', $id)->first();

            if (!$inactiveItem) {
                return response()->json(['success' => false, 'message' => 'Inactive item not found.'], 404);
            }

            if ($user->role !== 'admin' && $inactiveItem->distributor_id !== $accessData['distributor_id']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to reactivate this item.'], 403);
            }

            if ($qtyToReactivate > $inactiveItem->quantity) {
                return response()->json(['success' => false, 'message' => 'Quantity exceeds inactive stock.'], 400);
            }

            DB::beginTransaction();

            // Check if active inventory record exists for this product
            $activeInventory = DistributorInventory::where('distributor_id', $inactiveItem->distributor_id)
                ->where('product_id', $inactiveItem->product_id)
                ->first();

            if ($activeInventory) {
                $activeInventory->quantity += $qtyToReactivate;
                $activeInventory->save();
            } else {
                DistributorInventory::create([
                    'distributor_id' => $inactiveItem->distributor_id,
                    'product_id' => $inactiveItem->product_id,
                    'quantity' => $qtyToReactivate,
                    'ecommerce_status' => $inactiveItem->previous_ecommerce_status ?? 'not_deployed'
                ]);
            }

            // Deduct the quantity from the inactive inventory
            $newInactiveQty = $inactiveItem->quantity - $qtyToReactivate;

            if ($newInactiveQty <= 0) {
                DB::table('inactive_distributor_inventories')->where('id', $id)->delete();
            } else {
                DB::table('inactive_distributor_inventories')
                    ->where('id', $id)
                    ->update([
                        'quantity' => $newInactiveQty,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product quantity successfully reactivated and restored.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reactivate product', 'error' => $e->getMessage()], 500);
        }
    }
}