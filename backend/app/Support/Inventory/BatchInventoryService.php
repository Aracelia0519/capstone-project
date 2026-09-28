<?php

namespace App\Support\Inventory;

use App\Models\OperationDistributor\DistributorInventory;
use App\Models\OperationDistributor\DistributorInventoryBatch;
use App\Models\OperationDistributor\InventoryLog;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Supplier\SupplierRawMaterial;
use App\Models\Supplier\SupplierRawMaterialBatch;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Every quantity movement in the system goes through this class.
 *
 * Controllers must not write `quantity` directly. Concentrating the arithmetic
 * here is what makes the batch totals trustworthy: a sale, a restock, a
 * reservation release and an archive all update the batch row and the cached
 * product total in the same place, so they cannot disagree.
 *
 * The three invariants this class protects:
 *
 *  1. Expired stock is never sold and never procured.
 *     Enforced in {@see sellFefo()} and {@see reserveForProcurement()}.
 *
 *  2. Every incoming batch has at least one year of shelf life.
 *     Enforced in {@see validateExpiration()}, which the FormRequests call before
 *     anything is written.
 *
 *  3. Sales consume the batch that expires soonest, not an arbitrary one.
 *     Enforced by the FEFO ordering in {@see sellFefo()}.
 */
class BatchInventoryService
{
    // =================================================================
    // VALIDATION
    // =================================================================

    /**
     * Check an incoming expiration date against the category rules.
     *
     * @return string|null An error message, or null when the date is acceptable.
     */
    public function validateExpiration(
        ?string $category,
        $expirationDate,
        ?\DateTimeInterface $receivedAt = null
    ): ?string {
        $blank = $expirationDate === null
            || (is_string($expirationDate) && trim($expirationDate) === '');

        if ($blank) {
            return BatchRules::requiresExpiration($category)
                ? 'An expiration date is required for this category. Tools, Accessories and Packaging are the only categories where it may be left blank.'
                : null;
        }

        if (! is_string($expirationDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($expirationDate))) {
            return 'The expiration date must be a valid date in YYYY-MM-DD format.';
        }

        $receivedAt ??= now();

        if (! BatchRules::isFarEnoughOut(trim($expirationDate), $category, $receivedAt)) {
            return BatchRules::minimumRuleMessage();
        }

        return null;
    }

    /**
     * Throw a domain exception when the date is not acceptable.
     *
     * @throws \App\Support\Inventory\Exceptions\InvalidExpirationDate
     */
    public function assertExpirationIsValid(
        ?string $category,
        $expirationDate,
        ?\DateTimeInterface $receivedAt = null
    ): void {
        $error = $this->validateExpiration($category, $expirationDate, $receivedAt);

        if ($error !== null) {
            throw new Exceptions\InvalidExpirationDate($error);
        }
    }

    // =================================================================
    // SUPPLIER SIDE
    // =================================================================

    /**
     * Add a delivery lot to a supplier's product.
     *
     * Called both for the very first stock of a new product and for every
     * subsequent restock, which is what lets a supplier receive 40 cans in March
     * and 60 in October with two different deadlines.
     *
     * @throws Exceptions\InvalidExpirationDate
     * @throws Exceptions\InsufficientStock
     */
    public function addBatch(
        SupplierRawMaterial $material,
        int $quantity,
        ?string $expirationDate,
        ?int $actorId = null,
        ?string $batchCode = null,
        ?\DateTimeInterface $receivedAt = null
    ): SupplierRawMaterialBatch {
        $this->assertExpirationIsValid($material->category, $expirationDate, $receivedAt);

        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }

        $receivedAt ??= now();

        return DB::transaction(function () use ($material, $quantity, $expirationDate, $actorId, $batchCode, $receivedAt) {
            $batch = SupplierRawMaterialBatch::create([
                'supplier_raw_material_id' => $material->id,
                'user_id'                   => $material->user_id,
                'batch_code'                => $batchCode ?: $this->generateBatchCode($material->id),
                'quantity'                  => $quantity,
                'reserved_quantity'         => 0,
                'expiration_date'           => $this->normaliseDate($expirationDate),
                'received_at'               => $receivedAt,
                'is_archived'               => false,
            ]);

            $material->syncBatchRollups();

            return $batch;
        });
    }

    /**
     * Units of a product a distributor may still buy right now.
     */
    public function supplierAvailableQuantity(SupplierRawMaterial $material): int
    {
        return (int) SupplierRawMaterialBatch::query()
            ->where('supplier_raw_material_id', $material->id)
            ->sellable()
            ->sum(DB::raw('quantity - reserved_quantity'));
    }

    /**
     * Promise stock to a distributor at procurement time.
     *
     * Reserving rather than deducting stops the same units being sold to two
     * distributors while a shipment is in transit. The reserved amount is held
     * against the batch until the delivery is either confirmed or cancelled.
     *
     * @return list<array{quantity: int, expiration_date: string|null}>
     *         The batches the request draws from, in FEFO order.
     *
     * @throws Exceptions\InsufficientStock
     */
    public function reserveForProcurement(SupplierRawMaterial $material, int $quantity): array
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }

        return DB::transaction(function () use ($material, $quantity) {
            $remaining = $quantity;
            $allocations = [];

            $batches = SupplierRawMaterialBatch::query()
                ->where('supplier_raw_material_id', $material->id)
                ->sellable()
                ->orderByRaw(BatchRules::fefoOrderBy())
                // Lock the rows so two distributors cannot reserve the same units
                // concurrently.
                ->lockForUpdate()
                ->get();

            // Measured BEFORE the loop mutates reserved_quantity, so the error
            // message reports the stock that was actually on offer.
            $available = $batches->sum(
                fn (SupplierRawMaterialBatch $b) => max(0, $b->quantity - $b->reserved_quantity)
            );

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $free = $batch->quantity - $batch->reserved_quantity;

                if ($free <= 0) {
                    continue;
                }

                $take = min($free, $remaining);

                $batch->increment('reserved_quantity', $take);
                $remaining -= $take;

                $allocations[] = [
                    'supplier_raw_material_batch_id' => $batch->id,
                    'batch_code'                       => $batch->batch_code,
                    'quantity'                         => $take,
                    'expiration_date'                  => $batch->expiration_date
                        ? $batch->expiration_date->toDateString()
                        : null,
                ];
            }

            if ($remaining > 0) {
                throw new Exceptions\InsufficientStock(sprintf(
                    'Only %d unit(s) of "%s" are available to procure. %d were requested.',
                    $available,
                    $material->name,
                    $quantity
                ));
            }

            $material->syncBatchRollups();

            return $allocations;
        });
    }

    /**
     * Give reserved stock back to the batches it came from.
     *
     * Called when a procurement request is rejected, cancelled, or returned.
     */
    public function releaseReservation(ProcurementRequest $procurement): void
    {
        if (! $procurement->product_id || ! $procurement->quantity) {
            return;
        }

        DB::transaction(function () use ($procurement) {
            $material = SupplierRawMaterial::find($procurement->product_id);

            if (! $material) {
                return;
            }

            // Prefer to release against the exact batch the request named, so a
            // restock in the meantime is not consumed by the release.
            // normaliseDate() handles both a cast Carbon instance and a raw string,
            // because the column is only date-cast on some models.
            $expiry = $this->normaliseDate($procurement->expiration_date);

            $query = SupplierRawMaterialBatch::query()
                ->where('supplier_raw_material_id', $material->id)
                ->where('reserved_quantity', '>', 0);

            if ($expiry !== null) {
                $query->whereDate('expiration_date', $expiry);
            }

            $remaining = (int) $procurement->quantity;

            foreach ($query->orderByRaw(BatchRules::fefoOrderBy())->lockForUpdate()->get() as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $release = min($batch->reserved_quantity, $remaining);
                $batch->decrement('reserved_quantity', $release);
                $remaining -= $release;
            }

            $material->syncBatchRollups();
        });
    }

    /**
     * Turn reserved stock into shipped stock: the supplier no longer holds it, and
     * the batch it left behind is recorded on the request.
     */
    public function consumeReservation(ProcurementRequest $procurement): void
    {
        if (! $procurement->product_id || ! $procurement->quantity) {
            return;
        }

        DB::transaction(function () use ($procurement) {
            $material = SupplierRawMaterial::find($procurement->product_id);

            if (! $material) {
                return;
            }

            $remaining = (int) $procurement->quantity;
            $expiry = $this->normaliseDate($procurement->expiration_date);

            $query = SupplierRawMaterialBatch::query()
                ->where('supplier_raw_material_id', $material->id);

            if ($expiry !== null) {
                $query->whereDate('expiration_date', $expiry);
            }

            foreach ($query->orderByRaw(BatchRules::fefoOrderBy())->lockForUpdate()->get() as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $reserved = min((int) $batch->reserved_quantity, $remaining);

                // Reserved units leave the physical quantity too.
                $take = min((int) $batch->quantity, $remaining);

                $batch->decrement('reserved_quantity', $reserved);
                $batch->decrement('quantity', $take);

                $remaining -= $take;
            }

            $material->syncBatchRollups();
        });
    }

    /**
     * Archive a supplier batch that has passed its expiration date.
     *
     * Archiving is a hard stop: the batch stops being procurable immediately, but
     * the row and its history are kept for audit rather than deleted.
     */
    public function archiveSupplierBatch(SupplierRawMaterialBatch $batch, int $actorId, ?string $reason = null): void
    {
        DB::transaction(function () use ($batch, $actorId, $reason) {
            $batch->forceFill([
                'is_archived'    => true,
                'archived_at'    => now(),
                'archive_reason' => $reason ?: 'Expired stock archived',
                'archived_by'    => $actorId,
            ])->save();

            $batch->material?->syncBatchRollups();
        });
    }

    /**
     * Archive every expired batch belonging to a supplier.
     *
     * @return int Number of batches archived.
     */
    public function archiveExpiredSupplierBatches(int $supplierId, int $actorId, ?string $reason = null): int
    {
        $batches = SupplierRawMaterialBatch::query()
            ->where('user_id', $supplierId)
            ->where('is_archived', false)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->orderBy('expiration_date')
            ->get();

        foreach ($batches as $batch) {
            $this->archiveSupplierBatch($batch, $actorId, $reason);
        }

        return $batches->count();
    }

    // =================================================================
    // DISTRIBUTOR SIDE
    // =================================================================

    /**
     * Receive stock into a distributor's warehouse as a new batch.
     *
     * @throws Exceptions\InvalidExpirationDate
     */
    public function receiveBatch(
        int $distributorId,
        int $productId,
        int $quantity,
        ?string $expirationDate,
        ?int $actorId = null,
        ?ProcurementRequest $procurement = null,
        ?int $sourceRawMaterialId = null,
        ?string $batchCode = null
    ): DistributorInventoryBatch {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }

        // A category is not always known here (the product is a distributor
        // product, not a supplier material), so the blanket one-year rule applies.
        $this->assertExpirationIsValid(null, $expirationDate);

        return DB::transaction(function () use (
            $distributorId, $productId, $quantity, $expirationDate,
            $actorId, $procurement, $sourceRawMaterialId, $batchCode
        ) {
            $inventory = DistributorInventory::firstOrCreate(
                ['distributor_id' => $distributorId, 'product_id' => $productId],
                ['quantity' => 0]
            );

            $batch = $this->findOrCreateBatch(
                $inventory,
                $batchCode ?: $this->generateBatchCode($productId, 'DIST'),
                $expirationDate
            );

            $batch->increment('quantity', $quantity);

            if ($procurement) {
                $batch->forceFill([
                    'source_procurement_request_id' => $procurement->id,
                    'source_raw_material_id'        => $sourceRawMaterialId ?? $procurement->product_id,
                    'received_at'                   => now(),
                ])->save();
            }

            $inventory->syncBatchTotals();

            InventoryLog::create([
                'distributor_id'           => $distributorId,
                'product_id'               => $productId,
                'inventory_id'             => $inventory->id,
                'batch_code'               => $batch->batch_code,
                'expiration_date'          => $batch->expiration_date?->toDateString(),
                'procurement_request_id'   => $procurement?->id,
                'quantity_added'           => $quantity,
                'quantity_removed'         => 0,
                'event_type'               => $procurement ? 'procurement_receipt' : 'restock',
                'user_id'                  => $actorId,
                'notes'                    => $procurement
                    ? "Received from procurement {$procurement->request_code}"
                    : 'Manual restock',
            ]);

            return $batch->refresh();
        });
    }

    /**
     * Sell stock, consuming the batch that expires soonest.
     *
     * This is the FEFO heart of the checkout. It walks the sellable batches in
     * expiration order, takes as much as it can from each, and throws if the
     * warehouse cannot cover the full quantity — rather than silently selling
     * less than the customer paid for.
     *
     * MUST be called inside a transaction; it takes row locks.
     *
     * @return list<DistributorInventoryBatch> The batches that were drawn from.
     *
     * @throws Exceptions\InsufficientStock
     */
    public function sellFefo(
        int $distributorId,
        int $productId,
        int $quantity,
        ?int $actorId = null,
        string $eventType = 'sale',
        ?string $notes = null
    ): array {
        if ($quantity < 1) {
            return [];
        }

        // Self-contained transaction.
        //
        // This method is partly destructive before it knows whether it can
        // finish: it drains batch after batch and only then discovers the stock
        // was short. Relying on the caller to have wrapped it would mean a
        // checkout that forgets DB::beginTransaction() leaves the warehouse
        // quietly depleted with no order recorded. Owning the transaction here
        // makes the all-or-nothing guarantee unconditional. It nests harmlessly
        // inside a caller's transaction via savepoints.
        return DB::transaction(function () use ($distributorId, $productId, $quantity, $actorId, $eventType, $notes) {
            $remaining = $quantity;
            $drawn = [];

            // Expired batches are excluded by ->sellable(): a customer can never be
            // served from stock whose deadline has passed.
            $batches = DistributorInventoryBatch::query()
                ->where('distributor_id', $distributorId)
                ->where('product_id', $productId)
                ->sellable()
                ->orderByRaw(BatchRules::fefoOrderBy())
                ->lockForUpdate()
                ->get();

            // Measured before any mutation, for an accurate error message.
            $available = (int) $batches->sum('quantity');

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $stock = (int) $batch->quantity;

                if ($stock <= 0) {
                    continue;
                }

                $take = min($stock, $remaining);

                $batch->decrement('quantity', $take);
                $remaining -= $take;
                $drawn[] = $batch;

                InventoryLog::create([
                    'distributor_id'   => $distributorId,
                    'product_id'       => $productId,
                    'inventory_id'     => $batch->distributor_inventory_id,
                    'batch_code'       => $batch->batch_code,
                    'expiration_date'  => $batch->expiration_date?->toDateString(),
                    'quantity_added'   => 0,
                    'quantity_removed' => $take,
                    'event_type'       => $eventType,
                    'user_id'          => $actorId,
                    'notes'            => $notes,
                ]);
            }

            // Throwing here rolls the whole thing back, including the partial
            // draws and their audit rows.
            if ($remaining > 0) {
                $name = DistributorInventory::query()
                    ->where('distributor_id', $distributorId)
                    ->where('product_id', $productId)
                    ->with('product')
                    ->first()?->product?->name ?? "product #{$productId}";

                throw new Exceptions\InsufficientStock(sprintf(
                    'Not enough unexpired stock for %s. %d available, %d requested.',
                    $name,
                    $available,
                    $quantity
                ));
            }

            $this->syncParent($distributorId, $productId);

            return $drawn;
        });
    }

    /**
     * Put units back into the batch they were sold from.
     *
     * A failed delivery must not dump the stock into an arbitrary batch: the
     * original batch code and expiration date are honoured so the returned units
     * keep their original deadline.
     */
    public function restockBatch(
        int $distributorId,
        int $productId,
        int $quantity,
        ?string $batchCode = null,
        ?string $expirationDate = null,
        ?int $actorId = null,
        string $eventType = 'restock',
        ?string $notes = null
    ): DistributorInventoryBatch {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }

        return DB::transaction(function () use (
            $distributorId, $productId, $quantity,
            $batchCode, $expirationDate, $actorId, $eventType, $notes
        ) {
            $inventory = DistributorInventory::firstOrCreate(
                ['distributor_id' => $distributorId, 'product_id' => $productId],
                ['quantity' => 0]
            );

            $isExpired = BatchRules::isExpired($expirationDate);

            if ($isExpired) {
                // Goods coming back after their deadline must NOT rejoin the
                // saleable pool, and they must not revive the archived lot they
                // came from either. Park them in a brand new batch carrying a
                // fresh one-year deadline, under a fresh code, so the audit trail
                // shows a return rather than a resurrection of dead stock.
                $batch = $this->findOrCreateBatch(
                    $inventory,
                    $this->generateBatchCode($productId, 'DIST-RTN'),
                    BatchRules::minimumExpirationDate()->toDateString()
                );
            } else {
                $batch = $this->findOrCreateBatch(
                    $inventory,
                    $batchCode ?: $this->generateBatchCode($productId, 'DIST'),
                    $expirationDate
                );
            }

            $batch->increment('quantity', $quantity);
            $batch->forceFill([
                'is_archived'    => false,
                'archived_at'    => null,
                'archive_reason' => null,
            ])->save();

            $inventory->syncBatchTotals();

            InventoryLog::create([
                'distributor_id'   => $distributorId,
                'product_id'       => $productId,
                'inventory_id'     => $inventory->id,
                'batch_code'       => $batch->batch_code,
                'expiration_date'  => $batch->expiration_date?->toDateString(),
                'quantity_added'   => $quantity,
                'quantity_removed' => 0,
                'event_type'       => $eventType,
                'user_id'          => $actorId,
                'notes'            => $notes,
            ]);

            return $batch->refresh();
        });
    }

    /**
     * Units a customer may still buy, ignoring batches that have expired.
     */
    public function distributorAvailableQuantity(int $distributorId, int $productId): int
    {
        return (int) DistributorInventoryBatch::query()
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId)
            ->sellable()
            ->sum('quantity');
    }

    /**
     * Total physically present, expired units included.
     */
    public function distributorTotalQuantity(int $distributorId, int $productId): int
    {
        return (int) DistributorInventoryBatch::query()
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId)
            ->where('is_archived', false)
            ->sum('quantity');
    }

    /**
     * Archive a distributor batch that has expired, so it leaves the active
     * supply chain and can no longer be bought.
     */
    public function archiveDistributorBatch(
        DistributorInventoryBatch $batch,
        int $actorId,
        ?string $reason = null
    ): void {
        DB::transaction(function () use ($batch, $actorId, $reason) {
            $batch->forceFill([
                'is_archived'    => true,
                'archived_at'    => now(),
                'archive_reason' => $reason ?: 'Expired stock archived',
                'archived_by'    => $actorId,
            ])->save();

            InventoryLog::create([
                'distributor_id'   => $batch->distributor_id,
                'product_id'       => $batch->product_id,
                'inventory_id'     => $batch->distributor_inventory_id,
                'batch_code'       => $batch->batch_code,
                'expiration_date'  => $batch->expiration_date?->toDateString(),
                'quantity_added'   => 0,
                'quantity_removed' => (int) $batch->quantity,
                'event_type'       => 'archive',
                'user_id'          => $actorId,
                'notes'            => $reason ?: 'Expired stock archived',
            ]);

            // The stock stays on the books but stops counting towards the sellable
            // total, so the product row reflects only what can still be sold.
            $this->syncParent($batch->distributor_id, $batch->product_id);
        });
    }

    /**
     * Archive every expired batch held by a distributor.
     *
     * @return int Number of batches archived.
     */
    public function archiveExpiredDistributorBatches(int $distributorId, int $actorId, ?string $reason = null): int
    {
        $batches = DistributorInventoryBatch::query()
            ->where('distributor_id', $distributorId)
            ->where('is_archived', false)
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->orderBy('expiration_date')
            ->get();

        foreach ($batches as $batch) {
            $this->archiveDistributorBatch($batch, $actorId, $reason);
        }

        return $batches->count();
    }

    // =================================================================
    // REPORTING HELPERS
    // =================================================================

    /**
     * Per-product batch breakdown for inventory screens.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function batchBreakdown(int $distributorId, int $productId): array
    {
        return DistributorInventoryBatch::query()
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId)
            ->orderByRaw(BatchRules::fefoOrderBy())
            ->get()
            ->map(fn (DistributorInventoryBatch $b) => [
                'id'               => $b->id,
                'batch_code'       => $b->batch_code,
                'quantity'         => (int) $b->quantity,
                'expiration_date'  => $b->expiration_date?->toDateString(),
                'is_archived'      => (bool) $b->is_archived,
                'is_expired'       => $b->is_expired,
                'health'           => $b->health,
                'days_until_expiry' => $b->days_until_expiry,
                'received_at'      => $b->received_at?->toDateString(),
            ])
            ->groupBy(fn (array $b) => (int) $b['id'])
            ->map(fn ($group) => $group->first())
            ->all();
    }

    // =================================================================
    // INTERNALS
    // =================================================================

    /**
     * Reuse a batch with the same code, otherwise create it.
     *
     * Reusing keeps restocks tidy: receiving the same lot twice does not fragment
     * the product into two rows that both claim the same expiry.
     */
    private function findOrCreateBatch(
        DistributorInventory $inventory,
        string $batchCode,
        ?string $expirationDate
    ): DistributorInventoryBatch {
        $expiry = $this->normaliseDate($expirationDate);

        $existing = DistributorInventoryBatch::query()
            ->where('distributor_inventory_id', $inventory->id)
            ->where('batch_code', $batchCode)
            // Never adopt an archived lot. Reusing one would silently un-archive
            // stock that was deliberately pulled out of the supply chain, and the
            // caller would then also have to reverse the archive metadata.
            ->where('is_archived', false)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createBatchWithFallbackCode($inventory, $batchCode, $expiry);
    }

    /**
     * Create a batch, stepping the code forward if it collides.
     *
     * A supplier is allowed to reuse a batch label, so a collision on
     * UNIQUE(did, batch_code) is expected and must not blow up a delivery.
     */
    private function createBatchWithFallbackCode(
        DistributorInventory $inventory,
        string $batchCode,
        ?string $expirationDate
    ): DistributorInventoryBatch {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DistributorInventoryBatch::create([
                    'distributor_inventory_id' => $inventory->id,
                    'distributor_id'           => $inventory->distributor_id,
                    'product_id'               => $inventory->product_id,
                    'batch_code'               => $attempt === 0
                        ? $batchCode
                        : $batchCode . '-R' . $attempt,
                    'quantity'                 => 0,
                    'expiration_date'          => $expirationDate,
                    'received_at'              => now(),
                    'is_archived'              => false,
                ]);
            } catch (QueryException $e) {
                if ($attempt === 4) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Unable to allocate a unique batch code.');
    }

    private function syncParent(int $distributorId, int $productId): void
    {
        DistributorInventory::query()
            ->where('distributor_id', $distributorId)
            ->where('product_id', $productId)
            ->first()
            ?->syncBatchTotals();
    }

    /**
     * A readable, collision-resistant, human-typeable batch code.
     *
     * @param string $prefix Distinguishes supplier lots (SUP) from distributor lots (DIST).
     */
    public function generateBatchCode(int $subjectId, string $prefix = 'SUP'): string
    {
        return sprintf(
            '%s-%05d-%s-%s',
            $prefix,
            $subjectId,
            now()->format('ymd'),
            Str::upper(Str::random(4))
        );
    }

    private function normaliseDate($date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        return substr(trim((string) $date), 0, 10);
    }
}
