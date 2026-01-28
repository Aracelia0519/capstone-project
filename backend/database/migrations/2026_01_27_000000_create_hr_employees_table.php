<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            
            // Company/HR Manager References
            $table->unsignedBigInteger('parent_distributor_id')->comment('References users.id where role=distributor');
            $table->unsignedBigInteger('hr_manager_id')->nullable()->comment('References hr_managers.id');
            $table->unsignedBigInteger('created_by_user_id')->comment('User who created this employee');
            
            // Basic Information
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->enum('marital_status', ['single', 'married', 'widowed', 'separated', 'divorced'])->default('single');
            $table->string('nationality')->default('Filipino');
            
            // Employment Details
            $table->string('department');
            $table->string('position');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'probationary', 'intern'])->default('full_time');
            $table->enum('employment_status', ['probationary', 'regular', 'contractual', 'resigned', 'terminated', 'retired'])->default('probationary');
            $table->date('hire_date');
            $table->date('probation_end_date')->nullable();
            $table->date('regularization_date')->nullable();
            
            // Compensation
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('salary_currency')->default('PHP');
            $table->enum('payment_frequency', ['monthly', 'bi_monthly', 'weekly', 'daily'])->default('monthly');
            
            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            
            // Government Numbers
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();
            
            // Identification
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            
            // Educational Background
            $table->string('educational_attainment')->nullable();
            $table->string('school_graduated')->nullable();
            $table->year('year_graduated')->nullable();
            $table->string('course')->nullable();
            
            // Documents
            $table->string('resume')->nullable();
            $table->string('employment_contract')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('nbi_clearance')->nullable();
            $table->string('police_clearance')->nullable();
            
            // Status and Notes
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('parent_distributor_id');
            $table->index('hr_manager_id');
            $table->index('created_by_user_id');
            $table->index('employee_code');
            $table->index('department');
            $table->index('employment_status');
            $table->index('status');
            $table->index(['parent_distributor_id', 'status']);
            $table->index('email');
            
            // Foreign Keys
            $table->foreign('parent_distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hr_manager_id')->references('id')->on('hr_managers')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hr_employees');
    }
};