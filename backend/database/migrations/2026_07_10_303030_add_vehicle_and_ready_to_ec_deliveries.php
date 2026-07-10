<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ec_order_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('delivery_personnel_id');
            $table->boolean('is_ready_to_go')->default(false)->after('status');
            // Foreign key if you have an ecommerce_vehicles table
            // $table->foreign('vehicle_id')->references('id')->on('ecommerce_vehicles')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('ec_order_deliveries', function (Blueprint $table) {
            $table->dropColumn(['vehicle_id', 'is_ready_to_go']);
        });
    }
};