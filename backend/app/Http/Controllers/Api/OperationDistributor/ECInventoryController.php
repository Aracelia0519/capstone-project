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
     * Check RBAC Permissions for Inventory Module
     */
    private function checkAccess($user, $action = 'can_view')
    {
        // Admin
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // Distributor
        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // HR Manager
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager && $hrManager->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
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
                    'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
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
                        if ($action === 'can_create' && $access->can_create) $hasAccess = true;
                        if ($action === 'can_update' && $access->can_update) $hasAccess = true;
                        if ($action === 'can_delete' && $access->can_delete) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_create' => (bool)$access->can_create,
                                    'can_update' => (bool)$access->can_update,
                                    'can_delete' => (bool)$access->can_delete,
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
            'permissions' => ['can_view' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false]
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
     * Request a product to be deployed to the E-Commerce store.
     */
    public function requestDeployment($id)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_update');

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
}