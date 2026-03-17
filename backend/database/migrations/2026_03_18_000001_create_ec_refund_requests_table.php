<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ec_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('client_return_requests')->onDelete('cascade');
            $table->foreignId('distributor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Employee/OpDist who requested it
            $table->decimal('amount', 10, 2);
            $table->string('receipt_proof_path'); // Photo proving the returned item was received physically
            $table->string('client_gcash_number')->nullable(); // Saved here if the client didn't have one in DB
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Finance manager ID
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ec_refund_requests');
    }
};