<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'in_transit' to the enum list
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','approved','ready','processing','prepared','shipped','in_transit','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert records back to 'shipped' before reverting the column enum to avoid data truncation errors
        DB::statement("UPDATE procurement_requests SET status = 'shipped' WHERE status = 'in_transit'");
        DB::statement("ALTER TABLE procurement_requests MODIFY COLUMN status ENUM('pending','approved','ready','processing','prepared','shipped','delivered','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }
};