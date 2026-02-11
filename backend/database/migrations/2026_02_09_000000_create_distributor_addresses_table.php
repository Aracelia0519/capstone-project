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
        Schema::create('distributor_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_requirements_id')
                  ->constrained('distributor_requirements')
                  ->onDelete('cascade');
            $table->string('province')->default('Cavite');
            $table->string('city');
            $table->string('barangay');
            $table->text('block_address'); // Stores Block, Lot, Street, Subdivision
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
        Schema::dropIfExists('distributor_addresses');
    }
};