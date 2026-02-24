<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Added this import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_rate_per_km', 8, 2)->default(15.00)->comment('Fee per kilometer');
            $table->decimal('rate_per_item', 8, 2)->default(5.00)->comment('Fee per quantity of item');
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->comment('Minimum total amount for free shipping');
            $table->timestamps();
        });

        // Insert default shipping rule
        DB::table('shipping_rules')->insert([
            'base_rate_per_km' => 15.00,
            'rate_per_item' => 5.00,
            'free_shipping_threshold' => 5000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rules');
    }
};