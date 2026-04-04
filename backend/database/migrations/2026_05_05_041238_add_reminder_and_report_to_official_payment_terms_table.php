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
        Schema::table('official_payment_terms', function (Blueprint $table) {
            // Removed the after() constraint so it just appends to the table safely
            $table->integer('reminder_count')->default(0);
            $table->string('legal_report_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->dropColumn(['reminder_count', 'legal_report_path']);
        });
    }
};