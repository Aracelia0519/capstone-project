<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\OperationDistributor\InventoryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;

class ArrivedItemController extends Controller
{
    /**
     * Check RBAC Permissions for Arrived Items Module
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
                        ->where('permission_key', 'ec_arrived_item') // Permission key for this module
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

    public function index(Request $request)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to view arrived items.'], 403);
        }

        $distributorId = $accessData['distributor_id'];

        $query = ProcurementRequest::where('status', 'delivered')
            ->where('moved_to_inventory', false)
            ->orderBy('delivered_at', 'desc');

        if ($user->role !== 'admin') {
            $query->where('distributor_id', $distributorId);
        }

        $arrivedItems = $query->get();

        // Attach the arrival proof from supplier_deliveries
        foreach ($arrivedItems as $item) {
            $delivery = DB::table('supplier_deliveries')
                ->where('procurement_request_id', $item->id)
                ->first();
                
            $item->arrival_proof_path = $delivery ? $delivery->arrival_proof_path : null;
        }

        return response()->json([
            'data' => $arrivedItems,
            'permissions' => $accessData['permissions']
        ]);
    }

    public function moveToInventory(Request $request, $id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_update');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to update inventory.'], 403);
        }

        $distributorId = $accessData['distributor_id'];
        
        DB::beginTransaction();
        try {
            $query = ProcurementRequest::where('id', $id)
                ->where('status', 'delivered')
                ->where('moved_to_inventory', false);

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $distributorId);
            }

            $procurement = $query->firstOrFail();

            // 1. Determine Product ID mapping from Supplier Raw Material to Distributor Product
            $productId = $procurement->product_id;
            
            if (!$productId) {
                // Check if the distributor already has this product registered
                $product = DB::table('distributor_products')
                    ->where('distributor_id', $distributorId)
                    ->where('name', $procurement->product_name)
                    ->first();
                    
                if ($product) {
                    $productId = $product->id;
                } else {
                    // Create a new Distributor Product using the Supplier Raw Material details
                    $rawMaterial = $procurement->raw_material_details;
                    
                    $productId = DB::table('distributor_products')->insertGetId([
                        'distributor_id' => $distributorId,
                        'category' => $procurement->category,
                        'type' => $rawMaterial ? $rawMaterial->type : 'Standard',
                        'name' => $procurement->product_name,
                        'sku_code' => $rawMaterial && $rawMaterial->sku_code ? $rawMaterial->sku_code : 'SKU-' . strtoupper(substr(uniqid(), -6)),
                        'size' => $rawMaterial ? $rawMaterial->size : 'Standard',
                        'color_code' => $rawMaterial ? $rawMaterial->color_code : null,
                        'price' => $procurement->unit_price * 1.30, // Default 30% markup for client sale
                        'cost' => $procurement->unit_price,
                        'description' => $rawMaterial ? $rawMaterial->description : 'Procured from supplier: ' . $procurement->supplier,
                        'image_url' => $rawMaterial ? $rawMaterial->image_url : null,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // Update procurement with the official mapped product_id
                $procurement->product_id = $productId;
            }

            // 2. Mark the request as moved
            $procurement->moved_to_inventory = true;
            $procurement->save();

            // 3. Add to the separate DistributorInventory table
            if ($productId) {
                // Fetch existing inventory record, or create a new one with 0 quantity
                $inventory = DistributorInventory::firstOrCreate(
                    [
                        'distributor_id' => $distributorId,
                        'product_id' => $productId
                    ],
                    ['quantity' => 0]
                );

                // Increment the quantity
                $inventory->quantity += $procurement->quantity;
                $inventory->save();

                // 4. Create Audit Log
                InventoryLog::create([
                    'distributor_id' => $distributorId,
                    'product_id' => $productId,
                    'procurement_request_id' => $procurement->id,
                    'quantity_added' => $procurement->quantity,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Successfully moved to inventory.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to move to inventory.', 'error' => $e->getMessage()], 500);
        }
    }
}