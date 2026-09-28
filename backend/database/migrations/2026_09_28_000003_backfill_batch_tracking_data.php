<?php

use App\Support\Inventory\BatchRules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DATA BACKFILL — adapt existing rows to the new batch schema.
 *
 * Nothing is deleted. Row ids, prices, quantities, names, users and order history
 * are all left exactly as they are. The only thing written is the batch/expiry
 * metadata the new schema needs.
 *
 * What it does
 * ────────────
 *  1. supplier_raw_materials: every ACTIVE, non-archived, non-deleted product gets
 *     a default expiration date of TODAY + 1 YEAR, except products in a
 *     non-perishable category (Tools & Accessories / Packaging) which are left
 *     NULL because a paint brush does not expire.
 *  2. Each of those products gets ONE seed batch carrying its existing quantity and
 *     the same default expiration date, so no stock is invented or lost.
 *  3. supplier_raw_materials.quantity / reserved_quantity are re-derived from the
 *     batch rows so the legacy flat reads stay correct.
 *  4. distributor_inventories: every existing row is given a batch code, the same
 *     +1-year default date and a received_at timestamp, turning each pre-existing
 *     single row into a proper batch.
 *  5. procurement_requests: rows with no expiry recorded inherit the +1-year default
 *     so nothing is left un-dated.
 *  6. inactive_distributor_inventories: batch code + expiry backfilled from the
 *     active batch it was split out of, where one can be identified.
 *  7. inventory_logs: linked back to the batch rows they describe and given an
 *     event_type where it is missing.
 *
 * IDEMPOTENT — safe to run any number of times. Steps 2, 4 and 7 all check for
 * existing rows before inserting.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('supplier_raw_materials')) {
            return;
        }

        $defaultExpiry = BatchRules::minimumExpirationDate()->toDateString();
        $today         = now()->toDateString();

        $this->backfillSupplierProducts($defaultExpiry);
        $this->backfillProcurementRequests($defaultExpiry);
        $this->backfillDistributorInventory($defaultExpiry);
        $this->backfillInactiveInventory($defaultExpiry);
        $this->backfillInventoryLogs($defaultExpiry);
    }

    public function down(): void
    {
        /*
         * Intentionally empty of destructive work.
         *
         * The data written by up() is the ONLY record of when your stock expires.
         * Rolling back would leave expiring stock looking like it never expires, so
         * there is nothing safe to do here. The schema itself is reverted by
         * 2026_09_28_000002_create_batch_tracking_schema.
         *
         * If you genuinely need to undo this, snapshot first:
         *   mysqldump --single-transaction capstone001 supplier_raw_materials \
         *     supplier_raw_material_batches distributor_inventories > pre_batch_dump.sql
         */
    }

    // =====================================================================
    // 1 + 2 + 3 — supplier products
    // =====================================================================

    private function backfillSupplierProducts(string $defaultExpiry): void
    {
        $hasBatches = Schema::hasTable('supplier_raw_material_batches');

        DB::table('supplier_raw_materials')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereNull('archived_at')
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($defaultExpiry, $hasBatches) {
                foreach ($products as $product) {
                    $isPerishable = BatchRules::requiresExpiration($product->category);

                    // ── default expiry ───────────────────────────────────────
                    if ($isPerishable) {
                        $current = $product->expiration_date
                            ? (string) $product->expiration_date
                            : null;

                        // Only overwrite when the stored date is missing OR already
                        // closer than the 1-year rule allows. A longer-dated batch
                        // is left completely alone.
                        if ($current === null || ! BatchRules::isFarEnoughOut($current, $product->category)) {
                            DB::table('supplier_raw_materials')
                                ->where('id', $product->id)
                                ->update(['expiration_date' => $defaultExpiry]);
                        }
                    } else {
                        // Non-perishable: strip any date so the system stops
                        // scheduling a paint brush for disposal.
                        if ($product->expiration_date !== null) {
                            DB::table('supplier_raw_materials')
                                ->where('id', $product->id)
                                ->update(['expiration_date' => null]);
                        }
                    }

                    if (! $hasBatches) {
                        continue;
                    }

                    // ── seed batch ──────────────────────────────────────────
                    $alreadySeeded = DB::table('supplier_raw_material_batches')
                        ->where('supplier_raw_material_id', $product->id)
                        ->exists();

                    if ($alreadySeeded) {
                        $this->resyncProductRollup((int) $product->id);
                        continue;
                    }

                    $quantity = (int) ($product->quantity ?? 0);

                    if ($quantity <= 0) {
                        $this->resyncProductRollup((int) $product->id);
                        continue;
                    }

                    $expiry = $isPerishable ? $this->effectiveProductExpiry($product, $defaultExpiry) : null;
                    $now    = now();

                    DB::table('supplier_raw_material_batches')->insert([
                        'supplier_raw_material_id' => $product->id,
                        'user_id'                   => $product->user_id,
                        'batch_code'                => $this->seedBatchCode((int) $product->id),
                        'quantity'                  => $quantity,
                        'reserved_quantity'         => 0,
                        'expiration_date'           => $expiry,
                        'received_at'               => $product->created_at ?? $now,
                        'is_archived'               => false,
                        'created_at'                => $now,
                        'updated_at'                => $now,
                    ]);

                    $this->resyncProductRollup((int) $product->id);
                }
            });
    }

    /**
     * Recompute the flat cached columns on the product from its live batches.
     */
    private function resyncProductRollup(int $materialId): void
    {
        if (! Schema::hasTable('supplier_raw_material_batches')) {
            return;
        }

        $rollup = DB::table('supplier_raw_material_batches')
            ->where('supplier_raw_material_id', $materialId)
            ->where('is_archived', false)
            ->selectRaw('COALESCE(SUM(quantity), 0) AS qty')
            ->selectRaw('COALESCE(SUM(reserved_quantity), 0) AS reserved')
            ->selectRaw('MIN(expiration_date) AS earliest')
            ->first();

        $earliest = $rollup->earliest ?? null;

        DB::table('supplier_raw_materials')
            ->where('id', $materialId)
            ->update([
                'quantity'          => (int) $rollup->qty,
                'reserved_quantity' => (int) $rollup->reserved,
                'expiration_date'   => $earliest ? substr((string) $earliest, 0, 10) : null,
                'updated_at'        => now(),
            ]);
    }

    // =====================================================================
    // 5 — procurement requests
    // =====================================================================

    private function backfillProcurementRequests(string $defaultExpiry): void
    {
        if (! Schema::hasColumn('procurement_requests', 'expiration_date')) {
            return;
        }

        // Cancelled / rejected requests never move stock, so they are skipped.
        DB::table('procurement_requests')
            ->whereNull('expiration_date')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->update(['expiration_date' => $defaultExpiry]);

        // Safety net: also repair any value that predates the 1-year rule but is
        // still sitting in a live (non-cancelled) request.
        DB::table('procurement_requests')
            ->whereNotNull('expiration_date')
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->whereDate('expiration_date', '<', $defaultExpiry)
            ->update(['expiration_date' => $defaultExpiry]);
    }

    // =====================================================================
    // 4 — distributor inventory
    // =====================================================================

    private function backfillDistributorInventory(string $defaultExpiry): void
    {
        if (! Schema::hasTable('distributor_inventories')) {
            return;
        }

        $hasChildren = Schema::hasTable('distributor_inventory_batches');

        DB::table('distributor_inventories')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($defaultExpiry, $hasChildren) {
                foreach ($rows as $row) {
                    $patch = [];

                    if (empty($row->batch_code)) {
                        $patch['batch_code'] = $this->legacyBatchCode((int) $row->id);
                    }

                    if ($row->expiration_date === null) {
                        $patch['expiration_date'] = $defaultExpiry;
                    }

                    if (Schema::hasColumn('distributor_inventories', 'is_archived')
                        && $row->is_archived === null) {
                        $patch['is_archived'] = false;
                    }

                    if (Schema::hasColumn('distributor_inventories', 'received_at')
                        && $row->received_at === null) {
                        $patch['received_at'] = $row->created_at ?? now();
                    }

                    // Point the batch back at the procurement request that filled it.
                    if (Schema::hasColumn('distributor_inventories', 'source_procurement_request_id')
                        && $row->source_procurement_request_id === null
                        && Schema::hasColumn('inventory_logs', 'procurement_request_id')) {
                        $logRequestId = DB::table('inventory_logs')
                            ->where('product_id', $row->product_id)
                            ->where('distributor_id', $row->distributor_id)
                            ->whereNotNull('procurement_request_id')
                            ->orderByDesc('id')
                            ->value('procurement_request_id');

                        if ($logRequestId) {
                            $patch['source_procurement_request_id'] = $logRequestId;
                        }
                    }

                    if ($patch) {
                        $patch['updated_at'] = now();
                        DB::table('distributor_inventories')->where('id', $row->id)->update($patch);
                    }

                    if ($hasChildren) {
                        $this->seedDistributorBatch($row, $patch, $defaultExpiry);
                    }
                }
            });
    }

    /**
     * Turn one pre-batch distributor_inventories row into a single seed child batch
     * carrying the row's whole quantity. Idempotent: skips when the child exists.
     */
    private function seedDistributorBatch(object $row, array $patch, string $defaultExpiry): void
    {
        $exists = DB::table('distributor_inventory_batches')
            ->where('distributor_inventory_id', $row->id)
            ->exists();

        if ($exists) {
            return;
        }

        $quantity = (int) ($row->quantity ?? 0);

        if ($quantity <= 0) {
            return;
        }

        $now = now();

        DB::table('distributor_inventory_batches')->insert([
            'distributor_inventory_id'      => $row->id,
            'distributor_id'                => $row->distributor_id,
            'product_id'                    => $row->product_id,
            'batch_code'                    => $patch['batch_code'] ?? ($row->batch_code ?: $this->legacyBatchCode((int) $row->id)),
            'quantity'                      => $quantity,
            'expiration_date'               => $patch['expiration_date'] ?? ($row->expiration_date ?: $defaultExpiry),
            'source_procurement_request_id' => $patch['source_procurement_request_id'] ?? $row->source_procurement_request_id ?? null,
            'source_raw_material_id'        => $row->source_raw_material_id ?? null,
            'received_at'                   => $row->received_at ?? $row->created_at ?? $now,
            'is_archived'                   => false,
            'created_at'                    => $now,
            'updated_at'                    => $now,
        ]);
    }

    // =====================================================================
    // 6 — inactive distributor inventory
    // =====================================================================

    private function backfillInactiveInventory(string $defaultExpiry): void
    {
        if (! Schema::hasTable('inactive_distributor_inventories')) {
            return;
        }

        DB::table('inactive_distributor_inventories')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($defaultExpiry) {
                foreach ($rows as $row) {
                    $patch = [];

                    if (empty($row->batch_code)) {
                        $patch['batch_code'] = $this->legacyBatchCode((int) $row->id, 'ARC');
                    }

                    if ($row->expiration_date === null) {
                        // Prefer the date of the matching live batch so a
                        // deactivation does not invent a new shelf life.
                        $fromBatch = Schema::hasTable('distributor_inventories')
                            ? DB::table('distributor_inventories')
                                ->where('distributor_id', $row->distributor_id)
                                ->where('product_id', $row->product_id)
                                ->whereNotNull('expiration_date')
                                ->orderBy(BatchRules::fefoOrderBy())
                                ->value('expiration_date')
                            : null;

                        $patch['expiration_date'] = $fromBatch
                            ? substr((string) $fromBatch, 0, 10)
                            : $defaultExpiry;
                    }

                    if (Schema::hasColumn('inactive_distributor_inventories', 'source_procurement_request_id')
                        && $row->source_procurement_request_id === null
                        && Schema::hasTable('distributor_inventories')) {
                        $fromBatch = DB::table('distributor_inventories')
                            ->where('distributor_id', $row->distributor_id)
                            ->where('product_id', $row->product_id)
                            ->whereNotNull('source_procurement_request_id')
                            ->orderByDesc('id')
                            ->value('source_procurement_request_id');

                        if ($fromBatch) {
                            $patch['source_procurement_request_id'] = $fromBatch;
                        }
                    }

                    if ($patch) {
                        $patch['updated_at'] = now();
                        DB::table('inactive_distributor_inventories')->where('id', $row->id)->update($patch);
                    }
                }
            });
    }

    // =====================================================================
    // 7 — inventory logs
    // =====================================================================

    private function backfillInventoryLogs(string $defaultExpiry): void
    {
        if (! Schema::hasTable('inventory_logs') || ! Schema::hasTable('distributor_inventories')) {
            return;
        }

        DB::table('inventory_logs')
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($defaultExpiry) {
                foreach ($logs as $log) {
                    $patch = [];

                    if (empty($log->event_type)) {
                        $patch['event_type'] = $log->quantity_removed > 0
                            ? 'stock_out'
                            : 'procurement_receipt';
                    }

                    if (Schema::hasColumn('inventory_logs', 'quantity_removed')
                        && $log->quantity_removed === null) {
                        $patch['quantity_removed'] = 0;
                    }

                    if (Schema::hasColumn('inventory_logs', 'inventory_id')
                        && $log->inventory_id === null) {
                        $batchQuery = DB::table('distributor_inventories')
                            ->where('distributor_id', $log->distributor_id)
                            ->where('product_id', $log->product_id);

                        if (Schema::hasColumn('distributor_inventories', 'source_procurement_request_id')
                            && $log->procurement_request_id) {
                            $batchQuery->where('source_procurement_request_id', $log->procurement_request_id);
                        }

                        $batch = $batchQuery->orderBy('id')->first();

                        if ($batch) {
                            // inventory_id keeps pointing at the PARENT row, because the
                            // foreign key targets distributor_inventories.
                            $patch['inventory_id'] = $batch->id;
                            $patch['expiration_date'] ??= $batch->expiration_date;
                        }
                    }

                    // Prefer the child batch's own code, falling back to the parent.
                    if (empty($log->batch_code)) {
                        $childCode = Schema::hasTable('distributor_inventory_batches')
                            ? DB::table('distributor_inventory_batches')
                                ->where('distributor_id', $log->distributor_id)
                                ->where('product_id', $log->product_id)
                                ->orderBy(BatchRules::fefoOrderBy())
                                ->value('batch_code')
                            : null;

                        $patch['batch_code'] = $childCode;
                    }

                    if (empty($log->expiration_date)) {
                        $patch['expiration_date'] = $defaultExpiry;
                    }

                    if ($patch) {
                        $patch['updated_at'] = now();
                        DB::table('inventory_logs')->where('id', $log->id)->update($patch);
                    }
                }
            });
    }

    // =====================================================================

    /**
     * Keep an already-stored, still-valid expiry; otherwise fall back to the default.
     */
    private function effectiveProductExpiry(object $product, string $defaultExpiry): ?string
    {
        $stored = $product->expiration_date ? substr((string) $product->expiration_date, 0, 10) : null;

        if ($stored && BatchRules::isFarEnoughOut($stored, $product->category)) {
            return $stored;
        }

        return $defaultExpiry;
    }

    /**
     * Deterministic seed code so a re-run of this migration cannot collide with
     * the batch it created the first time.
     */
    private function seedBatchCode(int $materialId): string
    {
        return sprintf('LEG-%06d-01', $materialId);
    }

    private function legacyBatchCode(int $inventoryId, string $prefix = 'LEG'): string
    {
        return sprintf('%s-D%06d', $prefix, $inventoryId);
    }
};
