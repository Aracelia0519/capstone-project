<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_distributors', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to the parent distributor (user who created this operational distributor)
            $table->foreignId('parent_distributor_id')->constrained('users')->onDelete('cascade');
            
            // Foreign key to the operational distributor's user account
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Operational distributor information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // Employment Information
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary'])->default('full_time');
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->default(0.00);
            $table->string('position')->default('Operational Distributor');
            
            // Documents
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            $table->string('resume')->nullable();
            $table->string('employment_contract')->nullable();
            
            // Status fields
            $table->enum('status', ['pending', 'active', 'inactive'])->default('active');
            
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
        Schema::dropIfExists('operational_distributors');
    }
};