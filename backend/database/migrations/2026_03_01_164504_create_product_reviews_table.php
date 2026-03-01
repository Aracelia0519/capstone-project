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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('client_orders')->onDelete('cascade');
            $table->integer('rating')->comment('1 to 5 stars');
            $table->text('comment')->nullable();
            $table->timestamps();

            // Ensure a user can only review a specific product within a specific order once
            $table->unique(['client_id', 'product_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};