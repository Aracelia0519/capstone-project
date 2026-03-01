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
        Schema::create('ec_delivery_remittances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('delivery_personnel_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('remittance_proof_path')->nullable();
            $table->timestamps();

            // Relationships
            $table->foreign('distributor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('delivery_personnel_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ec_delivery_remittances');
    }
};