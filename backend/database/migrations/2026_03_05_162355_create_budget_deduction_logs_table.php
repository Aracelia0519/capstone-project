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
        Schema::create('budget_deduction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('procurement_request_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            // Constraints
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('procurement_request_id')->references('id')->on('procurement_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_deduction_logs');
    }
};