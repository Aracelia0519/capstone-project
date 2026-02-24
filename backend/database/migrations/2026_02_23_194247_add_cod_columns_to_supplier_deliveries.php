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
        Schema::table('supplier_deliveries', function (Blueprint $table) {
            $table->string('payment_received_proof_path')->nullable()->after('arrival_proof_path');
            $table->string('remittance_proof_path')->nullable()->after('payment_received_proof_path');
        });

        // Update the ENUM to include 'remitting' and 'completed'
        DB::statement("ALTER TABLE supplier_deliveries MODIFY COLUMN status ENUM('assigned', 'in_transit', 'delivered', 'remitting', 'completed') NOT NULL DEFAULT 'assigned'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert records to safe enum values before dropping
        DB::statement("UPDATE supplier_deliveries SET status = 'delivered' WHERE status IN ('remitting', 'completed')");
        DB::statement("ALTER TABLE supplier_deliveries MODIFY COLUMN status ENUM('assigned', 'in_transit', 'delivered') NOT NULL DEFAULT 'assigned'");

        Schema::table('supplier_deliveries', function (Blueprint $table) {
            $table->dropColumn(['payment_received_proof_path', 'remittance_proof_path']);
        });
    }
};