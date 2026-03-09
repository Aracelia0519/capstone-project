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
        Schema::create('client_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            
            $table->string('gcash_number')->nullable();
            
            $table->timestamps();

            // Foreign key to link directly to the client's account
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_payment_settings');
    }
};