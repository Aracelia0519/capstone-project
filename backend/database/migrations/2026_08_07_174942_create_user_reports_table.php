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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade')->comment('The user being reported');
            $table->foreignId('reported_by_id')->constrained('users')->onDelete('cascade')->comment('The user filing the report');
            $table->string('reporter_role')->comment('Role of the person reporting (client, etc.)');
            $table->string('reported_user_role')->comment('Role of the person being reported');
            $table->string('reason');
            $table->text('description');
            $table->date('incident_date');
            $table->string('evidence_path')->nullable();
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->timestamps(); // created_at serves as the 'date reported'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};