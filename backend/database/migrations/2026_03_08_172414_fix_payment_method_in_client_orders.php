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
        // We use a raw statement here to bypass doctrine/dbal requirements for altering ENUMs
        // This changes the column from strict ENUM('cod') to a flexible VARCHAR string
        DB::statement("ALTER TABLE client_orders MODIFY COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'cod'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE client_orders MODIFY COLUMN payment_method ENUM('cod') NOT NULL DEFAULT 'cod'");
    }
};