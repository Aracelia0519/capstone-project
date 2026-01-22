<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_managers', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to the parent distributor (user who created this HR manager)
            $table->foreignId('parent_distributor_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key to the HR manager's user account
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // HR manager information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // HR manager specific fields
            $table->string('position')->default('HR Manager');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            
            // ID verification fields
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            
            // HR manager specific documents
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_managers');
    }
};