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
        Schema::create('distributor_payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->enum('frequency', ['daily', 'weekly', 'bi-monthly', 'monthly'])->default('weekly');
            
            // For Weekly (e.g., 'friday')
            $table->string('day_of_week')->nullable();
            
            // For Bi-Monthly (e.g., '1-15')
            $table->string('bi_monthly_schedule')->nullable();
            
            // For Monthly (e.g., '1', '15', 'last')
            $table->string('day_of_month')->nullable();
            
            $table->timestamps();

            $table->foreign('distributor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_payroll_settings');
    }
};