<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BATCH TRACKING SCHEMA
 *
 * Replaces flat "one number per product" stock with real batches:
 * each batch carries its OWN quantity and its OWN expiration date.
 *
 * Shape of the new model
 * ──────────────────────
 *
 *   supplier_raw_materials              (the product / variant definition)
 *     quantity            ─┐  cached rollups, kept in sync by the service layer so the
 *     reserved_quantity   ─┤  existing read paths and reports keep working untouched
 *     expiration_date    ─┘  (earliest live batch date, for sorting/display)
 *     is_archived, archived_at, archive_reason, archived_by
 *        │
 *        └── supplier_raw_material_batches        ◄── NEW: one row per delivery lot
 *              batch_code, quantity, reserved_quantity, expiration_date (nullable)
 *              received_at, is_archived, archived_at, archive_reason, archived_by
 *
 *   distributor_inventories            (ALREADY batch-shaped on the live DB — reused)
 *     one row per batch: batch_code, expiration_date, quantity, is_archived,
 *     source_procurement_request_id, source_raw_material_id, received_at
 *
 *   procurement_requests               the batch the distributor reserved gets frozen
 *     expiration_date, stock_reserved_at, stock_released_at, stock_consumed_at
 *                                      onto the request so the delivered batch is
 *                                      unambiguous even after the supplier restocks.
 *
 *   client_order_items / sp_order_items
 *     batch_code, expiration_date      which batch actually shipped to the buyer.
 *
 *   inventory_logs                     full audit trail, additions AND removals.
 *
 * IDEMPOTENCY
 * ───────────
 * This database was previously altered out-of-band by a failed prompt: the columns
 * below already exist in MySQL but no migration ever declared them. Every DDL
 * statement is therefore guarded by Schema::hasColumn()/hasTable(), so running this
 * on the live database skips the DDL and running it on a fresh database creates
 * everything from scratch. Both paths converge on the same schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addColumnsToSupplierRawMaterials();
        $this->createSupplierRawMaterialBatches();
        $this->addColumnsToDistributorInventories();
        $this->createDistributorInventoryBatches();
        $this->addColumnsToInactiveDistributorInventories();
        $this->addColumnsToInventoryLogs();
        $this->addColumnsToProcurementRequests();
        $this->addColumnsToOrderItems('client_order_items');
        $this->addColumnsToOrderItems('sp_order_items');
    }

    public function down(): void
    {
        // DESTRUCTIVE BY DESIGN.
        // These columns now hold the only copy of your expiry data, so rolling this
        // migration back throws the batch tracking away. Take a mysqldump first.

        // Foreign keys have to be dropped before the columns they reference — MySQL
        // refuses to drop a column that is still part of a constraint.
        $this->dropForeign('inventory_logs', 'inventory_logs_inventory_id_foreign');
        $this->dropForeign('inventory_logs', 'inventory_logs_user_id_foreign');
        $this->dropForeign('distributor_inventories', 'distributor_inventories_source_procurement_request_id_foreign');
        $this->dropForeign('distributor_inventories', 'distributor_inventories_archived_by_foreign');
        $this->dropForeign('supplier_raw_materials', 'supplier_raw_materials_archived_by_foreign');

        // Only the UNIQUE key has to go explicitly.
        //
        //  - MySQL will not let a UNIQUE index outlive a NOT NULL column it covers,
        //    so `di_dist_batch_unique` must be dropped before `batch_code` goes.
        //  - The plain composite indexes are removed automatically when their
        //    columns are dropped, and dropping them by hand is actively harmful:
        //    MySQL refuses ("needed in a foreign key constraint") when the index
        //    happens to be the one backing an unrelated foreign key.
        $this->dropIndex('distributor_inventories', 'di_dist_batch_unique');

        $this->dropColumns('client_order_items', ['batch_code', 'expiration_date']);
        $this->dropColumns('sp_order_items', ['batch_code', 'expiration_date']);

        $this->dropColumns('procurement_requests', [
            'expiration_date', 'stock_reserved_at', 'stock_released_at', 'stock_consumed_at',
        ]);

        $this->dropColumns('inventory_logs', [
            'inventory_id', 'expiration_date', 'batch_code',
            'quantity_removed', 'event_type', 'user_id', 'notes',
        ]);

        $this->dropColumns('inactive_distributor_inventories', [
            'batch_code', 'expiration_date', 'source_procurement_request_id',
        ]);

        $this->dropColumns('distributor_inventories', [
            'batch_code', 'expiration_date', 'source_procurement_request_id',
            'source_raw_material_id', 'received_at', 'is_archived',
            'archived_at', 'archive_reason', 'archived_by',
        ]);

        Schema::dropIfExists('distributor_inventory_batches');
        Schema::dropIfExists('supplier_raw_material_batches');

        $this->dropColumns('supplier_raw_materials', [
            'quantity', 'reserved_quantity', 'minimum_stock_level', 'expiration_date',
            'is_archived', 'archived_at', 'archive_reason', 'archived_by',
        ]);
    }

    // =====================================================================
    // supplier_raw_materials
    // =====================================================================

    private function addColumnsToSupplierRawMaterials(): void
    {
        if (! Schema::hasTable('supplier_raw_materials')) {
            return;
        }

        $missing = array_values(array_filter([
            'quantity', 'reserved_quantity', 'minimum_stock_level', 'expiration_date',
            'is_archived', 'archived_at', 'archive_reason', 'archived_by',
        ], fn ($c) => ! Schema::hasColumn('supplier_raw_materials', $c)));

        if ($missing) {
            Schema::table('supplier_raw_materials', function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        // UNSIGNED so a negative quantity is impossible at the storage layer.
                        'quantity'            => $table->unsignedInteger('quantity')->default(0),
                        'reserved_quantity'   => $table->unsignedInteger('reserved_quantity')->default(0),
                        'minimum_stock_level' => $table->unsignedInteger('minimum_stock_level')->default(0),
                        'expiration_date'     => $table->date('expiration_date')->nullable(),
                        'is_archived'         => $table->boolean('is_archived')->default(false),
                        'archived_at'         => $table->timestamp('archived_at')->nullable(),
                        'archive_reason'      => $table->string('archive_reason')->nullable(),
                        'archived_by'         => $table->unsignedBigInteger('archived_by')->nullable(),
                        default               => null,
                    };
                }
            });
        }

        $indexes = $this->indexNames('supplier_raw_materials');

        $this->addIndex('supplier_raw_materials', 'srm_user_archived_index', ['user_id', 'archived_at']);
        $this->addIndex('supplier_raw_materials', 'srm_user_active_index', ['user_id', 'is_active']);
        $this->addIndex('supplier_raw_materials', 'srm_expiration_index', ['expiration_date']);
        $this->addIndex('supplier_raw_materials', 'srm_user_sku_index', ['user_id', 'sku_code']);

        if (! in_array('supplier_raw_materials_archived_by_foreign', $indexes, true)
            && Schema::hasTable('users')) {
            Schema::table('supplier_raw_materials', function (Blueprint $table) {
                $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    // =====================================================================
    // supplier_raw_material_batches  (new)
    // =====================================================================

    private function createSupplierRawMaterialBatches(): void
    {
        if (Schema::hasTable('supplier_raw_material_batches')) {
            return;
        }

        Schema::create('supplier_raw_material_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_raw_material_id');
            $table->unsignedBigInteger('user_id')->comment('Denormalised owner for fast scoping');
            $table->string('batch_code', 64);

            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);

            // NULL means "this product never expires" — Tools & Accessories and
            // Packaging. Everything else must carry a date at least 1 year out.
            $table->date('expiration_date')->nullable();

            $table->timestamp('received_at')->useCurrent();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_reason')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();

            $table->timestamps();

            $table->unique(['supplier_raw_material_id', 'batch_code'], 'srmb_material_batch_unique');
            $table->index(['supplier_raw_material_id', 'is_archived'], 'srmb_material_active_index');
            $table->index(['supplier_raw_material_id', 'expiration_date'], 'srmb_material_expiry_index');
            $table->index('user_id', 'srmb_user_index');

            $table->foreign('supplier_raw_material_id')
                ->references('id')->on('supplier_raw_materials')->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('archived_by')
                ->references('id')->on('users')->onDelete('set null');
        });
    }

    // =====================================================================
    // distributor_inventories
    // =====================================================================

    private function addColumnsToDistributorInventories(): void
    {
        if (! Schema::hasTable('distributor_inventories')) {
            return;
        }

        $missing = array_values(array_filter([
            'batch_code', 'expiration_date', 'source_procurement_request_id',
            'source_raw_material_id', 'received_at', 'is_archived',
            'archived_at', 'archive_reason', 'archived_by',
        ], fn ($c) => ! Schema::hasColumn('distributor_inventories', $c)));

        if ($missing) {
            Schema::table('distributor_inventories', function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        'batch_code'                    => $table->string('batch_code', 64)->nullable(),
                        'expiration_date'               => $table->date('expiration_date')->nullable(),
                        'source_procurement_request_id' => $table->unsignedBigInteger('source_procurement_request_id')->nullable(),
                        'source_raw_material_id'        => $table->unsignedBigInteger('source_raw_material_id')->nullable(),
                        'received_at'                   => $table->timestamp('received_at')->useCurrent(),
                        'is_archived'                   => $table->boolean('is_archived')->default(false),
                        'archived_at'                   => $table->timestamp('archived_at')->nullable(),
                        'archive_reason'                => $table->string('archive_reason')->nullable(),
                        'archived_by'                   => $table->unsignedBigInteger('archived_by')->nullable(),
                        default                         => null,
                    };
                }
            });
        }

        $this->addIndex('distributor_inventories', 'di_dist_product_archived_index', ['distributor_id', 'product_id', 'is_archived']);
        $this->addIndex('distributor_inventories', 'di_dist_expiration_index', ['distributor_id', 'expiration_date']);

        // The key that actually enables multiple batches per product. Only legal
        // now that batch_code exists. 2026_09_28_000001 drops the legacy
        // UNIQUE(distributor_id, product_id) that used to forbid this.
        // batch_code is nullable and MySQL allows unlimited NULLs in a UNIQUE
        // index, so rows without a batch code are not de-duplicated.
        $this->addUnique('distributor_inventories', 'di_dist_batch_unique', ['distributor_id', 'batch_code']);

        if (Schema::hasTable('procurement_requests')) {
            $this->addForeign(
                'distributor_inventories',
                'distributor_inventories_source_procurement_request_id_foreign',
                'source_procurement_request_id',
                'procurement_requests',
                'set null'
            );
        }

        if (Schema::hasTable('users')) {
            $this->addForeign(
                'distributor_inventories',
                'distributor_inventories_archived_by_foreign',
                'archived_by',
                'users',
                'set null'
            );
        }
    }

    // =====================================================================
    // distributor_inventory_batches  (new)
    // =====================================================================

    /**
     * Per-batch quantities for distributor stock.
     *
     * `distributor_inventories` deliberately KEEPS its original meaning: one row
     * per (distributor, product) whose `quantity` is the product total. Seventeen
     * controllers and reports read that column directly, and rewriting all of them
     * to aggregate would risk silent stock misreporting for no benefit.
     *
     * So the batch detail lives here instead, as a child of that row:
     *
     *   distributor_inventories          product total, ecommerce_status
     *     └── distributor_inventory_batches   one row per lot:
     *           batch_code, quantity, expiration_date, source_procurement_request_id
     *
     * A sale walks the children FEFO, decrements each child, then writes the new
     * sum back to the parent. Readers of the parent are unaffected.
     */
    private function createDistributorInventoryBatches(): void
    {
        if (Schema::hasTable('distributor_inventory_batches')) {
            return;
        }

        Schema::create('distributor_inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_inventory_id')->comment('Parent distributor_inventories row');
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('product_id');
            $table->string('batch_code', 64);

            $table->integer('quantity')->default(0);

            // NULL = never expires (Tools & Accessories, Packaging).
            $table->date('expiration_date')->nullable();

            $table->unsignedBigInteger('source_procurement_request_id')->nullable();
            $table->unsignedBigInteger('source_raw_material_id')->nullable();
            $table->timestamp('received_at')->useCurrent();

            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_reason')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();

            $table->timestamps();

            $table->unique(['distributor_inventory_id', 'batch_code'], 'dib_inventory_batch_unique');
            $table->index(['distributor_id', 'product_id', 'is_archived'], 'dib_dist_product_active_index');
            $table->index(['distributor_id', 'expiration_date'], 'dib_dist_expiry_index');
            $table->index('source_procurement_request_id', 'dib_source_request_index');

            $table->foreign('distributor_inventory_id')
                ->references('id')->on('distributor_inventories')->onDelete('cascade');
            $table->foreign('distributor_id')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')->on('distributor_products')->onDelete('cascade');
        });

        // Optional provenance links, only when the referenced tables are present.
        if (Schema::hasTable('procurement_requests')) {
            Schema::table('distributor_inventory_batches', function (Blueprint $table) {
                $table->foreign('source_procurement_request_id')
                    ->references('id')->on('procurement_requests')->onDelete('set null');
            });
        }

        if (Schema::hasTable('supplier_raw_materials') && Schema::hasTable('users')) {
            Schema::table('distributor_inventory_batches', function (Blueprint $table) {
                $table->foreign('source_raw_material_id')
                    ->references('id')->on('supplier_raw_materials')->onDelete('set null');
                $table->foreign('archived_by')
                    ->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    // =====================================================================
    // inactive_distributor_inventories
    // =====================================================================

    private function addColumnsToInactiveDistributorInventories(): void
    {
        if (! Schema::hasTable('inactive_distributor_inventories')) {
            return;
        }

        $missing = array_values(array_filter([
            'batch_code', 'expiration_date', 'source_procurement_request_id',
        ], fn ($c) => ! Schema::hasColumn('inactive_distributor_inventories', $c)));

        if ($missing) {
            Schema::table('inactive_distributor_inventories', function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        'batch_code'                    => $table->string('batch_code', 64)->nullable(),
                        'expiration_date'               => $table->date('expiration_date')->nullable(),
                        'source_procurement_request_id' => $table->unsignedBigInteger('source_procurement_request_id')->nullable(),
                        default                         => null,
                    };
                }
            });
        }
    }

    // =====================================================================
    // inventory_logs
    // =====================================================================

    private function addColumnsToInventoryLogs(): void
    {
        if (! Schema::hasTable('inventory_logs')) {
            return;
        }

        $missing = array_values(array_filter([
            'inventory_id', 'expiration_date', 'batch_code',
            'quantity_removed', 'event_type', 'user_id', 'notes',
        ], fn ($c) => ! Schema::hasColumn('inventory_logs', $c)));

        if ($missing) {
            Schema::table('inventory_logs', function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        'inventory_id'     => $table->unsignedBigInteger('inventory_id')->nullable(),
                        'expiration_date'  => $table->date('expiration_date')->nullable(),
                        'batch_code'       => $table->string('batch_code', 64)->nullable(),
                        'quantity_removed' => $table->integer('quantity_removed')->default(0),
                        'event_type'       => $table->string('event_type', 40)->default('procurement_receipt'),
                        'user_id'          => $table->unsignedBigInteger('user_id')->nullable(),
                        'notes'            => $table->text('notes')->nullable(),
                        default            => null,
                    };
                }
            });
        }

        $this->addIndex('inventory_logs', 'il_dist_product_index', ['distributor_id', 'product_id']);
        $this->addIndex('inventory_logs', 'il_event_type_index', ['event_type']);

        if (Schema::hasTable('distributor_inventories')) {
            $this->addForeign('inventory_logs', 'inventory_logs_inventory_id_foreign', 'inventory_id', 'distributor_inventories', 'set null');
        }
        if (Schema::hasTable('users')) {
            $this->addForeign('inventory_logs', 'inventory_logs_user_id_foreign', 'user_id', 'users', 'set null');
        }
    }

    // =====================================================================
    // procurement_requests
    // =====================================================================

    private function addColumnsToProcurementRequests(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        $missing = array_values(array_filter([
            'expiration_date', 'stock_reserved_at', 'stock_released_at', 'stock_consumed_at',
        ], fn ($c) => ! Schema::hasColumn('procurement_requests', $c)));

        if ($missing) {
            Schema::table('procurement_requests', function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        'expiration_date'     => $table->date('expiration_date')->nullable()->after('quantity'),
                        'stock_reserved_at'   => $table->timestamp('stock_reserved_at')->nullable(),
                        'stock_released_at'   => $table->timestamp('stock_released_at')->nullable(),
                        'stock_consumed_at'   => $table->timestamp('stock_consumed_at')->nullable(),
                        default               => null,
                    };
                }
            });
        }

        $this->addIndex('procurement_requests', 'pr_product_status_index', ['product_id', 'status']);
        $this->addIndex('procurement_requests', 'pr_expiration_index', ['expiration_date']);
    }

    // =====================================================================
    // order items
    // =====================================================================

    private function addColumnsToOrderItems(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $missing = array_values(array_filter(
            ['batch_code', 'expiration_date'],
            fn ($c) => ! Schema::hasColumn($table, $c)
        ));

        if (! $missing) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($missing) {
            foreach ($missing as $column) {
                match ($column) {
                    'batch_code'       => $blueprint->string('batch_code', 64)->nullable(),
                    'expiration_date'  => $blueprint->date('expiration_date')->nullable(),
                    default            => null,
                };
            }
        });
    }

    // =====================================================================
    // guarded helpers
    // =====================================================================

    private function addIndex(string $table, string $name, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (in_array($name, $this->indexNames($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($name, $columns) {
            $t->index($columns, $name);
        });
    }

    private function addUnique(string $table, string $name, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if (in_array($name, $this->indexNames($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($name, $columns) {
            $t->unique($columns, $name);
        });
    }

    private function addForeign(string $table, string $name, string $column, string $refTable, string $onDelete): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if (! Schema::hasTable($refTable)) {
            return;
        }

        if (in_array($name, $this->foreignKeyNames($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($column, $refTable, $onDelete) {
            $t->foreign($column)->references('id')->on($refTable)->onDelete($onDelete);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! in_array($name, $this->indexNames($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($name) {
            $t->dropIndex($name);
        });
    }

    /**
     * Names of the REAL foreign key constraints on a table.
     *
     * Deliberately does NOT use `SHOW INDEX`. A DROP FOREIGN KEY that fails, or a
     * table built by a different tool, can leave a plain *index* behind under the
     * foreign key's name. `SHOW INDEX` cannot tell the two apart, so a guard built
     * on it would happily try to drop a constraint that is not there and abort the
     * rollback. information_schema is the only reliable source.
     *
     * @return list<string>
     */
    private function foreignKeyNames(string $table): array
    {
        return array_values(array_unique(array_map(
            static fn ($row) => (string) $row->CONSTRAINT_NAME,
            \Illuminate\Support\Facades\DB::select(
                'SELECT DISTINCT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$table]
            )
        )));
    }

    private function dropForeign(string $table, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! in_array($name, $this->foreignKeyNames($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($name) {
            $t->dropForeign($name);
        });
    }

    private function dropColumns(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $present = array_values(array_filter(
            $columns,
            fn ($c) => Schema::hasColumn($table, $c)
        ));

        if (! $present) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($present) {
            $blueprint->dropColumn($present);
        });
    }

    /** @return list<string> */
    private function indexNames(string $table): array
    {
        return array_values(array_unique(array_map(
            static fn ($row) => (string) $row->Key_name,
            \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}`")
        )));
    }
};
