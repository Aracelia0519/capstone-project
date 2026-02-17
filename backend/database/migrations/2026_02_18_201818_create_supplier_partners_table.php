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
        Schema::create('supplier_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            // Link back to the original request for reference
            $table->foreignId('request_id')->nullable()->constrained('distributor_suppliers')->onDelete('set null');
            
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->date('partnership_start_date');
            $table->date('partnership_end_date')->nullable();
            
            // Performance metrics placeholders
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->integer('total_orders')->default(0);
            
            $table->timestamps();

            // Ensure unique pair
            $table->unique(['supplier_id', 'distributor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_partners');
    }
};