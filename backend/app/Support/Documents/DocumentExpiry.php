<?php

namespace App\Support\Documents;

use Carbon\CarbonImmutable;

/**
 * The rules behind document-expiration renewals, in one place.
 *
 * Both the admin list and the action endpoints read their thresholds from here,
 * so the "is this close enough to expiry?" answer that fills the table can never
 * disagree with the "may I terminate this account?" answer that enforces a
 * decision. Hiding a button is a courtesy; these predicates are the rule.
 */
final class DocumentExpiry
{
    /**
     * How close a document has to be to appear on the renewals list.
     *
     * The requirement is "1 month away or less", read as a 30-day window.
     */
    public const RENEWAL_WINDOW_DAYS = 30;

    /**
     * Days past expiration before an account may be terminated.
     *
     * The notification promises termination "one week after the expiration date",
     * so 7 is the earliest date the promise permits.
     */
    public const TERMINATION_GRACE_DAYS = 7;

    /**
     * The two dated documents, keyed by the slug the API and UI share.
     *
     * @return array<string, string>
     */
    public static function documents(): array
    {
        return [
            'dti_certificate' => 'DTI Certificate',
            'mayor_permit'   => "Mayor's Permit",
        ];
    }

    public static function isTrackable(?string $key): bool
    {
        return array_key_exists($key, self::documents());
    }

    public static function label(string $key): string
    {
        return self::documents()[$key] ?? $key;
    }

    /**
     * Whole days from today until the document expires. Negative once overdue.
     *
     * Arithmetic is done on start-of-day timestamps rather than via a date-diff so
     * it cannot drift with the Carbon major version or across a DST boundary, and
     * so a document dated today reads as 0 rather than flipping to -1 partway
     * through the day. A document dated today is still valid: it expires tomorrow.
     */
    public static function daysRemaining(?string $expirationDate): ?int
    {
        if (blank($expirationDate)) {
            return null;
        }

        try {
            $expiry = CarbonImmutable::parse($expirationDate)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $today = CarbonImmutable::now()->startOfDay();

        // Cast to int: a float here would round mid-day and flip a boundary case.
        return (int) round(($expiry->getTimestamp() - $today->getTimestamp()) / 86400);
    }

    /**
     * Bucket a document for the UI: what colour and what wording it deserves.
     *
     * @return string one of: missing, expired, critical, warning, ok
     */
    public static function state(?string $expirationDate): string
    {
        $days = self::daysRemaining($expirationDate);

        if ($days === null) {
            return 'missing';
        }

        return match (true) {
            $days < 0                              => 'expired',
            $days <= self::TERMINATION_GRACE_DAYS  => 'critical',
            $days <= self::RENEWAL_WINDOW_DAYS     => 'warning',
            default                                => 'ok',
        };
    }

    /**
     * Belongs on the renewals list: dated, and inside the window.
     *
     * Overdue documents stay on the list (they are the ones that still need
     * action) but are reported separately so the UI can rank them first.
     */
    public static function needsRenewal(?string $expirationDate): bool
    {
        $days = self::daysRemaining($expirationDate);

        return $days !== null && $days <= self::RENEWAL_WINDOW_DAYS;
    }

    /**
     * Whether an account may be terminated because of this document.
     *
     * Enforced server-side on the action, never trusted from the client.
     */
    public static function canTerminate(?string $expirationDate): bool
    {
        $days = self::daysRemaining($expirationDate);

        return $days !== null && $days <= -self::TERMINATION_GRACE_DAYS;
    }

    /**
     * Whole days from today until this document may be acted on terminally.
     *
     * Null when it has no date, 0 once the grace period has elapsed, and negative
     * only if a document is somehow dated further out than the grace period
     * already allows, which the `max` prevents.
     */
    public static function daysUntilTerminationAllowed(?string $expirationDate): ?int
    {
        $days = self::daysRemaining($expirationDate);

        if ($days === null) {
            return null;
        }

        return max(0, self::TERMINATION_GRACE_DAYS + $days);
    }

    /**
     * The warning the admin is required to be able to send.
     */
    public static function warningMessage(string $documentKey): string
    {
        return sprintf(
            'Please renew your %s as it is nearing its expiration date. '
            . 'If not renewed, your account will be terminated one week after the expiration date.',
            self::label($documentKey)
        );
    }

    public static function warningTitle(string $documentKey): string
    {
        return sprintf('%s Renewal Due', self::label($documentKey));
    }

    /**
     * Flat shape used by list responses, so the table does not repeat date maths.
     *
     * @return array<string, mixed>
     */
    public static function summary(string $key, ?string $expirationDate): array
    {
        $days = self::daysRemaining($expirationDate);

        return [
            'key'            => $key,
            'label'          => self::label($key),
            'expiration_at'  => $expirationDate,
            'days_remaining' => $days,
            'state'          => self::state($expirationDate),
            'needs_renewal'  => self::needsRenewal($expirationDate),
            'can_terminate'  => self::canTerminate($expirationDate),
            'days_until_termination' => self::daysUntilTerminationAllowed($expirationDate),
        ];
    }
}
