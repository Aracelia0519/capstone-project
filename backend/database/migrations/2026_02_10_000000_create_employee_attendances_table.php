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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // Links to hr_employees id
            $table->unsignedBigInteger('distributor_id'); // Links to users id (the distributor)
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            
            // Geolocation Data
            $table->decimal('lat_in', 10, 8)->nullable();
            $table->decimal('long_in', 11, 8)->nullable();
            $table->decimal('lat_out', 10, 8)->nullable();
            $table->decimal('long_out', 11, 8)->nullable();
            
            // Status and Calculations
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->enum('status', ['Present', 'Late', 'Absent', 'Half Day'])->default('Present');
            $table->text('notes')->nullable(); // For rejection reasons or manual adjustments
            
            $table->timestamps();

            // Foreign Keys
            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};