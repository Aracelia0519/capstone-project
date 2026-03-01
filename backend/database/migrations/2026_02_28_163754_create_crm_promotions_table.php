<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Creator
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade'); // Distributor reference
            $table->unsignedBigInteger('product_id')->nullable(); // From distributor_products
            $table->string('name');
            $table->string('type'); // percentage, fixed, free_shipping, bogo
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('code')->unique();
            $table->integer('usage_limit')->default(100);
            $table->integer('used_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('pending'); // pending, active, inactive, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_promotions');
    }
};