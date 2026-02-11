<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_code')->unique(); // e.g., PAY-202602-001
            $table->unsignedBigInteger('user_id'); // Links to users table
            $table->string('user_type'); // 'employee', 'hr_manager', 'finance_manager', 'operational_distributor'
            $table->string('department');
            $table->string('position');
            
            // Period
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');

            // Financials
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            
            // Deductions (PH Standards)
            $table->decimal('sss_contribution', 10, 2)->default(0);
            $table->decimal('philhealth_contribution', 10, 2)->default(0);
            $table->decimal('pagibig_contribution', 10, 2)->default(0);
            $table->decimal('withholding_tax', 10, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            
            // Net
            $table->decimal('net_pay', 12, 2)->default(0);
            
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable(); // Who processed it
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};