<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: This migration previously contained the body of the NEXT migration
 * (it added `distributor_signed_at ... ->after('agreement_path')` while
 * `agreement_path` did not exist yet), which made every fresh install fail with
 *
 *   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'agreement_path'
 *                   in 'distributor_suppliers'
 *
 * The pair of files was swapped. Both now do what their filename says, and both
 * are guarded so a database that already has the column is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('distributor_suppliers')) {
            return;
        }

        if (! Schema::hasColumn('distributor_suppliers', 'agreement_path')) {
            Schema::table('distributor_suppliers', function (Blueprint $table) {
                $table->string('agreement_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('distributor_suppliers')
            && Schema::hasColumn('distributor_suppliers', 'agreement_path')) {
            Schema::table('distributor_suppliers', function (Blueprint $table) {
                $table->dropColumn('agreement_path');
            });
        }
    }
};
