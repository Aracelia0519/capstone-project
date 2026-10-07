<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-lot detail for stock that has been moved out of the active supply chain.
 *
 * When units are marked "unavailable for selling" they are drawn from specific
 * batches and parked here, keeping each lot's batch code and expiration date.
 * Reactivation recreates those exact lots instead of inventing a new one, so a
 * deactivate/reactivate round-trip no longer rewrites the stock's expiry.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inactive_distributor_inventory_batches')) {
            return;
        }

        Schema::create('inactive_distributor_inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inactive_distributor_inventory_id')
                ->comment('Parent inactive_distributor_inventories row');

            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('product_id');

            // Copied verbatim from the batch the units came out of. NULL means
            // the inactive record was created before batch tracking (or the
            // units only ever existed on a stale rollup).
            $table->string('batch_code', 64)->nullable();
            $table->date('expiration_date')->nullable();

            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->foreign('inactive_distributor_inventory_id', 'idib_inactive_fk')
                ->references('id')
                ->on('inactive_distributor_inventories')
                ->onDelete('cascade');

            $table->index(['distributor_id', 'product_id'], 'idib_dist_product_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inactive_distributor_inventory_batches');
    }
};