<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the dedicated inventory table
        Schema::create('distributor_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('cascade');
            
            // Ensure a distributor only has one inventory record per product
            $table->unique(['distributor_id', 'product_id']);
        });

        // 2. Add tracking to procurement requests so we don't pull them twice
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('procurement_requests', 'moved_to_inventory')) {
                $table->boolean('moved_to_inventory')->default(false)->after('status');
            }
        });

        // 3. Create a log table for stock movements (audit trail)
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('procurement_request_id')->nullable();
            $table->integer('quantity_added');
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('cascade');
            $table->foreign('procurement_request_id')->references('id')->on('procurement_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('distributor_inventories');
        
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'moved_to_inventory')) {
                $table->dropColumn('moved_to_inventory');
            }
        });
    }
};