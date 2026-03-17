<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('client_order_items')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->string('proof_image_path');
            $table->enum('status', ['pending', 'approved', 'rejected', 'shipped', 'completed'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_proof_path')->nullable();
            $table->timestamps();
        });

        Schema::create('client_return_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('client_return_requests')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'image', 'system'])->default('text');
            $table->string('file_path')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_return_messages');
        Schema::dropIfExists('client_return_requests');
    }
};