<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('status'); // e.g., 'Success' or 'Failed'
            $table->text('browser')->nullable();
            $table->text('failure_reason')->nullable(); // For tracking brute force / guessing
            $table->timestamp('logged_in_at')->nullable();
            $table->string('Fullname')->nullable();
            $table->string('role')->nullable();
            $table->timestamps(); // Handles created_at and updated_at automatically
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_logs');
    }
};