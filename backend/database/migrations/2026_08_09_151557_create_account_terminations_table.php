<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_terminations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('role');
            $table->unsignedBigInteger('terminated_by')->nullable();
            $table->string('termination_type')->nullable(); // e.g., 'Permanent', 'Temporary'
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'terminated', 'reversed'])->default('pending');
            $table->timestamp('terminated_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('terminated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reversed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_terminations');
    }
};