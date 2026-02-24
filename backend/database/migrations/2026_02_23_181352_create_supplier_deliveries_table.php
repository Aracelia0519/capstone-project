<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_request_id');
            $table->unsignedBigInteger('delivery_personnel_id');
            $table->string('shipping_proof_path')->nullable();
            $table->enum('status', ['assigned', 'in_transit', 'delivered'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('procurement_request_id')
                  ->references('id')
                  ->on('procurement_requests')
                  ->onDelete('cascade');
                  
            $table->foreign('delivery_personnel_id')
                  ->references('id')
                  ->on('supplier_personnels')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_deliveries');
    }
};