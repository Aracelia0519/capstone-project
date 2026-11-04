<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client requests a product from the Service Provider's inventory and the
     * two sides negotiate a markup percentage until one side approves.
     */
    public function up(): void
    {
        Schema::create('service_product_use_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_request_id')->constrained('client_service_requests')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('sp_inventory_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->integer('quantity')->default(1);
            $table->decimal('base_unit_price', 10, 2);
            $table->string('status')->default('pending_provider'); // pending_provider | negotiating | rejected | approved
            $table->text('rejection_reason')->nullable();
            $table->decimal('offer_percentage', 5, 2)->nullable();
            $table->string('offer_by')->nullable(); // provider | client
            $table->decimal('approved_percentage', 5, 2)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('material_expense_request_id')->nullable();
            $table->timestamps();

            $table->foreign('sp_inventory_id')->references('id')->on('sp_inventories')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('set null');
            $table->foreign('material_expense_request_id')->references('id')->on('material_expense_requests')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_product_use_requests');
    }
};
