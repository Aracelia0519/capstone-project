<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anchors the "daily billing" counter for Daily-priced services.
     * It is set when the official deal is agreed (status becomes 'ongoing'),
     * so every calendar day from that date until the job is completed &
     * approved accrues one daily fee (the agreed deal price).
     */
    public function up(): void
    {
        Schema::table('official_deals', function (Blueprint $table) {
            $table->timestamp('daily_billing_started_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('official_deals', function (Blueprint $table) {
            $table->dropColumn('daily_billing_started_at');
        });
    }
};