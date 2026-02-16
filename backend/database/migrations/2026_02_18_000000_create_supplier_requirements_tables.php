<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create Supplier Requirements Table
        Schema::create('supplier_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name')->nullable();
            
            // ID Details
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            
            // Photo Paths
            $table->string('valid_id_photo')->nullable();
            $table->string('dti_certificate_photo')->nullable();
            $table->string('mayor_permit_photo')->nullable();
            $table->string('barangay_clearance_photo')->nullable();
            
            // Business Registration
            $table->string('business_registration_number')->nullable();
            $table->string('business_registration_photo')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
        });

        // 2. Create Supplier Addresses Table
        Schema::create('supplier_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_requirements_id')->constrained('supplier_requirements')->onDelete('cascade');
            $table->string('province')->default('Cavite');
            $table->string('city');
            $table->string('barangay');
            $table->text('block_address'); // Block/Lot/Street/Subdivision
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_addresses');
        Schema::dropIfExists('supplier_requirements');
    }
};