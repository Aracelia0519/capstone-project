<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterStatusColumnInServiceProviderDistributors extends Migration
{
    public function up()
    {
        // Change the column to VARCHAR(20) to accept any status string
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->change();
        });
    }

    public function down()
    {
        // Rollback: you may restore the original type, but we keep it safe
        // This is a simple rollback that just sets it back to a string, but you might want to revert to enum
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->string('status', 20)->change(); // same as up, no data loss
        });
    }
}