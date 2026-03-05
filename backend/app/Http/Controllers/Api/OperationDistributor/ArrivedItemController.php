<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\OperationDistributor\InventoryLog;
use Illuminate\Support\Facades\DB;

class ArrivedItemController extends Controller
{
    private function getDistributorId(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'operational_distributor') {
            $op = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            return $op ? $op->parent_distributor_id : $user->id;
        }
        return $user->id;
    }

    public function index(Request $request)
    {
        $distributorId = $this->getDistributorId($request);

        // Fetch all delivered requests that haven't been moved to inventory yet
        $arrivedItems = ProcurementRequest::where('distributor_id', $distributorId)
            ->where('status', 'delivered')
            ->where('moved_to_inventory', false)
            ->orderBy('delivered_at', 'desc')
            ->get();

        // Attach the arrival proof from supplier_deliveries
        foreach ($arrivedItems as $item) {
            $delivery = DB::table('supplier_deliveries')
                ->where('procurement_request_id', $item->id)
                ->first();
                
            $item->arrival_proof_path = $delivery ? $delivery->arrival_proof_path : null;
        }

        return response()->json($arrivedItems);
    }

    public function moveToInventory(Request $request, $id)
    {
        $distributorId = $this->getDistributorId($request);
        
        DB::beginTransaction();
        try {
            $procurement = ProcurementRequest::where('id', $id)
                ->where('distributor_id', $distributorId)
                ->where('status', 'delivered')
                ->where('moved_to_inventory', false)
                ->firstOrFail();

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