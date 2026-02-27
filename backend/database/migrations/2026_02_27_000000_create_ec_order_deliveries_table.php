<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the existing enum to include 'prepared'
        DB::statement("ALTER TABLE client_orders MODIFY COLUMN status ENUM('pending','confirmed','prepared','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::create('ec_order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->onDelete('cascade');
            $table->foreignId('delivery_personnel_id')->constrained('hr_employees')->onDelete('cascade');
            $table->string('preparation_proof_path');
            $table->enum('status', ['assigned', 'in_transit', 'delivered', 'remitting', 'completed'])->default('assigned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ec_order_deliveries');
        
        // Revert the enum back to the original state
        DB::statement("ALTER TABLE client_orders MODIFY COLUMN status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
    }
};