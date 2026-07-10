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
        Schema::table('distributor_products', function (Blueprint $table) {
            // Adds a decimal column for weight. Adjust the precision (8, 2) and placement (after 'price') if needed.
            $table->decimal('weight', 8, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_products', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};