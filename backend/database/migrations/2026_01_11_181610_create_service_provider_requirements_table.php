<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_provider_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Service Provider ID
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable(); // Front side
            $table->string('selfie_with_id_photo')->nullable(); // Selfie with ID
            $table->text('rejection_reason')->nullable(); // If rejected
            $table->enum('status', ['not_submitted', 'pending', 'verified', 'rejected'])->default('not_submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Add index for faster queries
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provider_requirements');
    }
};