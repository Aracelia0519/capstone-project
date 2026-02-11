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
        Schema::create('distributor_working_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->string('day_of_week'); // Monday, Tuesday, etc.
            $table->boolean('is_open')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('distributor_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Ensure a distributor can only have one entry per day
            $table->unique(['distributor_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_working_hours');
    }
};