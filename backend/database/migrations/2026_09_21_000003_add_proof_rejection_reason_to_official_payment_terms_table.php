<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores the reason a Service Provider rejected a Client's on-hand
     * proof of payment (so the Client can see why it was not counted).
     */
    public function up(): void
    {
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->text('proof_rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('official_payment_terms', function (Blueprint $table) {
            $table->dropColumn('proof_rejection_reason');
        });
    }
};