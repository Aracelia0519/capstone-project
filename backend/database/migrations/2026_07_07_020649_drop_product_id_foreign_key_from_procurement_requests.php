<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProductIdForeignKeyFromProcurementRequests extends Migration
{
    public function up()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['product_id']);
            // Optionally change column type if needed (e.g., make it unsigned bigint)
            // $table->unsignedBigInteger('product_id')->change();
        });
    }

    public function down()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            // Re-add the foreign key if rolling back
            $table->foreign('product_id')->references('id')->on('distributor_products')->onDelete('set null');
        });
    }
}