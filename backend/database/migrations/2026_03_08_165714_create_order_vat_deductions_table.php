<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_vat_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('vatable_sales', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_vat_deductions');
    }
};