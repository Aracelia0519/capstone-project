<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('distributor_suppliers', function (Blueprint $table) {
            // Adding the distributor_signed_at column as nullable
            $table->timestamp('distributor_signed_at')->nullable()->after('agreement_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_suppliers', function (Blueprint $table) {
            $table->dropColumn('distributor_signed_at');
        });
    }
};