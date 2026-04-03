<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // 1. Drop the strict foreign key tied to client_orders
            $table->dropForeign(['order_id']);
            
            // 2. Make order_id nullable (since SPs won't use it)
            $table->unsignedBigInteger('order_id')->nullable()->change();
            
            // 3. Add a new column specifically for Service Provider orders
            $table->unsignedBigInteger('sp_order_id')->nullable()->after('order_id');
            $table->foreign('sp_order_id')->references('id')->on('sp_orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropForeign(['sp_order_id']);
            $table->dropColumn('sp_order_id');
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->foreign('order_id')->references('id')->on('client_orders')->onDelete('cascade');
        });
    }
};