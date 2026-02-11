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
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('distributor_id');
            $table->enum('type', ['Clock In', 'Clock Out']);
            $table->dateTime('requested_time'); // The time they tried to clock in/out
            $table->string('photo_proof')->nullable(); // Path to uploaded photo
            $table->text('reason')->nullable(); // Why they are requesting (e.g., "GPS Error", "Client Meeting")
            $table->decimal('latitude', 10, 8)->nullable(); // Capture whatever location they had, if any
            $table->decimal('longitude', 11, 8)->nullable();
            
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('admin_remarks')->nullable(); // HR comments
            $table->unsignedBigInteger('approved_by')->nullable(); // ID of HR/Admin who approved
            
            $table->timestamps();

            // Foreign Keys
            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_requests');
    }
};