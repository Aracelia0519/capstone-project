<?php

namespace App\Services;

use App\Models\IdentityVerificationResult;
use App\Models\User;

/**
 * Shared logic for the automatic "Identity & Location Verification" credential check.
 *
 * The browser performs facial recognition (selfie vs ID photo) and OCR text
 * extraction on the uploaded ID. Those raw results are sent to the API together
 * with the submission. This service re-validates the textual credentials
 * (first name, last name and ID number) against the user's account record and
 * produces the final "credentials matched / not matched" decision that the
 * admin uses to approve or withhold account activation.
 */
class IdentityVerificationService
{
    /**
     * Normalize a string for comparison (lowercase, strip non-alphanumeric).
     */
    public static function normalize($value)
    {
        if ($value === null) {
            return '';
        }

        return strtolower((string) preg_replace('/[^A-Za-z0-9]/u', '', $value));
    }

    /**
     * Check whether the user's first name and last name appear in the OCR text.
     *
     * The browser-side matcher tolerates OCR noise (e.g. "JUAN" read as "JUUAN")
     * and accepts surname-first or given-first order. This re-validation mirrors
     * that behaviour so the client preview and the admin panel agree:
     *
     *  1. fast path - exact normalized first/last name found as substrings, then
     *  2. fuzzy path - the FIRST and LAST words of the typed name must both be
     *     present among the OCR words (any order), allowing a small edit
     *     distance for character-level OCR misreads. Middle names / initials
     *     printed on the ID are ignored.
     */
    public static function namesMatch(?User $user, $ocrText)
    {
        if (!$user || !$ocrText) {
            return false;
        }

        $haystack = static::normalize($ocrText);
        $firstName = static::normalize($user->first_name);
        $lastName = static::normalize($user->last_name);

        if ($firstName === '' && $lastName === '') {
            return false;
        }

        // Fast path: clean OCR, either order works because both names just
        // need to appear somewhere in the text.
        if (($firstName === '' || str_contains($haystack, $firstName))
            && ($lastName === '' || str_contains($haystack, $lastName))) {
            return true;
        }

        // Fuzzy path: tolerate OCR noise the same way the browser does.
        $ocrTokens = static::wordTokens($ocrText);
        if (count($ocrTokens) === 0) {
            return false;
        }

        $typedTokens = static::wordTokens($user->first_name . ' ' . $user->last_name);
        if (count($typedTokens) === 0) {
            return false;
        }

        $first = $typedTokens[0];
        $last = $typedTokens[count($typedTokens) - 1];

        if ($first === $last) {
            return static::tokenPresent($ocrTokens, $first);
        }

        return static::tokenPresent($ocrTokens, $first)
            && static::tokenPresent($ocrTokens, $last);
    }

    /**
     * Split a name into lowercase word tokens (keeps accented letters such as
     * Ñ, é) - equivalent to the browser's wordTokens().
     */
    protected static function wordTokens($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        preg_match_all('/\p{L}+/u', mb_strtolower((string) $value), $matches);

        return $matches[0] ?? [];
    }

    /**
     * Whether any OCR token matches the expected word, allowing a small edit
     * distance for OCR noise (e.g. "CRVZ" vs "CRUZ", "JUAN" vs "JULIAN").
     * Short tokens (< 4 chars) must match exactly to avoid false positives.
     */
    protected static function tokenPresent(array $tokens, string $needle)
    {
        if (in_array($needle, $tokens, true)) {
            return true;
        }

        $length = mb_strlen($needle);
        if ($length < 4) {
            return false;
        }

        $maxDist = $length <= 4 ? 1 : ($length <= 8 ? 2 : 3);

        foreach ($tokens as $token) {
            if (abs(mb_strlen($token) - $length) <= $maxDist
                && static::levenshteinUtf8($token, $needle) <= $maxDist) {
                return true;
            }
        }

        return false;
    }

    /**
     * Character-based Levenshtein distance (UTF-8 safe, so accented letters
     * count as a single edit, matching the browser's JS implementation).
     */
    protected static function levenshteinUtf8($a, $b)
    {
        $aChars = preg_split('//u', (string) $a, -1, PREG_SPLIT_NO_EMPTY);
        $bChars = preg_split('//u', (string) $b, -1, PREG_SPLIT_NO_EMPTY);

        $m = count($aChars);
        $n = count($bChars);

        if ($m === 0) {
            return $n;
        }
        if ($n === 0) {
            return $m;
        }

        $prev = range(0, $n);

        for ($i = 1; $i <= $m; $i++) {
            $cur = [$i];
            for ($j = 1; $j <= $n; $j++) {
                $cur[$j] = min(
                    $prev[$j] + 1,
                    $cur[$j - 1] + 1,
                    $prev[$j - 1] + ($aChars[$i - 1] === $bChars[$j - 1] ? 0 : 1)
                );
            }
            $prev = $cur;
        }

        return $prev[$n];
    }

    /**
     * Check whether the typed ID number matches the value extracted from the ID.
     * Comparison is fuzzy: separators (spaces, dashes, dots) are ignored.
     */
    public static function idNumberMatches(string $typedIdNumber, $extractedIdNumber)
    {
        $typed = static::normalize($typedIdNumber);
        $extracted = static::normalize($extractedIdNumber);

        if ($typed === '') {
            return false;
        }

        if ($extracted === '') {
            return false;
        }

        return $extracted === $typed
            || str_contains($extracted, $typed)
            || str_contains($typed, $extracted);
    }

    /**
     * Produce the final decision with human readable reasons.
     */
    public static function decide(bool $faceMatch, bool $nameMatch, bool $idNumberMatch)
    {
        $reasons = [];

        if (!$faceMatch) {
            $reasons[] = 'The face on your selfie does not match the face on your submitted ID photo.';
        }

        if (!$nameMatch) {
            $reasons[] = 'The first/last name found on your ID does not match the name on your account.';
        }

        if (!$idNumberMatch) {
            $reasons[] = 'The ID number found on your ID does not match the ID number you entered.';
        }

        return [
            'credentials_matched' => (bool) ($faceMatch && $nameMatch && $idNumberMatch),
            'failure_reason' => count($reasons) > 0 ? implode(' ', $reasons) : null,
        ];
    }

    /**
     * Persist the verification result for a user/requirement and return it.
     *
     * @param array $payload  raw request payload (ocr_text, ocr_id_number, face_match, face_similarity)
     */
    public static function saveResult(
        User $user,
        string $requirementType,
        $requirementId,
        string $role,
        $selfiePath,
        array $payload
    ) {
        $faceMatch = static::toBool($payload['face_match'] ?? false);
        $nameMatch = static::namesMatch($user, $payload['ocr_text'] ?? null);
        $idNumberMatch = static::idNumberMatches(
            (string) ($payload['typed_id_number'] ?? $payload['id_number'] ?? ''),
            $payload['ocr_id_number'] ?? null
        );
        $decision = static::decide($faceMatch, $nameMatch, $idNumberMatch);

        $result = IdentityVerificationResult::updateOrCreate(
            ['user_id' => $user->id, 'requirement_type' => $requirementType],
            [
                'role' => $role,
                'requirement_id' => $requirementId,
                'selfie_photo' => $selfiePath,
                'face_detected' => static::toBool($payload['face_detected'] ?? false),
                'face_match' => $faceMatch,
                'face_similarity' => floatval($payload['face_similarity'] ?? 0),
                'name_match' => $nameMatch,
                'id_number_match' => $idNumberMatch,
                'credentials_matched' => $decision['credentials_matched'],
                'failure_reason' => $decision['failure_reason'],
                'extracted_text' => $payload['ocr_text'] ?? null,
                'extracted_id_number' => $payload['ocr_id_number'] ?? null,
            ]
        );

        return $result->refresh();
    }

    /**
     * Format a result row for API responses.
     */
    public static function formatResult(?IdentityVerificationResult $result)
    {
        if (!$result) {
            return null;
        }

        return [
            'id' => $result->id,
            'requirement_type' => $result->requirement_type,
            'requirement_id' => $result->requirement_id,
            'selfie_photo' => $result->selfie_photo,
            'selfie_photo_url' => $result->selfie_photo_url,
            'face_detected' => $result->face_detected,
            'face_match' => $result->face_match,
            'face_similarity' => round((float) $result->face_similarity, 4),
            'name_match' => $result->name_match,
            'id_number_match' => $result->id_number_match,
            'credentials_matched' => $result->credentials_matched,
            'failure_reason' => $result->failure_reason,
            'extracted_text' => $result->extracted_text,
            'extracted_id_number' => $result->extracted_id_number,
            'checked_at' => $result->updated_at ? $result->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Lenient boolean coercion for form/JSON payloads.
     */
    protected static function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on']);
        }

        return false;
    }
}