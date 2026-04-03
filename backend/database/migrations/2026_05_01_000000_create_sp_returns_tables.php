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
        Schema::create('sp_return_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('sp_id'); // Service Provider ID
            $table->unsignedBigInteger('distributor_id');
            $table->string('reason');
            $table->string('proof_image_path');
            $table->string('status')->default('pending'); // pending, approved, rejected, shipped, completed
            $table->string('tracking_number')->nullable();
            $table->string('tracking_proof_path')->nullable();
            $table->timestamps();
        });

        Schema::create('sp_return_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_request_id');
            $table->unsignedBigInteger('sender_id'); // Can be SP or Distributor
            $table->unsignedBigInteger('receiver_id');
            $table->text('message')->nullable();
            $table->string('type')->default('text'); // text, image, system
            $table->string('file_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->foreign('return_request_id')->references('id')->on('sp_return_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_return_messages');
        Schema::dropIfExists('sp_return_requests');
    }
};