<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cart Table
        Schema::create('client_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Orders Table
        Schema::create('client_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_fee', 12, 2);
            $table->decimal('grand_total', 12, 2);
            $table->enum('payment_method', ['cod'])->default('cod');
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('delivery_address');
            $table->timestamps();
        });

        // Order Items Table
        Schema::create('client_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('distributor_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_order_items');
        Schema::dropIfExists('client_orders');
        Schema::dropIfExists('client_carts');
    }
};