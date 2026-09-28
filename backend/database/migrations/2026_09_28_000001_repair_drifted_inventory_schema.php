<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * REPAIR MIGRATION — schema drift left behind by a previously failed prompt.
 *
 * Diagnosis (verified against the live `capstone001` database):
 *
 *  1. `sp_order_deliveries` is READ and WRITTEN by SpOrderController (lines ~83 and ~123)
 *     but has NO migration file at all. On a fresh install the service-provider
 *     pickup flow fatals. -> created here.
 *
 *  2. `order_vat_deductions.sp_order_id` is INSERTED by SpShopController and READ by
 *     three finance/distributor dashboards, but `2026_03_08_165714` never declared it.
 *     -> column added here.
 *
 *  3. `procurement_requests.status` is an ENUM that the code writes values into which
 *     the ENUM does not declare ('op-approved', 'd-approved'). On a fresh install in
 *     MySQL strict mode those transitions throw. -> ENUM reconciled here.
 *
 *  4. `distributor_inventories` still carries the legacy
 *     UNIQUE(distributor_id, product_id) constraint from 2026_02_24_155752, which makes
 *     one row per product and therefore structurally impossible to hold more than one
 *     batch. -> dropped here and replaced by UNIQUE(distributor_id, batch_code).
 *
 * Every statement is guarded so this migration is a no-op on a database that is
 * already correct.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $notes = [];

    public function up(): void
    {
        $this->createSpOrderDeliveriesTable();
        $this->addSpOrderIdToOrderVatDeductions();
        $this->reconcileProcurementRequestStatusEnum();
        $this->replaceDistributorInventoryUniqueKey();
    }

    public function down(): void
    {
        // 1. Drop the repaired table only if we are the ones who created it.
        if (Schema::hasTable('sp_order_deliveries')) {
            Schema::dropIfExists('sp_order_deliveries');
            $this->notes[] = 'dropped table sp_order_deliveries';
        }

        // 2. Remove the out-of-band column. The foreign key has to go first,
        //    otherwise MySQL refuses ("needed in a foreign key constraint") because
        //    the supporting index is still bound to it.
        if (Schema::hasTable('order_vat_deductions') && Schema::hasColumn('order_vat_deductions', 'sp_order_id')) {
            // Drop every foreign key on the column, whatever it is called. This
            // column was originally added out-of-band under the name
            // `vat_sp_order_id_fk`, so matching on our own constraint name would
            // miss it and MySQL would then refuse to drop the column.
            $this->dropForeignKeysOnColumn('order_vat_deductions', 'sp_order_id');

            Schema::table('order_vat_deductions', function (Blueprint $table) {
                $table->dropColumn('sp_order_id');
            });
            $this->notes[] = 'dropped order_vat_deductions.sp_order_id';
        }

        // 3. The ENUM is left as-is on purpose. Narrowing an ENUM back would destroy
        //    any rows currently holding a value we would be removing, and these
        //    statuses are actively used by the procurement approval chain.
        //    This mirrors the existing precedent in 2026_02_19_000000, which also
        //    leaves its ENUM untouched in down().

        // 4. Re-apply the legacy single-row-per-product constraint ONLY when the table
        //    actually holds no duplicate (distributor_id, product_id) pairs.
        if (Schema::hasTable('distributor_inventories')) {
            $this->restoreLegacyUniqueKey();
        }
    }

    // ---------------------------------------------------------------------
    // 1. sp_order_deliveries
    // ---------------------------------------------------------------------

    private function createSpOrderDeliveriesTable(): void
    {
        if (Schema::hasTable('sp_order_deliveries')) {
            return;
        }

        Schema::create('sp_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sp_order_id');
            $table->unsignedBigInteger('service_provider_id')->nullable();
            $table->string('proof_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sp_order_id');
            $table->index('service_provider_id');
        });

        // Only attach the foreign keys when the referenced tables actually exist,
        // otherwise a fresh install in an unusual order would explode here.
        if (Schema::hasTable('sp_orders') && Schema::hasTable('users')) {
            Schema::table('sp_order_deliveries', function (Blueprint $table) {
                $table->foreign('sp_order_id')->references('id')->on('sp_orders')->onDelete('cascade');
                $table->foreign('service_provider_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        $this->notes[] = 'created table sp_order_deliveries';
    }

    // ---------------------------------------------------------------------
    // 2. order_vat_deductions.sp_order_id
    // ---------------------------------------------------------------------

    private function addSpOrderIdToOrderVatDeductions(): void
    {
        if (! Schema::hasTable('order_vat_deductions')) {
            return;
        }

        if (Schema::hasColumn('order_vat_deductions', 'sp_order_id')) {
            return;
        }

        Schema::table('order_vat_deductions', function (Blueprint $table) {
            $table->unsignedBigInteger('sp_order_id')->nullable()->after('order_id');
        });

        if (Schema::hasTable('sp_orders')) {
            Schema::table('order_vat_deductions', function (Blueprint $table) {
                $table->foreign('sp_order_id')->references('id')->on('sp_orders')->onDelete('cascade');
            });
        }

        Schema::table('order_vat_deductions', function (Blueprint $table) {
            $table->index('sp_order_id');
        });

        $this->notes[] = 'added order_vat_deductions.sp_order_id';
    }

    // ---------------------------------------------------------------------
    // 3. procurement_requests.status ENUM
    // ---------------------------------------------------------------------

    private function reconcileProcurementRequestStatusEnum(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        // Rebuild the full set of statuses the application can actually write.
        // Union with whatever is already stored so live data is never truncated.
        $required = [
            'pending', 'approved', 'd-approved', 'op-approved', 'ready', 'processing',
            'prepared', 'shipped', 'in_transit', 'delivered', 'rejected', 'cancelled',
        ];

        $current = [];
        foreach (DB::select("SHOW COLUMNS FROM procurement_requests LIKE 'status'") as $column) {
            if (! preg_match("/^enum\((.*)\)$/i", (string) $column->Type, $m)) {
                return; // Not an ENUM (a VARCHAR, say). Leave it entirely alone.
            }
            $current = array_map(
                static fn ($v) => trim($v, "' "),
                explode(',', $m[1])
            );
        }

        $merged = array_values(array_unique(array_merge($required, $current)));

        sort($merged);

        if ($merged === $current) {
            return; // Already correct.
        }

        // Build the literal safely — every member comes from a hardcoded whitelist
        // unioned with values that were read back out of the table itself.
        $literal = implode(',', array_map(
            static fn ($v) => "'" . str_replace(["\\", "'"], ["\\\\", "''"], $v) . "'",
            $merged
        ));

        DB::statement("ALTER TABLE `procurement_requests` MODIFY `status` ENUM({$literal}) NOT NULL DEFAULT 'pending'");

        $this->notes[] = 'reconciled procurement_requests.status ENUM';
    }

    // ---------------------------------------------------------------------
    // 4. distributor_inventories unique key
    // ---------------------------------------------------------------------

    private function replaceDistributorInventoryUniqueKey(): void
    {
        $legacy = 'distributor_inventories_distributor_id_product_id_unique';
        $batch  = 'di_dist_batch_unique';

        $indexes = $this->indexNames('distributor_inventories');

        if (in_array($legacy, $indexes, true)) {
            Schema::table('distributor_inventories', function (Blueprint $table) use ($legacy) {
                $table->dropUnique($legacy);
            });
            $this->notes[] = "dropped {$legacy}";
        }

        // Only add the replacement key when `batch_code` already exists. On a
        // database that has never had batch tracking the column is created by
        // 2026_09_28_000002, which adds this index itself once the column is there.
        // Adding the key here would fail with
        //   "Key column 'batch_code' doesn't exist in table".
        if (! Schema::hasColumn('distributor_inventories', 'batch_code')) {
            return;
        }

        if (in_array($batch, $indexes, true)) {
            return;
        }

        // batch_code is NULLable, and MySQL allows unlimited NULLs inside a UNIQUE
        // index, so this key correctly de-duplicates only rows that actually carry
        // a batch code. It is the constraint that lets one product hold many batches.
        Schema::table('distributor_inventories', function (Blueprint $table) {
            $table->unique(['distributor_id', 'batch_code'], 'di_dist_batch_unique');
        });

        $this->notes[] = "added {$batch}";
    }

    private function restoreLegacyUniqueKey(): void
    {
        $legacy = 'distributor_inventories_distributor_id_product_id_unique';

        if (in_array($legacy, $this->indexNames('distributor_inventories'), true)) {
            return;
        }

        $clashing = DB::table('distributor_inventories')
            ->select('distributor_id', 'product_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('distributor_id', 'product_id')
            ->having('c', '>', 1)
            ->limit(1)
            ->exists();

        if ($clashing) {
            // Rolling back would fail on the ADD anyway. Refuse loudly instead of
            // silently deleting somebody's stock to satisfy a constraint.
            throw new RuntimeException(
                'Cannot restore distributor_inventories_distributor_id_product_id_unique: '
                . 'the table now holds multiple batches for the same (distributor_id, product_id). '
                . 'Roll this migration forward instead, or collapse the batches by hand first.'
            );
        }

        Schema::table('distributor_inventories', function (Blueprint $table) use ($legacy) {
            $table->unique(['distributor_id', 'product_id'], $legacy);
        });
    }

    // ---------------------------------------------------------------------

    /** @return list<string> */
    private function indexNames(string $table): array
    {
        return array_values(array_unique(array_map(
            static fn ($row) => (string) $row->Key_name,
            DB::select("SHOW INDEX FROM `{$table}`")
        )));
    }

    /**
     * Names of real FOREIGN KEY constraints on a column.
     *
     * `SHOW INDEX` cannot distinguish a foreign key from a plain index that happens
     * to share its name, and it is the plain index that survives a failed
     * DROP FOREIGN KEY. information_schema is the only reliable source.
     *
     * @return list<string>
     */
    private function foreignKeysForColumn(string $table, string $column): array
    {
        return array_values(array_unique(array_map(
            static fn ($row) => (string) $row->CONSTRAINT_NAME,
            DB::select(
                'SELECT DISTINCT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$table, $column]
            )
        )));
    }

    private function dropForeignKeysOnColumn(string $table, string $column): void
    {
        foreach ($this->foreignKeysForColumn($table, $column) as $name) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            $this->notes[] = "dropped FK {$table}.{$name}";
        }
    }
};
