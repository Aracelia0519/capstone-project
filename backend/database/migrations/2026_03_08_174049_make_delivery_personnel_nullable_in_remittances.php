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
        // 1. Drop the existing foreign key constraint temporarily
        DB::statement('ALTER TABLE ec_delivery_remittances DROP FOREIGN KEY ec_delivery_remittances_delivery_personnel_id_foreign');
        
        // 2. Modify the column to allow NULL values (for online GCash payments)
        DB::statement('ALTER TABLE ec_delivery_remittances MODIFY delivery_personnel_id bigint(20) UNSIGNED NULL');
        
        // 3. Re-add the foreign key constraint
        DB::statement('ALTER TABLE ec_delivery_remittances ADD CONSTRAINT ec_delivery_remittances_delivery_personnel_id_foreign FOREIGN KEY (delivery_personnel_id) REFERENCES hr_employees(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE ec_delivery_remittances DROP FOREIGN KEY ec_delivery_remittances_delivery_personnel_id_foreign');
        DB::statement('ALTER TABLE ec_delivery_remittances MODIFY delivery_personnel_id bigint(20) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE ec_delivery_remittances ADD CONSTRAINT ec_delivery_remittances_delivery_personnel_id_foreign FOREIGN KEY (delivery_personnel_id) REFERENCES hr_employees(id) ON DELETE CASCADE');
    }
};