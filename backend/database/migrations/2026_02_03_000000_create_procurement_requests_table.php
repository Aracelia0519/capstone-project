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
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id')->nullable()->comment('User who made the request');
            $table->unsignedBigInteger('distributor_id')->nullable()->comment('Distributor who owns the product');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('request_code')->unique()->comment('Auto-generated request code');
            $table->string('product_name');
            $table->string('category');
            $table->string('supplier');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'processing', 'shipped', 'delivered', 'rejected', 'cancelled'])->default('pending');
            $table->string('shipping_method')->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('instructions')->nullable();
            $table->date('required_by_date')->nullable();
            $table->date('request_date');
            
            // Finance approval fields
            $table->unsignedBigInteger('finance_approved_by')->nullable();
            $table->timestamp('finance_approved_at')->nullable();
            $table->unsignedBigInteger('finance_rejected_by')->nullable();
            $table->timestamp('finance_rejected_at')->nullable();
            
            // General fields
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('set null');
            $table->foreign('finance_approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('finance_rejected_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['requester_id', 'status']);
            $table->index(['distributor_id', 'status']);
            $table->index('request_code');
            $table->index('status');
            $table->index('priority');
            $table->index('request_date');
            $table->index(['finance_approved_by', 'status']);
            $table->index(['finance_rejected_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_requests');
    }
};