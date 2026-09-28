<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\DistributorInventory;
use App\Models\OperationDistributor\DistributorInventoryBatch;
use App\Support\Inventory\BatchRules;
use App\Support\Inventory\BatchInventoryService;
use App\Support\Inventory\Exceptions\InvalidExpirationDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;
use App\Events\InventoryUpdated;

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

                // ── Batch detail ─────────────────────────────────────────
                // The parent row's `quantity` is the product total. How much of
                // that can actually be SOLD is the sum of the unexpired batches,
                // which is what the store and the checkout draw from. Exposing
                // both side by side is what stops someone reading 100 units on
                // screen while only 40 are purchasable.
                $batchQuery = $inv->batches()
                    ->orderByRaw(BatchRules::fefoOrderBy())
                    ->get();

                $sellableQty = (int) $batchQuery->sum(
                    fn (DistributorInventoryBatch $b) => $b->is_sellable ? (int) $b->quantity : 0
                );
                $expiredQty  = (int) $batchQuery->sum(
                    fn (DistributorInventoryBatch $b) => ($b->is_expired && ! $b->is_archived) ? (int) $b->quantity : 0
                );

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
                    'available_quantity' => $sellableQty,
                    'expired_quantity' => $expiredQty,
                    'is_archived' => (bool) $inv->is_archived,
                    'is_expired' => $sellableQty === 0 && (int) $inv->quantity > 0,
                    'earliest_expiration' => $inv->expiration_date
                        ? $inv->expiration_date->toDateString()
                        : null,
                    'batch_count' => $batchQuery->count(),
                    'batches' => $batchQuery->map(fn (DistributorInventoryBatch $b) => [
                        'id' => $b->id,
                        'batch_code' => $b->batch_code,
                        'quantity' => (int) $b->quantity,
                        'expiration_date' => $b->expiration_date?->toDateString(),
                        'is_archived' => (bool) $b->is_archived,
                        'is_expired' => $b->is_expired,
                        'is_sellable' => $b->is_sellable,
                        'health' => $b->health,
                        'days_until_expiry' => $b->days_until_expiry,
                        'received_at' => $b->received_at?->toDateString(),
                    ])->values(),
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
                'permissions' => $accessData['permissions'],
                'distributor_id' => $distributorId,
                'is_admin' => $user->role === 'admin'
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

    // =================================================================
    // BATCH MANAGEMENT
    // =================================================================

    /**
     * Every lot the distributor holds, soonest expiry first.
     *
     * `?expired=1` narrows to lots that have passed their date, which is the
     * queue the "Move to Archive" button is normally driven from.
     */
    public function batches(Request $request)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (! $accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view batches.'], 403);
        }

        $query = DistributorInventoryBatch::with(['product', 'inventory'])
            ->orderByRaw(BatchRules::fefoOrderBy());

        if ($user->role !== 'admin') {
            $query->where('distributor_id', $accessData['distributor_id']);
        }

        if ($request->boolean('expired')) {
            $query->whereNotNull('expiration_date')
                ->whereDate('expiration_date', '<', now()->toDateString());
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $batches = $query->get()->map(fn (DistributorInventoryBatch $b) => [
            'id' => $b->id,
            'inventory_id' => $b->distributor_inventory_id,
            'product_id' => $b->product_id,
            'product_name' => $b->product?->name,
            'batch_code' => $b->batch_code,
            'quantity' => (int) $b->quantity,
            'expiration_date' => $b->expiration_date?->toDateString(),
            'is_archived' => (bool) $b->is_archived,
            'is_expired' => $b->is_expired,
            'is_sellable' => $b->is_sellable,
            'health' => $b->health,
            'days_until_expiry' => $b->days_until_expiry,
            // Paired with days_until_expiry so an expired lot can be shown as
            // "12d ago" without the client having to know that the sign of
            // days_until_expiry is what carries that meaning.
            'days_expired' => $b->is_expired && $b->days_until_expiry !== null
                ? abs((int) $b->days_until_expiry)
                : null,
            'received_at' => $b->received_at?->toDateString(),
            'archive_reason' => $b->archive_reason,
        ]);

        return response()->json([
            'success' => true,
            'data' => $batches,
            'expired_count' => $batches->where('is_expired', true)->where('is_archived', false)->count(),
        ]);
    }

    /**
     * Lots that have passed their expiration date and are still active.
     *
     * These are the ones a distributor must clear out: while they sit here they
     * inflate the on-hand figure while being impossible to sell.
     */
    public function expired()
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (! $accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view expired stock.'], 403);
        }

        $query = DistributorInventoryBatch::with(['product', 'inventory'])
            ->where('is_archived', false)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->orderBy('expiration_date');

        if ($user->role !== 'admin') {
            $query->where('distributor_id', $accessData['distributor_id']);
        }

        $batches = $query->get()->map(fn (DistributorInventoryBatch $b) => [
            'id' => $b->id,
            'inventory_id' => $b->distributor_inventory_id,
            'product_id' => $b->product_id,
            'product_name' => $b->product?->name,
            'sku_code' => $b->product?->sku_code,
            'batch_code' => $b->batch_code,
            'quantity' => (int) $b->quantity,
            'expiration_date' => $b->expiration_date?->toDateString(),
            'days_until_expiry' => $b->days_until_expiry,
            'days_expired' => abs((int) $b->days_until_expiry),
        ]);

        return response()->json([
            'success' => true,
            'data' => $batches,
            'count' => $batches->count(),
            'total_quantity' => (int) $batches->sum('quantity'),
        ]);
    }

    /**
     * "Move to Archive" on a single lot.
     *
     * Refuses while the lot is still within date, because archiving fresh stock
     * would quietly destroy sellable inventory. Expired lots pass straight
     * through.
     */
    public function archiveBatch(Request $request, $batchId)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_manage');

        if (! $accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to archive stock.'], 403);
        }

        $request->validate(['reason' => 'nullable|string|max:255']);

        $batch = DistributorInventoryBatch::with('product')->findOrFail($batchId);

        if ($user->role !== 'admin' && $batch->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if (! $batch->is_expired) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'Batch %s is not expired (expires %s). Only expired stock can be archived.',
                    $batch->batch_code,
                    $batch->expiration_date?->toDateString() ?? 'never'
                ),
            ], 422);
        }

        app(BatchInventoryService::class)->archiveDistributorBatch(
            $batch,
            $user->id,
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => sprintf(
                'Batch %s moved to archive. %d unit(s) left the active supply chain.',
                $batch->batch_code,
                (int) $batch->quantity
            ),
            'batch' => $batch->fresh(),
        ]);
    }

    /**
     * Archive every expired lot in one action.
     *
     * The bulk version of the button above, for the monthly sweep.
     */
    public function archiveAllExpired(Request $request)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_manage');

        if (! $accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to archive stock.'], 403);
        }

        $request->validate(['reason' => 'nullable|string|max:255']);

        $target = $user->role === 'admin'
            ? null
            : $accessData['distributor_id'];

        $candidates = DistributorInventoryBatch::query()
            ->where('is_archived', false)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString());

        if ($target !== null) {
            $candidates->where('distributor_id', $target);
        }

        // Admin sweeps every distributor, so it is done per distributor to keep
        // the log entries attributed correctly.
        $ids = $candidates->pluck('distributor_id')->unique();

        $total = 0;
        foreach ($ids as $distributorId) {
            $total += app(BatchInventoryService::class)
                ->archiveExpiredDistributorBatches(
                    $distributorId,
                    $user->id,
                    $request->input('reason')
                );
        }

        return response()->json([
            'success'  => true,
            'archived' => $total,
            'message'  => $total === 0
                ? 'No expired stock found.'
                : sprintf('%d expired batch(es) moved to archive.', $total),
        ]);
    }

    /**
     * Book a delivery into stock as a new lot.
     *
     * Requires the expiration date, so stock cannot enter the warehouse without
     * a known shelf life.
     */
    public function receiveBatch(Request $request, $inventoryId)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_manage');

        if (! $accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to receive stock.'], 403);
        }

        $validated = $request->validate([
            'quantity'        => 'required|integer|min:1',
            'expiration_date' => 'nullable|date_format:Y-m-d',
            'batch_code'      => 'nullable|string|max:64',
        ]);

        $inventory = DistributorInventory::findOrFail($inventoryId);

        if ($user->role !== 'admin' && $inventory->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $batch = app(BatchInventoryService::class)->receiveBatch(
                $inventory->distributor_id,
                $inventory->product_id,
                (int) $validated['quantity'],
                $validated['expiration_date'] ?? null,
                $user->id,
                null,
                null,
                $validated['batch_code'] ?? null
            );
        } catch (InvalidExpirationDate $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => ['expiration_date' => [$e->getMessage()]],
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => sprintf('%d unit(s) received as batch %s.', (int) $validated['quantity'], $batch->batch_code),
            'batch'     => $batch,
            'inventory' => $inventory->fresh(['batches']),
        ], 201);
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

            // Broadcast the change
            event(new InventoryUpdated($inventory->distributor_id));

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

            // Broadcast the change
            event(new InventoryUpdated($inventory->distributor_id));

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

            // Broadcast the change
            event(new InventoryUpdated($inactiveItem->distributor_id));

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