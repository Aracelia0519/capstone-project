<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Replace 'ec_order_deliveries' with your actual table name if it is different
        Schema::table('ec_order_deliveries', function (Blueprint $table) {
            $table->string('delivery_proof_path')->nullable();
            $table->string('payment_received_proof_path')->nullable();
            $table->string('remittance_proof_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ec_order_deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_proof_path', 
                'payment_received_proof_path', 
                'remittance_proof_path'
            ]);
        });
    }
};