<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('official_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('official_deal_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('client_id');
            $table->string('payment_method'); // 'gcash' or 'on_hand'
            $table->string('payment_term'); // e.g. "50% payment first", "Service first before payment"
            $table->enum('status', ['pending', 'agreed', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('official_deal_id')->references('id')->on('official_deals')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('official_payment_terms');
    }
};