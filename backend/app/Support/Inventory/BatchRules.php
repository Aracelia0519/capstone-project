<?php

namespace App\Support\Inventory;

use Carbon\CarbonImmutable;

/**
 * Single source of truth for every batch/expiration rule in the system.
 *
 * The whole application funnels through this class so the supplier form, the
 * procurement request, the distributor receiving screen and the e-commerce
 * checkout can never disagree about what "valid" means.
 *
 * THE RULES
 * ─────────
 *  1. Tools & Accessories and Packaging are NON-PERISHABLE.
 *     Their expiration date is OPTIONAL. If one is supplied it must still honour
 *     the 1-year minimum, because a wrong date is worse than no date.
 *
 *  2. Every OTHER category REQUIRES an expiration date.
 *
 *  3. Any expiration date must be AT LEAST ONE YEAR after the moment the stock
 *     enters the system. One year, not one month, not 30 days.
 *
 *  4. Expired stock is never sellable and never procurable. It can only be
 *     archived.
 */
final class BatchRules
{
    /**
     * The minimum shelf life, in years, required on every incoming batch.
     */
    public const MINIMUM_SHELF_LIFE_YEARS = 1;

    /**
     * Categories whose expiration date is optional.
     *
     * Matched case-insensitively as a substring so that "Tools and Accessories",
     * "Application Tools", "Safety Equipment" and "Packaging & Containers" all
     * resolve correctly without having to enumerate every leaf.
     *
     * @var list<string>
     */
    public const NON_PERISHABLE_CATEGORIES = [
        'tool',
        'accessor',
        'packaging',
        'safety equipment',
        'surface preparation',
    ];

    /**
     * Are expiration dates optional for this category?
     */
    public static function isExpirationOptional(?string $category): bool
    {
        if ($category === null || trim($category) === '') {
            // Unknown category: be strict, we cannot prove it is non-perishable.
            return false;
        }

        $needle = mb_strtolower(trim($category));

        foreach (self::NON_PERISHABLE_CATEGORIES as $token) {
            if (str_contains($needle, $token)) {
                return true;
            }
        }

        return false;
    }

    public static function requiresExpiration(?string $category): bool
    {
        return ! self::isExpirationOptional($category);
    }

    /**
     * The earliest acceptable expiration date for stock received at $receivedAt.
     */
    public static function minimumExpirationDate(
        ?string $category = null,
        CarbonImmutable|\DateTimeInterface|null $receivedAt = null
    ): CarbonImmutable {
        $receivedAt ??= CarbonImmutable::now();

        return CarbonImmutable::instance($receivedAt)->addYears(self::MINIMUM_SHELF_LIFE_YEARS)->startOfDay();
    }

    /**
     * Is this date far enough in the future to be accepted?
     */
    public static function isFarEnoughOut(
        mixed $date,
        ?string $category = null,
        CarbonImmutable|\DateTimeInterface|null $receivedAt = null
    ): bool {
        if (self::toDateStringValue($date) === null) {
            // Absence is handled separately by requiresExpiration().
            return true;
        }

        $parsed = self::parse($date);

        if ($parsed === null) {
            return false;
        }

        return ! $parsed->lessThan(self::minimumExpirationDate($category, $receivedAt));
    }

    /**
     * Has this date already passed? Used to block procurement and sales.
     *
     * Accepts a string or a DateTimeInterface because Eloquent hands over a
     * Carbon once the `date` cast has run.
     */
    public static function isExpired(
        mixed $date,
        CarbonImmutable|\DateTimeInterface|null $now = null
    ): bool {
        if (self::toDateStringValue($date) === null) {
            return false; // Non-perishable.
        }

        $parsed = self::parse($date);

        if ($parsed === null) {
            // An unparseable date must never be treated as "still good".
            return true;
        }

        $now = $now ? CarbonImmutable::instance($now) : CarbonImmutable::now();

        return $parsed->lessThan($now->startOfDay());
    }

    /**
     * Days until expiry, or null when the item never expires.
     * Negative once expired.
     */
    public static function daysUntilExpiry(
        mixed $date,
        CarbonImmutable|\DateTimeInterface|null $now = null
    ): ?int {
        if (self::toDateStringValue($date) === null) {
            return null;
        }

        $parsed = self::parse($date);

        if ($parsed === null) {
            return null;
        }

        $now = $now ? CarbonImmutable::instance($now) : CarbonImmutable::now();

        // Deliberately plain timestamp arithmetic rather than `diffInDays()`.
        //
        // Carbon 3 reversed the argument order of `diffInDays()`: in Carbon 2
        // `$a->diffInDays($b)` meant "b minus a", in Carbon 3 it means "a minus b".
        // Code written against either version compiles and runs but returns the
        // opposite sign, which here turned a batch expiring in 950 days into one
        // reported as 950 days overdue — and `health()` buckets on this sign, so
        // every future-dated batch was being coloured as expired.
        //
        // Both sides are snapped to local midnight first, so the difference is a
        // whole number of calendar days; `round()` absorbs the 23h and 25h days
        // a DST transition introduces.
        return (int) round(
            ($parsed->startOfDay()->getTimestamp() - $now->startOfDay()->getTimestamp()) / 86400
        );
    }

    /**
     * Buckets a batch for the UI so every screen colours stock identically.
     *
     * @return 'expired'|'critical'|'warning'|'good'|'non_perishable'
     */
    public static function health(mixed $date, CarbonImmutable|\DateTimeInterface|null $now = null): string
    {
        if (self::toDateStringValue($date) === null) {
            return 'non_perishable';
        }

        $days = self::daysUntilExpiry($date, $now);

        if ($days === null) {
            return 'non_perishable';
        }

        return match (true) {
            $days < 0                                  => 'expired',
            $days <= 30                                => 'critical',
            $days <= 90                                => 'warning',
            default                                    => 'good',
        };
    }

    /**
     * FEFO ordering for a query builder.
     *
     * MySQL sorts NULLs FIRST on ASC, which would make every non-perishable batch
     * get consumed before any dated batch — the exact opposite of what we want.
     * This pushes NULLs to the back explicitly.
     *
     * @return string
     */
    public static function fefoOrderBy(string $column = 'expiration_date'): string
    {
        return "{$column} IS NULL ASC, {$column} ASC, id ASC";
    }

    /**
     * Human readable version of the minimum rule, reused by API error messages so
     * the backend and the frontend never word it differently.
     */
    public static function minimumRuleMessage(): string
    {
        return 'The expiration date must be at least '
            . self::MINIMUM_SHELF_LIFE_YEARS
            . ' year from today ('
            . self::minimumExpirationDate()->toDateString()
            . ' or later).';
    }

    // -----------------------------------------------------------------

    /**
     * Normalise anything date-shaped into a plain `Y-m-d` string.
     *
     * Eloquent casts `expiration_date` to a `date`, so a model read from the
     * database hands these helpers a Carbon instance while a freshly created one
     * still holds the raw string. Both have to work, and they have to produce the
     * SAME answer — otherwise a batch is "not expired" on the screen that created
     * it and "expired" on the screen that reads it back.
     *
     * The Carbon branch matters more than it looks: `__toString()` renders local
     * time ("2029-05-05 00:00:00" under Asia/Manila), which a strict `Y-m-d`
     * parse rejects. Taking `toDateString()` on the instance itself sidesteps the
     * format mismatch and the UTC re-serialisation that would otherwise shift the
     * day.
     */
    private static function toDateStringValue(mixed $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        if (is_string($date)) {
            $trimmed = trim($date);

            return $trimmed === '' ? null : $trimmed;
        }

        if (is_object($date) && method_exists($date, 'toDateString')) {
            return (string) $date->toDateString();
        }

        return trim((string) $date);
    }

    /**
     * Parse a date into midnight local time, or null when it is unusable.
     *
     * Returns null rather than guessing: an unparseable date must never be
     * silently treated as "valid", because every caller here is a safety check.
     */
    private static function parse(mixed $date): ?CarbonImmutable
    {
        $value = self::toDateStringValue($date);

        if ($value === null) {
            return null;
        }

        try {
            // Only the leading Y-m-d matters; a timestamp suffix ("2029-05-05
            // 00:00:00", "2029-05-05T00:00:00.000000Z") is tolerated explicitly
            // rather than relying on createFromFormat's trailing-data leniency.
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m) === 1) {
                if (! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                    return null;
                }

                return CarbonImmutable::createFromFormat(
                    'Y-m-d',
                    "{$m[1]}-{$m[2]}-{$m[3]}"
                )->startOfDay();
            }

            return CarbonImmutable::createFromFormat('Y-m-d', $value)?->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
