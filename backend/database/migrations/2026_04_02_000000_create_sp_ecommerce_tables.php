<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('sp_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('users')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('payment_method')->default('cod');
            $table->enum('status', ['pending', 'confirmed', 'prepared', 'ready_for_pickup', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('delivery_address');
            $table->timestamps();
        });

        Schema::create('sp_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp_order_id')->constrained('sp_orders')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_order_items');
        Schema::dropIfExists('sp_orders');
        Schema::dropIfExists('sp_carts');
    }
};