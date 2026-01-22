<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_managers', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to the parent distributor (user who created this finance manager)
            $table->foreignId('parent_distributor_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key to the finance manager's user account
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Finance manager information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // ID verification fields
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            
            // Employment Information
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('position')->default('Finance Manager');
            
            // Additional Documents
            $table->string('resume')->nullable();
            $table->string('employment_contract')->nullable();
            
            // Status fields
            $table->enum('status', ['pending', 'active', 'inactive', 'on_leave'])->default('active');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('parent_distributor_id');
            $table->index('status');
            $table->index('employment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_managers');
    }
};