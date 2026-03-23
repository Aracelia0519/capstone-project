<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\OperationDistributor\InventoryLog;
use App\Models\OperationDistributor\ProcurementReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;

class ArrivedItemController extends Controller
{
    /**
     * Check RBAC Permissions for Arrived Items Module (Level-Based)
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

        // Managers and Operational Distributors
        $distributorId = $this->getDistributorId($user);
        if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
            return [
                'has_access' => true,
                'distributor_id' => $distributorId,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
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
                        ->where('permission_key', 'ec_arrived_item') // Precise permission key
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

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') {
            $model = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'hr_manager') {
            $model = DB::table('hr_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'finance_manager') {
            $model = DB::table('finance_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'employee') {
            $model = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        return $user->id;
    }

    /**
     * Get all arrived (delivered) items pending inventory movement
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized access to arrived items'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            // Fetch requests that are 'delivered' and NOT YET moved to inventory
            $query = ProcurementRequest::where('status', 'delivered')
                ->where('moved_to_inventory', false);

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $distributorId);
            }

            $arrivedItems = $query->orderBy('delivered_at', 'desc')->get();

            // Fetch arrival proofs safely and map product
            $arrivedItems->map(function ($item) {
                // Determine Proof Path
                $delivery = DB::table('supplier_deliveries')
                    ->where('procurement_request_id', $item->id)
                    ->first();
                $item->arrival_proof_path = $delivery ? $delivery->arrival_proof_path : null;

                // Decode raw material details safely to an object/array
                if (is_string($item->raw_material_details)) {
                    $item->raw_material_details = json_decode($item->raw_material_details, true);
                }

                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $arrivedItems,
                'permissions' => $accessData['permissions']
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch arrived items.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all products flagged "To Be Returned" sitting in the warehouse
     */
    public function getReturns(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized access'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            $query = ProcurementReturn::with(['procurementRequest', 'supplier']);

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $distributorId);
            }

            $returns = $query->orderBy('created_at', 'desc')->get();

            // Safely map and decode raw material details
            $formattedReturns = $returns->map(function ($ret) {
                if ($ret->procurementRequest && is_string($ret->procurementRequest->raw_material_details)) {
                    $ret->procurementRequest->raw_material_details = json_decode($ret->procurementRequest->raw_material_details, true);
                }
                
                // Get the latest delivery associated with this procurement request
                // Since the original was already delivered, the latest delivery is the replacement
                $latestDelivery = DB::table('supplier_deliveries')
                    ->where('procurement_request_id', $ret->procurement_request_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                // Return everything needed including the NEW replacement paths!
                return [
                    'id' => $ret->id,
                    'procurement_request_id' => $ret->procurement_request_id,
                    'procurement_request' => $ret->procurementRequest,
                    'supplier' => $ret->supplier,
                    'quantity_returned' => $ret->quantity_returned,
                    'reason' => $ret->reason,
                    'status' => $ret->status,
                    'rejection_reason' => $ret->rejection_reason,
                    'proof_image_path' => $ret->proof_image_path,
                    'replacement_receipt_path' => $ret->replacement_receipt_path,
                    'replacement_proof_path' => $ret->replacement_proof_path,
                    'replacement_arrival_proof_path' => $latestDelivery ? $latestDelivery->arrival_proof_path : null, // Fetch Arrival Proof
                    'created_at' => $ret->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedReturns,
                'permissions' => $accessData['permissions']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch returns.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark an item as moved to inventory and increment stock
     */
    public function moveToInventory(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // Requires MANAGE level to adjust warehouse operational inventory
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized to move items to inventory'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            $procurement = ProcurementRequest::where('id', $id)
                ->where('status', 'delivered')
                ->where('moved_to_inventory', false)
                ->first();

            if (!$procurement) {
                return response()->json(['message' => 'Item not found or already moved to inventory.'], 404);
            }

            if ($user->role !== 'admin' && $procurement->distributor_id !== $distributorId) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            DB::beginTransaction();

            // 1. Try to map to an existing Product or create a connection
            $productId = $procurement->product_id;
            
            if (!$productId && $procurement->raw_material_details) {
                $details = is_string($procurement->raw_material_details) 
                           ? json_decode($procurement->raw_material_details, true) 
                           : $procurement->raw_material_details;
                
                // Extremely simple mapping: Try to find a product with the exact same name/sku
                $existingProduct = \App\Models\Distributor\Product::where('distributor_id', $distributorId)
                    ->where(function($q) use ($details, $procurement) {
                        if (isset($details['sku_code'])) {
                            $q->where('sku_code', $details['sku_code']);
                        } else {
                            $q->where('name', $procurement->product_name);
                        }
                    })->first();

                if ($existingProduct) {
                    $productId = $existingProduct->id;
                } else {
                    // Create a new product entry in EC master catalog
                    $newProduct = \App\Models\Distributor\Product::create([
                        'distributor_id' => $distributorId,
                        'name' => $procurement->product_name,
                        'description' => $details['description'] ?? 'Procured Item',
                        'category' => $procurement->category,
                        'type' => $details['type'] ?? 'Standard',
                        'size' => $details['size'] ?? 'Standard',
                        'color_code' => $details['color_code'] ?? null,
                        'sku_code' => $details['sku_code'] ?? null,
                        'price' => $procurement->unit_price, // Fallback price
                        'image_url' => $details['image_url'] ?? null,
                        'is_active' => true
                    ]);
                    $productId = $newProduct->id;
                }

                // Attach the resolved product_id back to the procurement record
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

    /**
     * Mark a REPLACEMENT as moved to inventory and increment stock
     */
    public function moveReplacementToInventory(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // Requires MANAGE level to adjust warehouse operational inventory
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized to move items to inventory'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            $returnReq = ProcurementReturn::with('procurementRequest')
                ->where('id', $id)
                ->where('status', 'delivered')
                ->first();

            if (!$returnReq) {
                return response()->json(['message' => 'Replacement not found or not yet delivered.'], 404);
            }

            if ($user->role !== 'admin' && $returnReq->distributor_id !== $distributorId) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            DB::beginTransaction();

            $procurement = $returnReq->procurementRequest;
            $productId = $procurement->product_id;
            
            // Map product just in case it doesn't exist
            if (!$productId && $procurement->raw_material_details) {
                $details = is_string($procurement->raw_material_details) 
                           ? json_decode($procurement->raw_material_details, true) 
                           : $procurement->raw_material_details;
                
                $existingProduct = \App\Models\Distributor\Product::where('distributor_id', $distributorId)
                    ->where(function($q) use ($details, $procurement) {
                        if (isset($details['sku_code'])) {
                            $q->where('sku_code', $details['sku_code']);
                        } else {
                            $q->where('name', $procurement->product_name);
                        }
                    })->first();

                if ($existingProduct) {
                    $productId = $existingProduct->id;
                } else {
                    $newProduct = \App\Models\Distributor\Product::create([
                        'distributor_id' => $distributorId,
                        'name' => $procurement->product_name,
                        'description' => $details['description'] ?? 'Procured Item',
                        'category' => $procurement->category,
                        'type' => $details['type'] ?? 'Standard',
                        'size' => $details['size'] ?? 'Standard',
                        'color_code' => $details['color_code'] ?? null,
                        'sku_code' => $details['sku_code'] ?? null,
                        'price' => $procurement->unit_price,
                        'image_url' => $details['image_url'] ?? null,
                        'is_active' => true
                    ]);
                    $productId = $newProduct->id;
                }
                $procurement->product_id = $productId;
                $procurement->save();
            }

            // Increment Inventory
            if ($productId) {
                $inventory = DistributorInventory::firstOrCreate(
                    ['distributor_id' => $distributorId, 'product_id' => $productId],
                    ['quantity' => 0]
                );
                $inventory->quantity += $returnReq->quantity_returned;
                $inventory->save();

                InventoryLog::create([
                    'distributor_id' => $distributorId,
                    'product_id' => $productId,
                    'procurement_request_id' => $procurement->id,
                    'quantity_added' => $returnReq->quantity_returned,
                    'notes' => 'Received from Supplier Replacement'
                ]);
            }

            // Mark return as fully completed
            $returnReq->status = 'completed';
            $returnReq->save();

            DB::commit();
            return response()->json(['message' => 'Successfully moved replacement to inventory.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to move replacement to inventory.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process a return for an arrived item
     */
    public function returnItem(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // Requires MANAGE level to process operational returns
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json(['message' => 'Unauthorized to process returns'], 403);
            }

            $request->validate([
                'quantity_returned' => 'required|integer|min:1',
                'reason' => 'required|string',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
            ]);

            $distributorId = $accessData['distributor_id'];

            $procurement = ProcurementRequest::where('id', $id)
                ->where('status', 'delivered')
                ->where('moved_to_inventory', false)
                ->first();

            if (!$procurement) {
                return response()->json(['message' => 'Item not found or already moved to inventory.'], 404);
            }

            if ($request->quantity_returned > $procurement->quantity) {
                return response()->json(['message' => 'Return quantity cannot exceed received quantity.'], 400);
            }

            DB::beginTransaction();

            $imagePath = $request->file('proof_image')->store('operation-distributor/returns', 'public');

            // Log the return
            $returnRecord = ProcurementReturn::create([
                'procurement_request_id' => $procurement->id,
                'distributor_id' => $distributorId,
                'supplier_id' => $procurement->supplier_id,
                'quantity_returned' => $request->quantity_returned,
                'reason' => $request->reason,
                'proof_image_path' => $imagePath,
                'status' => 'pending'
            ]);

            // Deduct the returned quantity from the procurement request so only accepted items are moved to inventory
            $procurement->quantity -= $request->quantity_returned;

            // If everything is returned, hide it from the arrived items screen by acting like it was "moved" (processed)
            if ($procurement->quantity == 0) {
                $procurement->status = 'returned';
                $procurement->moved_to_inventory = true; 
            }

            $procurement->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Return processed successfully.',
                'remaining_quantity' => $procurement->quantity,
                'status' => $procurement->status
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process return: ' . $e->getMessage()], 500);
        }
    }
}