<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\Account\AccountStatusUpdated;
use App\Events\Notification\NotificationEvent;
use App\Events\Requirements\DocumentReviewDecided;
use App\Http\Controllers\Controller;
use App\Models\AccountTermination;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Supplier\SupplierRequirements;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\DocumentSubmission;
use App\Support\Documents\DocumentExpiry;
use App\Support\Documents\DocumentFile;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Document-expiration renewals: the admin queue of accounts whose DTI Certificate
 * or Mayor's Permit is inside the renewal window, and the three actions that can
 * follow from it.
 *
 * Two rules hold across every method here:
 *
 *  1. The window and the grace period are only ever read from DocumentExpiry, so
 *     the button the admin is shown and the permission the server enforces come
 *     from one definition.
 *  2. Nothing trusts the client for a decision. A hidden button is a courtesy;
 *     every action re-derives whether it is allowed before acting. This codebase
 *     has no role middleware on the API, so each method also checks the role
 *     itself rather than trusting the `admin` route prefix.
 */
class RenewalController extends Controller
{
    /**
     * Which requirements model backs which role. Only these two roles carry the
     * dated business documents; clients and service providers do not.
     */
    private const ROLE_MODELS = [
        'distributor' => DistributorRequirements::class,
        'supplier'    => SupplierRequirements::class,
    ];

    /**
     * The renewal queue.
     *
     * Filters to suppliers and distributors whose DTI Certificate or Mayor's
     * Permit expires within the window, most urgent first. Overdue documents are
     * included, not just imminent ones: those are precisely the rows that still
     * need an action, and dropping them would make the termination rule
     * unreachable from the screen that exists to apply it.
     */
    public function index(Request $request)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $windowDays = $this->resolveWindow($request);

        $role = $request->query('role', 'all');
        $role = in_array($role, array_keys(self::ROLE_MODELS), true) ? $role : 'all';

        // Opt-in, not the default. The table's contract is "documents expiring
        // within a month", and quietly widening it to every account with an open
        // review would break that. What renewal does, though, is move the dates
        // out of the window -- so the account that just submitted the document
        // the admin is waiting on would disappear exactly when it matters. Hence
        // a filter the admin can switch on, not a change to what the list means.
        $review = $request->query('review', 'all');
        $review = in_array($review, ['all', 'pending'], true) ? $review : 'all';

        $cutoff = CarbonImmutable::now()
            ->startOfDay()
            ->addDays($windowDays)
            ->toDateString();

        $candidates = [];

        foreach (self::ROLE_MODELS as $candidateRole => $class) {
            if ($role !== 'all' && $candidateRole !== $role) {
                continue;
            }

            $rows = $this->candidateRows($class, $cutoff);

            if ($review === 'pending') {
                $rows = $rows->merge($this->rowsAwaitingReview($class))->unique('id')->values();
            }

            foreach ($rows as $requirements) {
                $row = $this->buildRow($requirements, $candidateRole);

                // Re-checked in PHP rather than trusted from SQL: the window
                // constant and the column comparison are two implementations of
                // the same rule, and this is the one that decides the answer.
                if ($row['expiring'] === [] && ($review !== 'pending' || $row['pending_reviews'] === 0)) {
                    continue;
                }

                $candidates[] = $row;
            }
        }

        // Most urgent first: the most-negative days_remaining is the document
        // already furthest past due, which is the row with the most at stake.
        usort($candidates, function (array $a, array $b) {
            return [$a['soonest_days_remaining'], $a['user']['name'] ?? '']
                <=> [$b['soonest_days_remaining'], $b['user']['name'] ?? ''];
        });

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));
        $page    = max(1, (int) $request->query('page', 1));

        $total = count($candidates);
        $slice = array_slice($candidates, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'success' => true,
            'data'    => $slice,
            'meta'    => [
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $perPage,
                'last_page'  => max(1, (int) ceil($total / $perPage)),
                'window_days' => $windowDays,
                // Computed across the whole role scope, not sliced by page or by
                // the review filter, so the UI can badge the filter button with
                // the true number of submissions waiting regardless of what the
                // current view happens to show.
                'pending_review_total' => $this->pendingReviewTotal($role),
                'review'   => $review,
            ],
            'window_days' => $windowDays,
        ]);
    }

    /**
     * One account in full: the user, both dated documents with their files, every
     * extra document, and what the admin is currently allowed to do about it.
     */
    public function show(Request $request, $userId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];
        $role = $resolved['role'];

        $requirements->loadMissing('relatedDocuments');
        $requirements->loadMissing('documentReviews');

        $termination = AccountTermination::where('account_id', $user->id)
            ->where('status', 'terminated')
            ->latest('id')
            ->first();

        $expiring = $this->expiringDocuments($requirements);

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => $this->userPayload($user, $requirements, $role),
                'documents' => [
                    'dti_certificate' => $this->documentPayload(
                        $requirements,
                        'dti_certificate',
                        'dti_certificate_photo'
                    ),
                    'mayor_permit' => $this->documentPayload(
                        $requirements,
                        'mayor_permit',
                        'mayor_permit_photo'
                    ),
                ],
                'expiring' => $expiring,
                'soonest' => $this->soonest($expiring),
                'related_documents' => $requirements->relatedDocuments
                    ->map(fn ($doc) => $doc->toDisplayArray())
                    ->all(),
                // Latest decision per tracked document, plus the full recent
                // history. The admin needs both: "what is waiting on me" and
                // "what did I decide last time, and why was it rejected".
                'reviews' => DocumentSubmission::reviewStates($requirements),
                'review_history' => $requirements->documentReviews
                    ->take(20)
                    ->map(fn ($review) => $review->toDisplayArray())
                    ->all(),
                'pending_reviews' => $requirements->documentReviews
                    ->where('status', 'pending')
                    ->count(),
                'other_documents' => [
                    'valid_id_photo'           => $this->photoUrl($requirements->valid_id_photo),
                    'barangay_clearance_photo'  => $this->photoUrl($requirements->barangay_clearance_photo),
                    'business_registration_photo' => $this->photoUrl($requirements->business_registration_photo),
                ],
                'termination' => $termination ? [
                    'id'           => $termination->id,
                    'status'       => $termination->status,
                    'reason'       => $termination->reason,
                    'terminated_at' => $this->formatTimestamp($termination->terminated_at),
                ] : null,
                'permissions' => $this->permissions($user, $requirements, $expiring, $termination),
                'verification' => [
                    'verified_at' => $this->formatTimestamp($requirements->documents_verified_at),
                    'verified_by' => $requirements->documents_verified_by,
                ],
            ],
        ]);
    }

    /**
     * Send the renewal warning into the user's notification inbox.
     *
     * The message text is fixed by policy and generated here, never taken from the
     * request, so the promise it makes about the one-week grace period is the same
     * promise DocumentExpiry::canTerminate() enforces.
     */
    public function sendNotification(Request $request, $userId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        // document_key is optional. Both permits usually come due together, and an
        // admin warning about one while the other silently lapses is the exact
        // failure this feature exists to prevent -- so omitting the key warns
        // about every document currently in the window, one notification each.
        $request->validate([
            'document_key' => 'nullable|string|in:' . implode(',', array_keys(DocumentExpiry::documents())),
        ]);

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];

        $documentKey = $request->input('document_key');

        $keys = $documentKey !== null
            ? [$documentKey]
            : array_keys(DocumentExpiry::documents());

        $sent    = [];
        $skipped = [];

        foreach ($keys as $key) {
            $expiration = $this->expirationFor($requirements, $key);
            $days       = DocumentExpiry::daysRemaining($expiration);

            if ($days === null) {
                $skipped[] = sprintf('%s has no expiration date on record.', DocumentExpiry::label($key));
                continue;
            }

            if (! DocumentExpiry::needsRenewal($expiration)) {
                $skipped[] = sprintf(
                    '%s expires in %d days, outside the %d-day renewal window.',
                    DocumentExpiry::label($key),
                    $days,
                    DocumentExpiry::RENEWAL_WINDOW_DAYS
                );
                continue;
            }

            $notification = $this->pushNotification(
                $user,
                'Warning',
                DocumentExpiry::warningTitle($key),
                DocumentExpiry::warningMessage($key)
            );

            $sent[] = [
                'document_key' => $key,
                'label'        => DocumentExpiry::label($key),
                'id'           => $notification->id,
                'message'      => $notification->message,
            ];
        }

        if ($sent === []) {
            return response()->json([
                'success' => false,
                'message' => $skipped === []
                    ? 'No document is currently inside the renewal window.'
                    : implode(' ', $skipped),
                'skipped' => $skipped,
            ], 422);
        }

        return response()->json([
            'success'  => true,
            'message'  => count($sent) === 1
                ? 'Renewal notification sent.'
                : sprintf('Renewal notifications sent for %d documents.', count($sent)),
            'sent'     => $sent,
            'skipped'  => $skipped,
        ]);
    }

    /**
     * Terminate an account whose dated document is past the grace period.
     *
     * The 7-day check is redone here from the stored date. Accepting a date or a
     * "yes you may terminate" flag from the request would make the whole rule
     * decorative.
     */
    public function terminate(Request $request, $userId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $request->validate([
            'document_key' => 'nullable|string|in:' . implode(',', array_keys(DocumentExpiry::documents())),
            'reason'       => 'nullable|string|max:2000',
        ]);

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => sprintf('This account is already %s.', $user->status),
            ], 422);
        }

        $eligible = $this->expiringDocuments($requirements)
            ->filter(fn (array $doc) => $doc['can_terminate'])
            ->values();

        if ($request->filled('document_key')) {
            $chosen = $request->input('document_key');
            $eligible = $eligible->filter(fn (array $doc) => $doc['key'] === $chosen)->values();
        }

        if ($eligible->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => $this->terminationBlockedMessage($requirements),
                'documents' => $this->expiringDocuments($requirements)->all(),
            ], 422);
        }

        $document = $eligible->first();

        DB::transaction(function () use ($user, $document, $request, $requirements) {
            // Idempotent on the (account, status) pair the rest of the app looks
            // terminations up by, so a double-click cannot stack two rows.
            $termination = AccountTermination::updateOrCreate(
                ['account_id' => $user->id, 'status' => 'terminated'],
                [
                    'role'             => $user->role,
                    'terminated_by'    => Auth::id(),
                    'termination_type' => $document['key'],
                    'reason'           => $request->input('reason')
                        ?: sprintf(
                            '%s expired on %s and was not renewed within the %d-day grace period.',
                            $document['label'],
                            $document['expiration_at'],
                            DocumentExpiry::TERMINATION_GRACE_DAYS
                        ),
                    'terminated_at'    => now(),
                ]
            );

            $user->forceFill(['status' => 'inactive'])->save();

            $this->pushNotification(
                $user,
                'Warning',
                'Account Terminated',
                sprintf(
                    'Your account has been terminated because your %s expired on %s and was not renewed.',
                    $document['label'],
                    $document['expiration_at']
                )
            );

            broadcast(new AccountStatusUpdated($user->id, 'terminated', $termination));
        });

        return response()->json([
            'success' => true,
            'message' => sprintf('%s terminated because the %s expired.', $user->name, $document['label']),
        ]);
    }

    /**
     * Reverse a termination once the user has supplied valid replacement documents.
     *
     * Both halves of "successfully submitted" are checked: the documents must be
     * newer than the termination, and their dates must actually be in the future.
     * A user who resubmits the same expired permit is not a renewal.
     */
    public function revokeTermination(Request $request, $userId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $request->validate([
            'reversal_reason' => 'required|string|max:2000',
        ]);

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];

        $termination = AccountTermination::where('account_id', $user->id)
            ->where('status', 'terminated')
            ->latest('id')
            ->first();

        if (! $termination) {
            return response()->json([
                'success' => false,
                'message' => 'This account has no active termination to revoke.',
            ], 422);
        }

        if ($ineligible = $this->renewalIneligibility($requirements, $termination)) {
            return response()->json([
                'success' => false,
                'message' => $ineligible,
                'documents' => $this->expiringDocuments($requirements)->all(),
            ], 422);
        }

        DB::transaction(function () use ($user, $termination, $request) {
            $termination->update([
                'status'          => 'reversed',
                'reversed_by'     => Auth::id(),
                'reversal_reason' => $request->input('reversal_reason'),
                'reversed_at'     => now(),
            ]);

            $user->forceFill(['status' => 'active'])->save();

            $this->pushNotification(
                $user,
                'Success',
                'Account Restored',
                sprintf(
                    'Your account termination has been reversed and your account is active again. Reason: %s',
                    $request->input('reversal_reason')
                )
            );

            broadcast(new AccountStatusUpdated($user->id, 'reversed', $termination));
        });

        return response()->json([
            'success' => true,
            'message' => sprintf('%s restored to active.', $user->name),
        ]);
    }

    /**
     * Record that an admin compared the uploaded files against the typed dates.
     *
     * The pre-renewal path: rows that predate document_reviews, or an account
     * whose documents were verified at first submission. It is refused while a
     * renewal is pending, because it would otherwise let an admin clear the
     * review queue with one click -- skipping the review the user is waiting on
     * and the only chance to reject a forged document.
     */
    public function verifyDocuments(Request $request, $userId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $request->validate([
            'document_key' => 'nullable|string|in:' . implode(',', array_keys(DocumentExpiry::documents())),
        ]);

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];

        // A verification with nothing to look at would be a rubber stamp.
        if ($request->filled('document_key') && ! $requirements->{$this->photoFieldFor($request->input('document_key'))}) {
            return response()->json([
                'success' => false,
                'message' => sprintf('No %s file has been uploaded to verify against.', DocumentExpiry::label($request->input('document_key'))),
            ], 422);
        }

        $pending = $requirements->documentReviews()
            ->where('status', 'pending')
            ->pluck('document_key')
            ->map(fn (string $key) => DocumentExpiry::label($key))
            ->all();

        if ($pending !== []) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'This account has a renewal awaiting your decision (%s). Approve or reject it instead of confirming the documents.',
                    implode(', ', $pending)
                ),
                'pending_documents' => $pending,
            ], 422);
        }

        $requirements->forceFill([
            'documents_verified_at' => now(),
            'documents_verified_by' => Auth::id(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => sprintf('Documents verified for %s.', $user->name),
            'data' => [
                'verified_at' => $this->formatTimestamp($requirements->documents_verified_at),
                'verified_by' => $requirements->documents_verified_by,
            ],
        ]);
    }

    /**
     * Approve or reject a renewed DTI Certificate / Mayor's Permit.
     *
     * The decision is per document, not per submission: the two permits can come
     * due together, and forcing an all-or-nothing call would mean rejecting a
     * perfectly good DTI Certificate because the Mayor's Permit was forged.
     *
     * Approval is also the verification stamp. The two used to be separate acts
     * -- "approve this renewal" and "confirm the dates match" -- which left two
     * buttons meaning overlapping things and no way to reject at all. Verifying
     * is precisely what approving a document is, so they are one act now.
     */
    public function reviewDocument(Request $request, $userId, $documentKey)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $request->validate([
            'decision'         => 'required|string|in:approved,rejected',
            'rejection_reason' => 'required_if:decision,rejected|nullable|string|max:2000',
        ]);

        if (! array_key_exists($documentKey, DocumentExpiry::documents())) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown document.',
            ], 404);
        }

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        /** @var User $user */
        $user = $resolved['user'];
        /** @var Model $requirements */
        $requirements = $resolved['requirements'];

        $requirements->loadMissing('documentReviews');

        // Scoped through the owner's own reviews, never fetched globally, so a
        // document key cannot be used to reach another account's submission.
        $review = $requirements->documentReviews
            ->where('document_key', $documentKey)
            ->where('status', 'pending')
            ->first();

        if (! $review) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'There is no %s renewal awaiting review for this account.',
                    DocumentExpiry::label($documentKey)
                ),
            ], 422);
        }

        $approved = $request->input('decision') === 'approved';
        $label    = DocumentExpiry::label($documentKey);

        $review->update([
            'status'           => $request->input('decision'),
            'rejection_reason' => $approved ? null : $request->input('rejection_reason'),
            'reviewed_at'      => now(),
            'reviewed_by'      => Auth::id(),
        ]);

        if ($approved) {
            // Approval is the one moment the account's live documents change.
            //
            // A submission writes nothing to the requirements row, so the reviewed
            // file and date are promoted here and nowhere else. Until this point
            // the account kept the documents it already had, and the window kept
            // flagging the ones that genuinely need attention.
            $requirements->forceFill([
                $documentKey . '_photo'      => $review->file_path,
                $documentKey . '_expiration' => $review->expiration_date?->format('Y-m-d'),
            ])->save();

            // Only stamp the account once nothing is left waiting: a partial
            // approval must not read as "documents verified".
            $stillPending = $requirements->documentReviews()
                ->where('status', 'pending')
                ->exists();

            if (! $stillPending) {
                $requirements->forceFill([
                    'documents_verified_at' => now(),
                    'documents_verified_by' => Auth::id(),
                ])->save();
            }
        }

        // A rejection deliberately does nothing to the requirements row. The
        // rejected upload never reached it, so there is nothing to undo: the
        // account still holds the last file and date an admin accepted, and the
        // review row keeps pointing at the rejected file for the record.

        $this->pushNotification(
            $user,
            $approved ? 'Success' : 'Warning',
            $approved ? "$label Approved" : "$label Rejected",
            $approved
                ? sprintf('Your renewed %s has been verified and approved.', $label)
                : sprintf(
                    'Your renewed %s was rejected. Reason: %s Please upload a valid replacement.',
                    $label,
                    $request->input('rejection_reason')
                )
        );

        // The owner is looking at the document this decision just rewound, and
        // the admin queue is looking at the card this decision just removed.
        // Both redraw from the pushed payload instead of being reloaded by hand.
        $freshRequirements = $requirements->fresh();

        event(new DocumentReviewDecided(
            (int) $user->id,
            $documentKey,
            $label,
            (string) $request->input('decision'),
            $approved ? null : (string) $request->input('rejection_reason'),
            DocumentSubmission::payload($freshRequirements)
        ));

        return response()->json([
            'success' => true,
            'message' => sprintf('%s marked as %s.', $label, $request->input('decision')),
            'data'    => [
                'review' => $review->fresh()->toDisplayArray(),
                'verification' => [
                    'verified_at' => $this->formatTimestamp($requirements->fresh()->documents_verified_at),
                    'verified_by' => $requirements->documents_verified_by,
                ],
                // The panel renders from this, so the admin sees the consequence
                // of their click without reloading the whole detail view.
                'reviews' => DocumentSubmission::reviewStates($requirements->fresh()),
            ],
        ]);
    }

    /**
     * Approve or reject one of the user's extra documents.
     */
    public function reviewRelatedDocument(Request $request, $userId, $documentId)
    {
        if (($guard = $this->guardAdmin()) !== null) {
            return $guard;
        }

        $request->validate([
            'decision'         => 'required|string|in:approved,rejected',
            'rejection_reason' => 'required_if:decision,rejected|nullable|string|max:2000',
        ]);

        $resolved = $this->resolveSubject((int) $userId);

        if ($resolved['error'] !== null) {
            return response()->json($resolved['error'], $resolved['error_status']);
        }

        $requirements = $resolved['requirements'];
        $requirements->loadMissing('relatedDocuments');

        // Scoped through the owner's relation, not fetched globally: an admin
        // must not be able to pass a document id belonging to another account.
        $document = $requirements->relatedDocuments->firstWhere('id', (int) $documentId);

        if (! $document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found for this account.',
            ], 404);
        }

        $document->update([
            'status'           => $request->input('decision'),
            'rejection_reason' => $request->input('decision') === 'rejected'
                ? $request->input('rejection_reason')
                : null,
            'reviewed_at'      => now(),
            'reviewed_by'      => Auth::id(),
        ]);

        // The owner's extra-document list is rendered from the same payload as
        // their dated documents, so the decision has to reach them the same way.
        // No document key: these names are whatever the user typed.
        event(new DocumentReviewDecided(
            (int) $requirements->user_id,
            null,
            (string) $document->document_name,
            (string) $request->input('decision'),
            $request->input('decision') === 'rejected'
                ? (string) $request->input('rejection_reason')
                : null,
            DocumentSubmission::payload($requirements->fresh())
        ));

        return response()->json([
            'success' => true,
            'message' => sprintf('%s marked as %s.', $document->document_name, $request->input('decision')),
            'data'    => $document->toDisplayArray(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Guards and resolution                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Reject non-admins. Returns a ready-made response, or null when allowed.
     *
     * There is no role middleware on this API, so this check is the only thing
     * between an authenticated supplier and every action in this class.
     */
    private function guardAdmin(): ?\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Admin privileges required.',
            ], 403);
        }

        return null;
    }

    /**
     * Load the user and their requirements row together, or explain why not.
     *
     * @return array{user: ?User, requirements: ?Model, role: ?string, error: ?array, error_status: int}
     */
    private function resolveSubject(int $userId): array
    {
        $user = User::find($userId);

        if (! $user) {
            return $this->resolutionError('User not found.', 404);
        }

        $class = self::ROLE_MODELS[$user->role] ?? null;

        if ($class === null) {
            return $this->resolutionError(
                'Only suppliers and distributors track document expirations.',
                422
            );
        }

        $requirements = $class::with('relatedDocuments')->where('user_id', $user->id)->first();

        if (! $requirements) {
            return $this->resolutionError(
                'This account has not submitted any business documents yet.',
                404
            );
        }

        return [
            'user' => $user,
            'requirements' => $requirements,
            'role' => $user->role,
            'error' => null,
            'error_status' => 200,
        ];
    }

    private function resolutionError(string $message, int $status): array
    {
        return [
            'user' => null,
            'requirements' => null,
            'role' => null,
            'error' => ['success' => false, 'message' => $message],
            'error_status' => $status,
        ];
    }

    /**
     * The window the admin is looking at, clamped so a stray query string cannot
     * turn the table into an unbounded list of every account.
     */
    private function resolveWindow(Request $request): int
    {
        $requested = (int) $request->query('window_days', DocumentExpiry::RENEWAL_WINDOW_DAYS);

        return max(1, min($requested, 365));
    }

    /* ------------------------------------------------------------------ */
    /* Queries and shaping                                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Requirements rows of one role with at least one dated document at or before
     * the cutoff.
     *
     * The column list is discovered at runtime: this database is known to have
     * drifted from its migrations, and a missing column should degrade the query,
     * not 500 the whole table.
     *
     * @return \Illuminate\Support\Collection<int, Model>
     */
    private function candidateRows(string $class, string $cutoff)
    {
        $table = (new $class)->getTable();

        if (! Schema::hasTable($table)) {
            return collect();
        }

        $clauses = [];

        foreach (['dti_certificate_expiration', 'mayor_permit_expiration'] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $clauses[] = fn ($query) => $query
                ->whereNotNull($column)
                ->whereDate($column, '<=', $cutoff);
        }

        if ($clauses === []) {
            return collect();
        }

        return $class::query()
            ->with('user')
            ->where(function ($outer) use ($clauses) {
                foreach ($clauses as $clause) {
                    $outer->orWhere($clause);
                }
            })
            ->get();
    }

    /**
     * Requirements rows of one role that have at least one renewal awaiting a
     * decision, regardless of when the documents expire.
     *
     * Guarded on the table existing for the same reason candidateRows() guards on
     * the expiration columns: this database is known to have drifted from its
     * migrations, and a missing table should empty the filter rather than 500 it.
     *
     * @return \Illuminate\Support\Collection<int, Model>
     */
    private function rowsAwaitingReview(string $class)
    {
        $table = (new $class)->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasTable('document_reviews')) {
            return collect();
        }

        return $class::query()
            ->with('user')
            ->whereIn('id', function ($query) use ($class) {
                $query->select('reviewable_id')
                    ->from('document_reviews')
                    ->where('reviewable_type', $class)
                    ->where('status', 'pending');
            })
            ->get();
    }

    /**
     * How many accounts in scope are waiting on a renewal decision.
     */
    private function pendingReviewTotal(string $role): int
    {
        if (! Schema::hasTable('document_reviews')) {
            return 0;
        }

        $total = 0;

        foreach (self::ROLE_MODELS as $candidateRole => $class) {
            if ($role !== 'all' && $candidateRole !== $role) {
                continue;
            }

            $total += $this->rowsAwaitingReview($class)->count();
        }

        return $total;
    }

    /**
     * One renewals-table row.
     *
     * @return array<string, mixed>
     */
    private function buildRow(Model $requirements, string $role): array
    {
        $user = $requirements->user;

        if (! $user) {
            // An orphaned requirements row cannot be actioned by anyone; skipping
            // it beats emitting a row whose every action button is inert.
            return [
                'expiring'        => [],
                'pending_reviews' => 0,
                'user'            => ['id' => null, 'role' => $role, 'name' => null],
            ];
        }

        $expiring = $this->expiringDocuments($requirements);
        $soonest = $this->soonest($expiring);
        $termination = AccountTermination::where('account_id', $requirements->user_id)
            ->where('status', 'terminated')
            ->latest('id')
            ->first();

        return [
            'user' => $this->userPayload($user, $requirements, $role),
            'documents' => $this->documentSummaries($requirements),
            'expiring' => $expiring->all(),
            'soonest' => $soonest,
            'soonest_days_remaining' => $soonest['days_remaining'] ?? 0,
            'is_overdue' => ($soonest['days_remaining'] ?? 0) < 0,
            // How many renewed documents this account is waiting on a decision
            // about. Counted, not listed: the table only needs to say "2 waiting",
            // and the detail view is where the files are actually read.
            'pending_reviews' => $requirements->documentReviews()
                ->where('status', 'pending')
                ->count(),
            'termination' => $termination ? [
                'id'            => $termination->id,
                'status'        => $termination->status,
                'reason'        => $termination->reason,
                'terminated_at' => $this->formatTimestamp($termination->terminated_at),
            ] : null,
            'permissions' => $this->permissions($user, $requirements, $expiring, $termination),
        ];
    }

    /**
     * Both dated documents, each with its computed state.
     *
     * @return array<string, array<string, mixed>>
     */
    private function documentSummaries(Model $requirements): array
    {
        $out = [];

        foreach (array_keys(DocumentExpiry::documents()) as $key) {
            $out[$key] = DocumentExpiry::summary($key, $this->expirationFor($requirements, $key));
        }

        return $out;
    }

    /**
     * The dated documents that are inside the renewal window, most urgent first.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function expiringDocuments(Model $requirements)
    {
        $summaries = array_values(array_filter(
            $this->documentSummaries($requirements),
            fn (array $summary) => $summary['needs_renewal']
        ));

        usort($summaries, fn (array $a, array $b) => $a['days_remaining'] <=> $b['days_remaining']);

        return collect($summaries);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $expiring
     * @return array<string, mixed>|null
     */
    private function soonest($expiring): ?array
    {
        return $expiring->isEmpty() ? null : $expiring->first();
    }

    /**
     * A dated document with the file the admin needs to check it against.
     *
     * @return array<string, mixed>
     */
    private function documentPayload(Model $requirements, string $key, string $photoField): array
    {
        $path = $requirements->{$photoField};

        return array_merge(
            DocumentExpiry::summary($key, $this->expirationFor($requirements, $key)),
            [
                'file_path' => $path,
                'file_url'  => $this->photoUrl($path),
                'has_file'  => (bool) $path,
            ]
        );
    }

    /**
     * What the admin may do, derived from state rather than hard-coded in the UI.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $expiring
     * @return array<string, bool>
     */
    private function permissions(User $user, Model $requirements, $expiring, ?AccountTermination $termination): array
    {
        $canTerminate = $expiring->contains(fn (array $doc) => $doc['can_terminate']);

        return [
            'can_notify'    => $expiring->isNotEmpty() && $user->status !== 'inactive',
            'can_terminate' => $canTerminate && $user->status === 'active',
            'can_revoke'    => $termination !== null && $this->renewalIneligibility($requirements, $termination) === null,
        ];
    }

    /**
     * Why a termination may not be revoked yet, or null when it may.
     *
     * @return string|null
     */
    private function renewalIneligibility(Model $requirements, AccountTermination $termination): ?string
    {
        $terminatedAt = $termination->terminated_at;

        if (! $terminatedAt) {
            return 'This termination has no termination date, so its renewal state cannot be established.';
        }

        // A renewal nobody has looked at cannot restore an account. Without this,
        // submitting a plausible-looking file would be enough to undo a
        // termination, which is the one decision that most needs a human behind
        // it -- and it would undo it against a document that may be a forgery.
        $pending = $requirements->documentReviews()
            ->where('status', 'pending')
            ->pluck('document_key')
            ->map(fn (string $key) => DocumentExpiry::label($key))
            ->all();

        if ($pending !== []) {
            return sprintf(
                'A renewal is still awaiting your decision (%s). Approve or reject it before revoking this termination.',
                implode(', ', $pending)
            );
        }

        // The row has to have been touched after the termination, otherwise the
        // documents on file are the same ones that got the account killed.
        if (! $requirements->updated_at || $requirements->updated_at->lessThanOrEqualTo($terminatedAt)) {
            return 'This account has not submitted updated documents since it was terminated.';
        }

        $dated = $this->documentSummaries($requirements);
        $dated = array_filter($dated, fn (array $doc) => $doc['expiration_at'] !== null);

        if ($dated === []) {
            return 'This account has not supplied any expiration dates with its updated documents.';
        }

        foreach ($dated as $doc) {
            $days = $doc['days_remaining'];

            // Only an actually-expired date blocks the revocation.
            //
            // Deliberately not `needs_renewal`: that means "30 days or fewer
            // left", which includes a permit that was validly renewed last week
            // and runs out next month. That is a renewal, not a stale
            // submission, and refusing it would leave the admin with no way to
            // undo their own termination.
            if ($days !== null && $days > 0) {
                continue;
            }

            if ($days === 0) {
                return sprintf(
                    'The submitted %s expires today (%s). Upload a document with a future expiration date.',
                    $doc['label'],
                    $doc['expiration_at']
                );
            }

            // A replacement that is itself expired is not a replacement.
            return sprintf(
                'The submitted %s is still expired (expired %s, %d days ago).',
                $doc['label'],
                $doc['expiration_at'],
                abs((int) $days)
            );
        }

        return null;
    }

    /**
     * The message explaining a refused termination, naming how long is left.
     */
    private function terminationBlockedMessage(Model $requirements): string
    {
        $soonest = $this->soonest($this->expiringDocuments($requirements));

        if ($soonest === null) {
            return sprintf(
                'No document is inside the %d-day renewal window, so there is nothing to terminate over.',
                DocumentExpiry::RENEWAL_WINDOW_DAYS
            );
        }

        $days = $soonest['days_until_termination'];

        return sprintf(
            'The %s expires in %d days. Termination is only permitted %d days after it expires.',
            $soonest['label'],
            max(0, $soonest['days_remaining']),
            DocumentExpiry::TERMINATION_GRACE_DAYS
        ) . ($days !== null ? sprintf(' You may terminate in %d day(s).', $days) : '');
    }

    /* ------------------------------------------------------------------ */
    /* Small helpers                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Read one of the two expiration columns as a plain Y-m-d string.
     *
     * Delegates to DocumentSubmission rather than reading the column itself, so
     * there is one definition of "the date that counts" for every threshold in
     * this controller. It is the last date an administrator approved: a renewal
     * still waiting on the queue has not been written to the row, and neither has
     * one that was rejected.
     */
    private function expirationFor(Model $requirements, string $key): ?string
    {
        return DocumentSubmission::effectiveExpiration($requirements, $key);
    }

    /**
     * Which upload column backs a document key.
     */
    private function photoFieldFor(string $key): string
    {
        return $key . '_photo';
    }

    private function photoUrl(?string $path): ?string
    {
        // DocumentFile, not Storage::url(): the browser is served by Vite on a
        // different port than the API, and a relative /storage path resolves to
        // the SPA fallback there -- a 200 response that is not an image.
        return DocumentFile::url($path);
    }

    /**
     * Render a timestamp column for the API.
     *
     * Tolerant of a raw string: this controller compares and formats columns on
     * tables that predate their casts, and a drift-induced string should not turn
     * the detail screen into a 500.
     */
    private function formatTimestamp($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d H:i:s')
            : substr((string) $value, 0, 19);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(?User $user, Model $requirements, string $role): array
    {
        return [
            'id'           => $user?->id,
            'name'         => $user?->name,
            'email'        => $user?->email,
            'role'         => $role,
            'status'       => $user?->status,
            'company_name' => $requirements->company_name,
            'requirements_id' => $requirements->id,
            'requirements_status' => $requirements->status,
        ];
    }

    /**
     * Insert a notification and push it over the socket, matching the shape the
     * existing notification controllers use.
     */
    private function pushNotification(User $user, string $type, string $title, string $message): SystemNotification
    {
        $notification = SystemNotification::create([
            'receiver_id'   => $user->id,
            'type'          => $type,
            'title'         => $title,
            'message'       => $message,
            'is_read'       => false,
            'sender_id'     => Auth::id(),
            'sender_role'   => 'admin',
            'receiver_role' => $user->role,
        ]);

        broadcast(new NotificationEvent($notification));

        return $notification;
    }
}
