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
        Schema::create('service_survey_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_request_id')->constrained('client_service_requests')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->text('agreement_text');
            $table->string('storage_path');
            $table->enum('status', ['pending_client', 'signed', 'in_progress', 'completed'])->default('pending_client');
            
            $table->timestamp('provider_signed_at')->nullable();
            $table->timestamp('client_signed_at')->nullable();
            $table->timestamp('survey_started_at')->nullable();
            $table->timestamp('survey_completed_at')->nullable();
            
            $table->string('provider_signature')->nullable();
            $table->string('client_signature')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_survey_agreements');
    }
};