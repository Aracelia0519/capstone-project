<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_returns', function (Blueprint $table) {
            $table->string('replacement_receipt_path')->nullable()->after('rejection_reason');
            $table->string('replacement_proof_path')->nullable()->after('replacement_receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_returns', function (Blueprint $table) {
            $table->dropColumn(['replacement_receipt_path', 'replacement_proof_path']);
        });
    }
};