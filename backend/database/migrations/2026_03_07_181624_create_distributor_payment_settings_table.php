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
        Schema::create('distributor_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            
            $table->boolean('is_cod_enabled')->default(true);
            $table->boolean('is_gcash_enabled')->default(false);
            $table->string('gcash_number')->nullable();
            
            $table->timestamps();

            // Setup foreign key based on the primary users table (which distributors fall under)
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_payment_settings');
    }
};