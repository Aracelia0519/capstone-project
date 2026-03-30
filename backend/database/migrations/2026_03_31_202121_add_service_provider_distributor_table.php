<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->timestamp('distributor_signed_at')->nullable();
            $table->string('distributor_signature_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->dropColumn(['distributor_signed_at', 'distributor_signature_path']);
        });
    }
};