<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add personnel_officer to the users table role enum safely using raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client','distributor','operational_distributor','service_provider','admin','hr_manager','finance_manager','employee','supplier','personnel_officer') NOT NULL DEFAULT 'client'");

        Schema::create('supplier_personnel_officers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->date('hire_date')->nullable();
            $table->string('position')->default('Personnel Officer');
            
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            $table->string('resume')->nullable();
            $table->string('employment_contract')->nullable();
            
            $table->enum('status', ['pending', 'active', 'inactive', 'on_leave'])->default('active');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_personnel_officers');
        
        // Revert users role enum (Note: this drops personnel_officer. Ensure no users have this role before rollback)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('client','distributor','operational_distributor','service_provider','admin','hr_manager','finance_manager','employee','supplier') NOT NULL DEFAULT 'client'");
    }
};