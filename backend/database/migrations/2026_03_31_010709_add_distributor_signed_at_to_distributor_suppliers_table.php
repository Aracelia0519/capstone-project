<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the supplier signature timestamp.
 *
 * This file was previously an empty no-op because its body had been written into
 * the preceding migration (2026_03_31_004539). See the note there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('distributor_suppliers')) {
            return;
        }

        if (! Schema::hasColumn('distributor_suppliers', 'distributor_signed_at')) {
            Schema::table('distributor_suppliers', function (Blueprint $table) {
                $table->timestamp('distributor_signed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('distributor_suppliers')
            && Schema::hasColumn('distributor_suppliers', 'distributor_signed_at')) {
            Schema::table('distributor_suppliers', function (Blueprint $table) {
                $table->dropColumn('distributor_signed_at');
            });
        }
    }
};
