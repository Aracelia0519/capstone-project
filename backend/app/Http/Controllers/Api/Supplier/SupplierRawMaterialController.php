<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\RestockRawMaterialRequest;
use App\Http\Requests\Supplier\StoreRawMaterialRequest;
use App\Http\Requests\Supplier\UpdateRawMaterialRequest;
use App\Models\Supplier\SupplierRawMaterial;
use App\Models\Supplier\SupplierRawMaterialBatch;
use App\Support\Inventory\BatchRules;
use App\Support\Inventory\BatchInventoryService;
use App\Support\Inventory\Exceptions\InsufficientStock;
use App\Support\Inventory\Exceptions\InvalidExpirationDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Supplier product catalogue, now batch-aware.
 *
 * New endpoints
 * ─────────────
 *   GET    /api/supplier/raw-materials                     list, with batch rollups
 *   POST   /api/supplier/raw-materials                     create product + first batch
 *   POST   /api/supplier/raw-materials/{id}                update product details
 *   DELETE /api/supplier/raw-materials/{id}                soft delete
 *   GET    /api/supplier/raw-materials/{id}/batches        list that product's lots
 *   POST   /api/supplier/raw-materials/{id}/restock         add a new lot
 *   POST   /api/supplier/raw-materials/{id}/archive-expired archive every expired lot
 *   POST   /api/supplier/raw-materials/batches/{batchId}/archive
 *   GET    /api/supplier/raw-materials/rules               the category/expiry rulebook
 */
class SupplierRawMaterialController extends Controller
{
    public function __construct(
        private readonly BatchInventoryService $batches
    ) {
    }

    // =================================================================
    // Read
    // =================================================================

    public function index()
    {
        $materials = SupplierRawMaterial::where('user_id', Auth::id())
            ->with(['liveBatches' => fn ($q) => $q->orderByRaw(BatchRules::fefoOrderBy())])
            ->orderBy('created_at', 'desc')
            ->get();

        // Older rows predate the `weight` column default.
        $materials->each(function ($material) {
            $material->weight = $material->weight ?? 10.00;
        });

        return response()->json($materials);
    }

    /**
     * The rulebook, so the frontend never hardcodes a duplicate of it.
     */
    public function rules()
    {
        return response()->json([
            'minimum_shelf_life_years' => BatchRules::MINIMUM_SHELF_LIFE_YEARS,
            'minimum_expiration_date'  => BatchRules::minimumExpirationDate()->toDateString(),
            'non_perishable_categories' => BatchRules::NON_PERISHABLE_CATEGORIES,
            'message'                  => BatchRules::minimumRuleMessage(),
        ]);
    }

    /**
     * Every lot of one product, soonest expiry first.
     */
    public function batches($id)
    {
        $material = $this->findOwned($id);

        $batches = $material->batches()
            ->orderByRaw(BatchRules::fefoOrderBy())
            ->get();

        return response()->json([
            'success'    => true,
            'material'   => $material->only(['id', 'name', 'category', 'quantity', 'reserved_quantity']),
            'batches'    => $batches,
            'expired_count' => $batches->where('is_expired', true)->count(),
        ]);
    }

    // =================================================================
    // Create / update
    // =================================================================

    public function store(StoreRawMaterialRequest $request)
    {
        $attributes = $request->materialAttributes();
        $attributes['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $attributes['image_url'] = $request->file('image')->store('supplier/raw_materials', 'public');
        }

        try {
            $material = DB::transaction(function () use ($attributes, $request) {
                $material = SupplierRawMaterial::create($attributes);

                // The opening lot. Quantity and expiration date are both mandatory
                // for perishable categories; BatchRules decides which applies.
                $this->batches->addBatch(
                    $material,
                    (int) $request->input('quantity', 0),
                    $request->input('expiration_date'),
                    Auth::id(),
                    $request->input('batch_code')
                );

                return $material;
            });
        } catch (InvalidExpirationDate $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => ['expiration_date' => [$e->getMessage()]],
            ], 422);
        } catch (InsufficientStock $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Material added successfully',
            'data'    => $material->fresh(['liveBatches']),
        ], 201);
    }

    public function update(UpdateRawMaterialRequest $request, $id)
    {
        $material = $this->findOwned($id);

        $validated = $request->materialAttributes();

        if ($request->hasFile('image')) {
            if ($material->image_url && Storage::disk('public')->exists($material->image_url)) {
                Storage::disk('public')->delete($material->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('supplier/raw_materials', 'public');
        }

        $material->update($validated);

        return response()->json([
            'message' => 'Material updated successfully',
            'data'    => $material->fresh(['liveBatches']),
        ]);
    }

    /**
     * Add a new lot of an existing product.
     *
     * The supplier states the quantity and the expiration date for THIS delivery.
     * Nothing about the product itself changes, and the running total grows by
     * exactly the amount added.
     */
    public function restock(RestockRawMaterialRequest $request, $id)
    {
        $material = $this->findOwned($id);

        try {
            $batch = $this->batches->addBatch(
                $material,
                (int) $request->quantity,
                $request->input('expiration_date'),
                Auth::id(),
                $request->input('batch_code')
            );
        } catch (InvalidExpirationDate $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => ['expiration_date' => [$e->getMessage()]],
            ], 422);
        }

        return response()->json([
            'message'  => sprintf('%d unit(s) added as batch %s.', $batch->quantity, $batch->batch_code),
            'batch'    => $batch,
            'material' => $material->fresh(['liveBatches']),
        ], 201);
    }

    public function destroy($id)
    {
        $material = $this->findOwned($id);

        if ($material->image_url && Storage::disk('public')->exists($material->image_url)) {
            Storage::disk('public')->delete($material->image_url);
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted successfully']);
    }

    // =================================================================
    // Archiving
    // =================================================================

    /**
     * Archive a single lot.
     *
     * Used for the "Move to Archive" button on one batch row.
     */
    public function archiveBatch(Request $request, $batchId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $batch = SupplierRawMaterialBatch::where('user_id', Auth::id())
            ->findOrFail($batchId);

        if ((int) $batch->quantity > 0 && ! $batch->is_expired) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'Batch %s still has %d unexpired unit(s). Only expired stock can be archived; '
                    . 'return it to the distributor instead.',
                    $batch->batch_code,
                    $batch->quantity
                ),
            ], 422);
        }

        $this->batches->archiveSupplierBatch($batch, Auth::id(), $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => sprintf('Batch %s moved to archive.', $batch->batch_code),
            'batch'   => $batch->fresh(),
        ]);
    }

    /**
     * Archive EVERY expired lot of one product.
     *
     * This is the "Move to Archive" button on a product card. Expired stock cannot
     * be procured, so pulling it out is what lets a product look healthy again once
     * fresh stock arrives.
     */
    public function archiveExpired(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $material = $this->findOwned($id);

        $count = $this->batches->archiveExpiredSupplierBatches(
            Auth::id(),
            Auth::id(),
            $request->input('reason')
        );

        return response()->json([
            'success'  => true,
            'archived' => $count,
            'message'  => $count === 0
                ? 'No expired batches to archive.'
                : sprintf('%d expired batch(es) moved to archive.', $count),
            'material' => $material->fresh(['liveBatches']),
        ]);
    }

    /**
     * Archive every expired lot across the whole catalogue.
     */
    public function archiveAllExpired(Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $count = $this->batches->archiveExpiredSupplierBatches(
            Auth::id(),
            Auth::id(),
            $request->input('reason')
        );

        return response()->json([
            'success'  => true,
            'archived' => $count,
            'message'  => $count === 0
                ? 'Nothing in your catalogue has expired.'
                : sprintf('%d expired batch(es) moved to archive.', $count),
        ]);
    }

    /**
     * Products a distributor is still allowed to buy.
     *
     * Anything fully expired is excluded, which is what "expired products cannot be
     * procured" means in practice.
     */
    public function procurable()
    {
        $materials = SupplierRawMaterial::where('user_id', Auth::id())
            ->procurable()
            ->with(['sellableBatches' => fn ($q) => $q->orderByRaw(BatchRules::fefoOrderBy())])
            ->get();

        return response()->json($materials);
    }

    // =================================================================

    private function findOwned($id): SupplierRawMaterial
    {
        return SupplierRawMaterial::where('user_id', Auth::id())->findOrFail($id);
    }
}
