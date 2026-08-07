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
        Schema::create('service_provider_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->string('motto')->nullable();
            $table->text('bio')->nullable();
            $table->integer('experience_years')->default(0);
            $table->string('specialties')->nullable();
            $table->json('gallery_images')->nullable();
            $table->timestamps();
            
            // A provider should only have one portfolio
            $table->unique('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_portfolios');
    }
};