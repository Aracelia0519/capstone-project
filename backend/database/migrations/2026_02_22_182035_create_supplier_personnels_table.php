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
        Schema::create('supplier_personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality')->default('Filipino');
            $table->text('address')->nullable();
            
            // Employment
            $table->string('personnel_type');
            $table->string('employment_type');
            $table->date('hire_date')->nullable();
            
            // Financial & Government
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();
            
            // Education & ID
            $table->string('educational_attainment')->nullable();
            $table->string('school_graduated')->nullable();
            $table->year('year_graduated')->nullable();
            $table->string('course')->nullable();
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            
            // File Paths
            $table->string('valid_id_photo')->nullable();
            $table->string('resume')->nullable();
            $table->string('employment_contract')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('nbi_clearance')->nullable();
            $table->string('police_clearance')->nullable();
            
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_personnels');
    }
};