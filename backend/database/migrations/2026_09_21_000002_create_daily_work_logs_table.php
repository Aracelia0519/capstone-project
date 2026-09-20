<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks which calendar days the Service Provider actually worked on a
     * Daily-priced job. If a day is marked as NOT worked (worked = false),
     * the Client is NOT charged for that day.
     */
    public function up(): void
    {
        Schema::create('daily_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_request_id')->constrained('client_service_requests')->onDelete('cascade');
            $table->date('work_date');
            $table->boolean('worked')->default(true);
            $table->timestamps();

            $table->unique(['client_service_request_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_work_logs');
    }
};