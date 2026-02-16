<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            // Link to the hr_employees table
            $table->unsignedBigInteger('employee_id');
            // Link to the distributor (business owner) for filtering
            $table->unsignedBigInteger('distributor_id');
            
            $table->string('type'); // vacation, sick, emergency, maternity
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('duration', 5, 1); // Number of days (allows 0.5 for half day)
            $table->text('reason');
            
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // ID of HR or Admin who approved
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('employee_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
};