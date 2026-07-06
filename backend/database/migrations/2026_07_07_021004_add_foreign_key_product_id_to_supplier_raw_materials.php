<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddForeignKeyProductIdToSupplierRawMaterials extends Migration
{
    public function up()
    {
        // Set any product_id that doesn't exist in supplier_raw_materials to NULL
        DB::table('procurement_requests')
            ->whereNotIn('product_id', DB::table('supplier_raw_materials')->pluck('id'))
            ->update(['product_id' => null]);

        // Now add the new foreign key
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreign('product_id')
                  ->references('id')
                  ->on('supplier_raw_materials')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
    }
}