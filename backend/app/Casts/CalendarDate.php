<?php

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * A calendar date with no time component, serialised as `Y-m-d`.
 *
 * Why this exists
 * ───────────────
 * The built-in `date` cast returns a Carbon, which Eloquent renders as an ISO-8601
 * instant in UTC. The application runs on `Asia/Manila` (UTC+8), so a DATE column
 * holding `2029-05-05` comes back out of the API as:
 *
 *     "2029-05-04T16:00:00.000000Z"
 *
 * A calendar date is not an instant. Midnight in Manila is 16:00 the *previous*
 * day in UTC, so any consumer that takes the first ten characters — which is what
 * every date input, every FEFO comparison in the browser, and every `toDateString()`
 * helper does — reads the wrong day. Stock would appear to expire a day early,
 * across every screen and every batch.
 *
 * Serialising as a plain `Y-m-d` string removes the timezone from the wire format
 * entirely. `2029-05-05` in, `2029-05-05` out, in every timezone.
 *
 * Use this for DATE columns only. Timestamps still need the ordinary
 * `datetime` cast, which legitimately carries an instant.
 */
class CalendarDate implements CastsAttributes, SerializesCastableAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Truncated to the first 10 characters on purpose: whatever the driver
        // hands back, only the calendar part is meaningful here.
        $date = substr((string) $value, 0, 10);

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function set(Model $model, string $key, $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Accept the ISO instants a client may send, and the plain `Y-m-d` a
        // <input type="date"> produces.
        $date = substr(trim((string) $value), 0, 10);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }

    /**
     * Emit the calendar day as a plain `Y-m-d` string.
     *
     * Two things about the framework are load-bearing here:
     *
     *  1. Eloquent *replaces* `$attributes[$key]` with whatever this returns
     *     (HasAttributes::addCastAttributesToArray), so the return value is the
     *     value itself, not a `[$key => $value]` fragment. The fragment form is
     *     only correct for casts whose value is a container spreading over
     *     several attributes.
     *
     *  2. By the time this is called, Eloquent has already run the DateTime
     *     through `Model::serializeDate()`, which renders an ISO instant in UTC.
     *     Re-reading that string would hand back the previous day, because local
     *     midnight is 16:00 the day before in UTC. So the day is taken from the
     *     RAW attribute instead — the value as stored, which is a calendar day by
     *     construction and has no timezone to lose.
     */
    public function serialize(Model $model, string $key, $value, array $attributes): ?string
    {
        $raw = $attributes[$key] ?? $value;

        if ($raw === null || $raw === '') {
            return null;
        }

        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d');
        }

        return substr((string) $raw, 0, 10);
    }
}
