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
        Schema::create('service_provider_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_provider_id');
            
            $table->boolean('is_on_hand_enabled')->default(true);
            $table->boolean('is_gcash_enabled')->default(false);
            $table->string('gcash_number')->nullable();
            
            $table->timestamps();

            // Foreign key to link directly to the service provider's account
            $table->foreign('service_provider_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_payment_settings');
    }
};