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
        Schema::table('distributor_payroll_settings', function (Blueprint $table) {
            // 'none', 'prorated' (per minute), 'fixed_block' (e.g. per 15 mins)
            $table->string('late_deduction_policy')->default('none')->after('day_of_month');
            
            // The monetary amount to deduct
            $table->decimal('late_deduction_amount', 10, 2)->nullable()->after('late_deduction_policy');
            
            // The time block in minutes (only used if policy is 'fixed_block')
            $table->integer('late_deduction_minutes')->nullable()->after('late_deduction_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['late_deduction_policy', 'late_deduction_amount', 'late_deduction_minutes']);
        });
    }
};