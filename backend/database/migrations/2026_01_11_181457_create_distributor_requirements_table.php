<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('company_name')->nullable(); // ✅ added

            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            $table->string('dti_certificate_photo')->nullable();
            $table->string('mayor_permit_photo')->nullable();
            $table->string('barangay_clearance_photo')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('business_registration_photo')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_requirements');
    }
};

