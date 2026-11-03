<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `returnItem()` writes `status = 'returned'` once every unit on a request has
 * gone back to the supplier. The column is an ENUM that never declared that
 * value, so MySQL strict mode rejected the update with
 *   "Data truncated for column 'status' at row 1"
 * — which only surfaced on a full return, because a partial return leaves the
 * request on 'delivered'.
 *
 * The value is appended to whatever the live ENUM already contains rather than
 * replacing the definition, so statuses added by other migrations are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        $current = [];

        foreach (DB::select("SHOW COLUMNS FROM procurement_requests LIKE 'status'") as $column) {
            if (! preg_match("/^enum\((.*)\)$/i", (string) $column->Type, $m)) {
                return; // Not an ENUM. Leave it alone.
            }

            $current = array_map(
                static fn ($v) => trim($v, "' "),
                explode(',', $m[1])
            );
        }

        if (in_array('returned', $current, true)) {
            return; // Already correct.
        }

        $merged = array_values(array_unique(array_merge($current, ['returned'])));
        sort($merged);

        $literal = implode(',', array_map(
            static fn ($v) => "'" . str_replace(["\\", "'"], ["\\\\", "''"], $v) . "'",
            $merged
        ));

        DB::statement("ALTER TABLE `procurement_requests` MODIFY `status` ENUM({$literal}) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Deliberately left in place. Narrowing the ENUM would truncate (or
        // refuse) every row already holding 'returned', destroying the history
        // of completed returns. This matches the precedent set by
        // 2026_02_19_000000 and 2026_09_28_000001.
    }
};
