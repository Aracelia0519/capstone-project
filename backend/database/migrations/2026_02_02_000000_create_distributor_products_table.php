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
        Schema::create('distributor_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->string('category', 100);
            $table->string('type', 100);
            $table->string('name', 255);
            $table->string('sku_code', 100)->nullable()->unique();
            $table->string('size', 100);
            $table->string('color_code', 50)->nullable(); // Changed from 20 to 50
            $table->decimal('price', 12, 2);
            $table->decimal('cost', 12, 2)->nullable();
            $table->integer('min_stock_level')->default(10);
            $table->integer('max_stock_level')->default(100);
            $table->text('description')->nullable();
            $table->text('image_url')->nullable(); // Changed from string to text for Base64
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['distributor_id', 'is_active']);
            $table->index('category');
            $table->index('type');
            $table->index('sku_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_products');
    }
};