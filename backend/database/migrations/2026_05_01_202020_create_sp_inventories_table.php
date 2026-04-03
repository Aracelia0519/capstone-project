<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sp_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        // Add a flag to track if an order item has already been moved to inventory
        Schema::table('sp_order_items', function (Blueprint $table) {
            $table->boolean('added_to_inventory')->default(false)->after('price');
        });
    }

    public function down()
    {
        Schema::table('sp_order_items', function (Blueprint $table) {
            $table->dropColumn('added_to_inventory');
        });
        Schema::dropIfExists('sp_inventories');
    }
};