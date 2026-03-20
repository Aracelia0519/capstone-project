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
        Schema::create('inactive_distributor_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->string('previous_ecommerce_status')->nullable();
            $table->timestamps();

            // Corrected foreign key table names based on your database schema
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inactive_distributor_inventories');
    }
};