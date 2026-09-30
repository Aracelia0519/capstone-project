<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expiration dates for the two dated business documents, plus the audit trail for
 * an admin confirming the typed date matches the uploaded file.
 *
 * Nullable on purpose: this database already holds approved submissions with no
 * expiry on record, and forcing a value here would make them unreadable. New
 * submissions are made to supply the dates by validation instead, so the column
 * staying nullable costs nothing and backfilling a guessed date would be worse.
 *
 * Guards everywhere -- this schema is known to have drifted from its migrations.
 */
return new class extends Migration
{
    private const TABLES = [
        'distributor_requirements',
        'supplier_requirements',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'dti_certificate_expiration')) {
                    $blueprint->date('dti_certificate_expiration')->nullable()->after('dti_certificate_photo');
                }

                if (! Schema::hasColumn($table, 'mayor_permit_expiration')) {
                    $blueprint->date('mayor_permit_expiration')->nullable()->after('mayor_permit_photo');
                }

                if (! Schema::hasColumn($table, 'documents_verified_at')) {
                    $blueprint->timestamp('documents_verified_at')->nullable();
                }

                if (! Schema::hasColumn($table, 'documents_verified_by')) {
                    // No foreign key: users are soft/loosely managed elsewhere and
                    // a constraint here would block account deletion.
                    $blueprint->unsignedBigInteger('documents_verified_by')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = array_values(array_filter(
                ['dti_certificate_expiration', 'mayor_permit_expiration', 'documents_verified_at', 'documents_verified_by'],
                fn (string $column) => Schema::hasColumn($table, $column)
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->dropColumn($columns);
            });
        }
    }
};
