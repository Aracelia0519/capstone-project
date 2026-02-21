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
        Schema::create('service_provider_addresses', function (Blueprint $table) {
            $table->id();
            
            // 1. Define the column first
            $table->unsignedBigInteger('service_provider_requirements_id');
            
            // 2. Define the foreign key with a custom, shorter constraint name ('sp_req_id_fk')
            $table->foreign('service_provider_requirements_id', 'sp_req_id_fk')
                  ->references('id')
                  ->on('service_provider_requirements')
                  ->onDelete('cascade');
                  
            $table->string('province')->default('Cavite');
            $table->string('city');
            $table->string('barangay');
            $table->text('block_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_addresses');
    }
};