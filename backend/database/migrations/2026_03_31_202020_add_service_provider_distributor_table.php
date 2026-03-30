<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->string('agreement_path')->nullable();
            $table->timestamp('sp_signed_at')->nullable();
            $table->string('sp_signature_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_provider_distributors', function (Blueprint $table) {
            $table->dropColumn(['agreement_path', 'sp_signed_at', 'sp_signature_path']);
        });
    }
};
