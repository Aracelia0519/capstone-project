<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->json('terms')->nullable()->after('proposed_end_date');
        });
    }

    public function down()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->dropColumn('terms');
        });
    }
};