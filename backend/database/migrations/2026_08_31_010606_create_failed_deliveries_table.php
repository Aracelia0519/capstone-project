<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Alter ENUMs safely to support the new statuses
        DB::statement("ALTER TABLE ec_order_deliveries MODIFY COLUMN status ENUM('assigned','in_transit','delivered','remitting','completed','ready_for_pickup','returning_to_hq','failed_delivery') NOT NULL DEFAULT 'assigned'");
        DB::statement("ALTER TABLE client_orders MODIFY COLUMN status ENUM('pending','confirmed','prepared','ready_for_pickup','shipped','delivered','cancelled','failed_delivery') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE sp_orders MODIFY COLUMN status ENUM('pending','confirmed','prepared','ready_for_pickup','shipped','delivered','cancelled','failed_delivery') NOT NULL DEFAULT 'pending'");

        Schema::create('failed_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ec_order_delivery_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('sp_order_id')->nullable();
            $table->unsignedBigInteger('delivery_personnel_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('References the client or service provider who ordered');
            $table->string('client_name');
            $table->text('delivery_address');
            $table->text('reason')->nullable();
            $table->integer('failed_attempts')->default(1);
            $table->timestamps();

            $table->foreign('ec_order_delivery_id')->references('id')->on('ec_order_deliveries')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
            $table->foreign('sp_order_id')->references('id')->on('sp_orders')->onDelete('cascade');
            $table->foreign('delivery_personnel_id')->references('id')->on('hr_employees')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('failed_deliveries');
    }
};