<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table for per-order financial tracking
        Schema::create('ec_order_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('client_orders')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2)->comment('Grand Total');
            $table->decimal('vat_deduction', 12, 2)->comment('VAT Amount');
            $table->decimal('total_sales', 12, 2)->comment('Amount - VAT');
            $table->timestamps();
        });

        // Table for distributor total cumulative sales
        Schema::create('distributor_overall_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade')->unique();
            $table->decimal('total_revenue', 15, 2)->default(0)->comment('Cumulative VAT-exclusive sales');
            $table->integer('total_sales_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec_order_financials');
        Schema::dropIfExists('distributor_overall_sales');
    }
};