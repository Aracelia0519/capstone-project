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
        Schema::table('supplier_raw_materials', function (Blueprint $table) {
            // Adds the weight column allowing decimals (e.g., 10.50), defaulting to 10.00 for safety
            $table->decimal('weight', 8, 2)->default(10.00)->after('size');
        });

        // Explicitly updates any pre-existing rows in the database to exactly 10.00 kg
        DB::table('supplier_raw_materials')->update(['weight' => 10.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_raw_materials', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};