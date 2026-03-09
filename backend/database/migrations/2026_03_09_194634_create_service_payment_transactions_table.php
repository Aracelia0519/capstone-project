<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_term_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('provider_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // 'gcash' or 'on_hand'
            $table->string('reference_number')->nullable(); // PayMongo Session ID or Receipt Ref
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();

            $table->foreign('payment_term_id')->references('id')->on('official_payment_terms')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_payment_transactions');
    }
};