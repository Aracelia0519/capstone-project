<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('valid_id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('valid_id_photo')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable(); // ADD THIS LINE
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requirements');
    }
};