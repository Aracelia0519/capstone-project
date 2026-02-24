<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('distributor_inventories', function (Blueprint $table) {
            $table->enum('ecommerce_status', ['not_deployed', 'pending', 'deployed'])
                  ->default('not_deployed')
                  ->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('distributor_inventories', function (Blueprint $table) {
            $table->dropColumn('ecommerce_status');
        });
    }
};