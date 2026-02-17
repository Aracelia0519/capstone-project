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
        Schema::create('distributor_suppliers', function (Blueprint $table) {
            $table->id();
            // The Main Distributor (Business Owner)
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            
            // The Supplier
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            
            // Who initiated the request (e.g., Operational Distributor)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Status Flow: 
            // 1. pending_internal (Waiting for Main Distributor approval)
            // 2. pending_supplier (Waiting for Supplier approval)
            // 3. active (Partnership established)
            // 4. rejected (Rejected by either party)
            $table->enum('status', ['pending_internal', 'pending_supplier', 'active', 'rejected', 'disconnected'])->default('pending_internal');
            
            $table->text('request_message')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamp('distributor_approved_at')->nullable();
            $table->timestamp('supplier_approved_at')->nullable();
            
            $table->timestamps();
            
            // Ensure unique partnership requests
            $table->unique(['distributor_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_suppliers');
    }
};