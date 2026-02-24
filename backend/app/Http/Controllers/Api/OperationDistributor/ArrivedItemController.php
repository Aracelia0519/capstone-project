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
        $arrivedItems = ProcurementRequest::with(['product'])
            ->where('distributor_id', $distributorId)
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

            // 1. Mark the request as moved
            $procurement->moved_to_inventory = true;
            $procurement->save();

            // 2. Add to the separate DistributorInventory table
            if ($procurement->product_id) {
                
                // Fetch existing inventory record, or create a new one with 0 quantity
                $inventory = DistributorInventory::firstOrCreate(
                    [
                        'distributor_id' => $distributorId,
                        'product_id' => $procurement->product_id
                    ],
                    ['quantity' => 0]
                );

                // Increment the quantity
                $inventory->quantity += $procurement->quantity;
                $inventory->save();

                // 3. Create Audit Log
                InventoryLog::create([
                    'distributor_id' => $distributorId,
                    'product_id' => $procurement->product_id,
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