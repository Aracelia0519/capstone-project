<?php

namespace App\Services;

use App\Models\DocumentReview;
use App\Models\RelatedDocument;
use App\Support\Documents\DocumentExpiry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Document-expiration handling shared by the distributor and supplier settings
 * controllers.
 *
 * Those two controllers are deliberate near-copies of each other, so the rules for
 * "which dates must accompany which upload" and "where does this file go" live
 * here instead of being typed twice and drifting apart. Everything a caller needs
 * is static: a rules array for its validator, plus save/payload helpers.
 */
class DocumentSubmission
{
    /**
     * Validation rules for the dated documents.
     *
     * Required, because a certificate the admin cannot date-check is exactly the
     * document this feature exists to police. `after_or_equal:today` rather than
     * `after:today`: a permit valid through today is not expired, and rejecting
     * it would push users into inventing a later date.
     *
     * @return array<string, string>
     */
    public static function expirationRules(): array
    {
        return [
            'dti_certificate_expiration' => 'required|date|after_or_equal:today',
            'mayor_permit_expiration'   => 'required|date|after_or_equal:today',
        ];
    }

    /**
     * Validation rules for the optional extra documents.
     *
     * A row with a name but no file is a mistake, not a half-entry, so the file is
     * required on every row. Capped at 10 to keep the admin verification screen
     * and the upload payload bounded.
     *
     * @return array<string, string>
     */
    public static function relatedDocumentRules(): array
    {
        return [
            'related_documents'                    => 'sometimes|array|max:10',
            'related_documents.*.document_name'    => 'required|string|max:150',
            'related_documents.*.file'             => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'related_documents.*.expiration_date'  => 'nullable|date',
        ];
    }

    /**
     * All rules in one array, for controllers that build a single Validator.
     *
     * @return array<string, string>
     */
    public static function rules(): array
    {
        return array_merge(self::expirationRules(), self::relatedDocumentRules());
    }

    /**
     * Human-readable messages. The defaults are fine except for these, which read
     * as if the rule name were a user-facing noun.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'dti_certificate_expiration.required'          => 'Enter the expiration date of your DTI Certificate.',
            'dti_certificate_expiration.after_or_equal'    => 'The DTI Certificate expiration date cannot be in the past.',
            'mayor_permit_expiration.required'            => "Enter the expiration date of your Mayor's Permit.",
            'mayor_permit_expiration.after_or_equal'      => "The Mayor's Permit expiration date cannot be in the past.",
            'related_documents.*.document_name.required'  => 'Give each related document a name.',
            'related_documents.*.file.required'           => 'Attach a file to each related document.',
            'related_documents.*.file.mimes'              => 'Related documents must be JPG, PNG or PDF files.',
            'related_documents.*.file.max'                => 'Each related document must be 5 MB or smaller.',
        ];
    }

    /**
     * Persist the two expiration dates onto a requirements row.
     *
     * Clears the verification stamp: these methods run when the user submits new
     * files, and an admin's confirmation that the *previous* upload matches its
     * date is not a confirmation of this one. Leaving it in place would show a
     * freshly resubmitted document as already verified.
     *
     * @param  Model  $requirements  a DistributorRequirements or SupplierRequirements
     */
    public static function saveExpirations(Request $request, Model $requirements): void
    {
        $requirements->dti_certificate_expiration = $request->input('dti_certificate_expiration');
        $requirements->mayor_permit_expiration   = $request->input('mayor_permit_expiration');
        $requirements->documents_verified_at     = null;
        $requirements->documents_verified_by     = null;
    }

    /**
     * Validation rules for replacing one dated document.
     *
     * PUT /requirements/documents/{documentKey} names the document in the URL, so
     * the body does not repeat the key: the fields are the generic `file` and
     * `expiration_date`, not `dti_certificate_photo`. Without that, a client
     * sending the old field names to a URL naming a different document would get a
     * silently accepted no-op.
     *
     * Both halves required, which is what the old pairing validator existed to
     * establish. Stating it as two `required` rules is the direct way to say it;
     * the `nullable` plus after-hook that it replaced was an artefact of letting
     * one request carry two documents at once.
     *
     * @return array<string, string>
     */
    public static function documentRules(): array
    {
        return [
            'file'            => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'expiration_date' => 'required|date|after:today',
        ];
    }

    /**
     * Messages for the single-document rules, phrased against the document the URL
     * named rather than a field name the user never sees.
     *
     * @return array<string, string>
     */
    public static function documentMessages(string $documentKey): array
    {
        $label = DocumentExpiry::label($documentKey);

        return [
            'file.required'            => sprintf('Attach the renewed %s.', $label),
            'file.mimes'               => sprintf('The %s must be a JPG, PNG or PDF file.', $label),
            'file.max'                 => sprintf('The %s must be 5 MB or smaller.', $label),
            'expiration_date.required' => sprintf('Enter the expiration date printed on the %s.', $label),
            'expiration_date.date'     => sprintf('Enter the %s expiration date as YYYY-MM-DD.', $label),
            'expiration_date.after'    => sprintf('The new %s must expire in the future.', $label),
        ];
    }

    /**
     * Validation rules for one extra document posted on its own.
     *
     * @return array<string, string>
     */
    public static function singleRelatedDocumentRules(): array
    {
        return [
            'document_name'   => 'required|string|max:150',
            'file'            => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'expiration_date' => 'nullable|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function singleRelatedDocumentMessages(): array
    {
        return [
            'document_name.required' => 'Give this document a name.',
            'file.required'          => 'Attach a file to this document.',
            'file.mimes'             => 'Documents must be JPG, PNG or PDF files.',
            'file.max'               => 'Each document must be 5 MB or smaller.',
        ];
    }

    /**
     * Queue a replacement for one dated document. Nothing on the requirements row
     * is written here.
     *
     * The account's live documents change only when an administrator approves the
     * review (see RenewalController::reviewDocument). Until then the proposed file
     * and date exist solely on the review row, so the approved DTI Certificate an
     * account is currently relying on stays on file and stays valid, the renewal
     * window keeps flagging the document that genuinely needs attention, and a
     * rejected renewal has nothing to roll back because it never displaced
     * anything.
     *
     * The verification stamp is deliberately left alone for the same reason: the
     * documents an administrator confirmed are still the documents on file, so
     * there is nothing new to un-verify. Submitting a renewal is not a claim that
     * the current ones stopped being true.
     *
     * @param  Model  $requirements
     * @param  string  $key  e.g. 'dti_certificate'
     * @return DocumentReview  the review now awaiting a decision
     */
    public static function applyDocument(Request $request, Model $requirements, string $key, string $folder): DocumentReview
    {
        $photoField = $key . '_photo';
        $dateField  = $key . '_expiration';
        $file       = $request->file('file');

        // What the renewal would displace, snapshotted so the admin can see the
        // before-and-after and so an approval has a defensible record of what it
        // replaced. These are the columns as they stand, which -- because nothing
        // writes them before approval -- is the last value an admin accepted. That
        // is the only safe fallback available here, and needing a helper to find it
        // used to be a sign that unapproved values were reaching the row.
        $previousFile = $requirements->{$photoField} ?? null;
        $previousDate = $requirements->{$dateField} ?? null;

        $name = $requirements->user_id . '_' . $photoField . '_' . time() . '_' . uniqid()
            . '.' . strtolower($file->getClientOriginalExtension() ?: 'pdf');

        // The old file is left on disk: it is what an admin compared against the
        // old date, and a dispute about a renewal is exactly when that record
        // matters. Superseded files are cheap to reap; a missing one cannot be
        // recovered.
        Storage::disk('public')->putFileAs($folder, $file, $name);

        return self::openDocumentReview(
            $requirements,
            $key,
            $folder . '/' . $name,
            $request->input('expiration_date'),
            $previousFile,
            $previousDate
        );
    }

    /**
     * Store one extra document the user attached beyond the fixed set.
     *
     * A single document rather than the indexed array the form upload produced.
     * `related_documents[0][file]` was an artefact of one multipart form carrying
     * everything at once; as a resource of its own this is one row with plain
     * field names.
     *
     * @param  Model  $requirements
     */
    public static function addRelatedDocument(Request $request, Model $requirements, string $folder): RelatedDocument
    {
        $index     = $requirements->relatedDocuments()->count();
        $extension = strtolower($request->file('file')->getClientOriginalExtension() ?: 'pdf');
        $filename  = $requirements->user_id . '_related_' . $index . '_' . time() . '_' . uniqid() . '.' . $extension;

        // putFileAs already returns the path including the folder, so the stored
        // value is built from the filename. Prefixing the return value too would
        // write 'folder/folder/name.png' and every later read of file_path would
        // 404.
        Storage::disk('public')->putFileAs($folder, $request->file('file'), $filename);

        return $requirements->relatedDocuments()->create([
            'document_name'   => trim((string) $request->input('document_name')),
            'file_path'       => $folder . '/' . $filename,
            'expiration_date' => $request->input('expiration_date') ?: null,
            'status'          => 'pending',
        ]);
    }

    /**
     * Queue one renewed document for an administrator to approve or reject.
     *
     * Any earlier review of the same document still awaiting a decision is
     * marked superseded rather than deleted: it is the record that the user
     * submitted, was ignored, and submitted again, and leaving two rows both
     * reading "pending" would leave the admin unsure which one to judge.
     *
     * Scoped to one document key, so renewing the Mayor's Permit leaves a DTI
     * Certificate renewal that is also waiting exactly where it was. That matters
     * because the panel submits documents one request at a time: without it, the
     * second request in a batch would silently retire the first.
     *
     * @param  Model  $requirements
     * @param  string  $key  e.g. 'dti_certificate'
     * @param  string|null  $filePath  the proposed file, stored but not yet live
     * @param  mixed  $expiration  the proposed expiration date
     * @param  string|null  $previousFile  the file this would replace
     * @param  mixed  $previousExpiration  the date this would replace
     */
    private static function openDocumentReview(
        Model $requirements,
        string $key,
        ?string $filePath,
        $expiration,
        ?string $previousFile = null,
        $previousExpiration = null
    ): DocumentReview {
        $requirements->documentReviews()
            ->where('document_key', $key)
            ->where('status', 'pending')
            ->update([
                'status'     => 'superseded',
                'reviewed_at' => now(),
            ]);

        return $requirements->documentReviews()->create([
            'document_key'             => $key,
            'file_path'                => (string) $filePath,
            'expiration_date'          => $expiration,
            'previous_file_path'       => $previousFile,
            'previous_expiration_date' => $previousExpiration,
            'status'                   => 'pending',
        ]);
    }

    /**
     * The expiration date that actually counts for this document.
     *
     * The column on the requirements row, and nothing else. Because a renewal
     * writes nothing until an administrator approves it, the column is by
     * construction the last date a human actually accepted -- a submission still
     * waiting on the queue is not counted, and a rejected one never counted at
     * all. That is what makes the panel, the notification, the renewals list,
     * termination and the revoke precondition agree with each other: they all
     * read one value that has only ever been set by a decision.
     *
     * Every threshold -- the renewals list, the notification, termination, and the
     * revoke precondition -- reads this rather than the column directly, so the
     * column name and its null handling are decided in exactly one place.
     *
     * @param  Model  $requirements
     * @param  string  $key  e.g. 'dti_certificate'
     */
    public static function effectiveExpiration(Model $requirements, string $key): ?string
    {
        return self::dateString($requirements->{$key . '_expiration'} ?? null);
    }

    /**
     * The latest review of each tracked document, keyed for the front end.
     *
     * Latest-per-document rather than the whole history: the panel has to show
     * "is this approved, rejected, or waiting", and the full audit trail belongs
     * to the admin's screen, not the supplier's.
     *
     * @param  Model  $requirements
     * @return array<string, array<string, mixed>|null>
     */
    public static function reviewStates(Model $requirements): array
    {
        $requirements->loadMissing('documentReviews');

        $states = [];

        foreach (array_keys(DocumentExpiry::documents()) as $key) {
            // documentReviews() is ordered newest-first, so the first match wins.
            $states[$key] = $requirements->documentReviews
                ->firstWhere('document_key', $key)
                ?->toDisplayArray();
        }

        return $states;
    }

    /**
     * Store the extra documents attached to this submission.
     *
     * Additive by design: resubmitting a rejected application replaces the fixed
     * photos but appends to whatever the user already uploaded, so a rejected
     * extra document is not silently deleted along with the rejection.
     *
     * @param  Model  $requirements
     * @return int  how many were stored
     */
    public static function saveRelatedDocuments(Request $request, Model $requirements, string $folder): int
    {
        $rows = $request->input('related_documents');

        if (! is_array($rows)) {
            return 0;
        }

        $stored = 0;

        foreach (array_keys($rows) as $index) {
            $file = $request->file("related_documents.{$index}.file");

            if (! $file) {
                continue;
            }

            $name = trim((string) $request->input("related_documents.{$index}.document_name", ''));

            if ($name === '') {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $filename  = $requirements->user_id . '_related_' . $index . '_' . time() . '_' . uniqid() . '.' . $extension;

            // putFileAs already returns the path including the folder, so the
            // stored value is built from the filename. Prefixing the return value
            // too would write 'folder/folder/name.png' and every later read of
            // file_path would 404.
            Storage::disk('public')->putFileAs($folder, $file, $filename);

            $requirements->relatedDocuments()->create([
                'document_name'    => $name,
                'file_path'        => $folder . '/' . $filename,
                'expiration_date'  => $request->input("related_documents.{$index}.expiration_date") ?: null,
                'status'           => 'pending',
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * The document block for a settings response: the two fixed documents with
     * their computed state, plus whatever extras the user attached.
     *
     * @param  Model  $requirements
     * @return array<string, mixed>
     */
    public static function payload(Model $requirements): array
    {
        return [
            // effectiveExpiration(), not the raw column: a rejected renewal must
            // show the user the date that still counts, not the one on the file
            // their rejection was about. Otherwise their own panel reports a
            // permit as valid for another year while the admin's list reports it
            // expired, and neither number is a mistake -- they are two answers.
            'dti_certificate' => DocumentExpiry::summary(
                'dti_certificate',
                self::effectiveExpiration($requirements, 'dti_certificate')
            ),
            'mayor_permit' => DocumentExpiry::summary(
                'mayor_permit',
                self::effectiveExpiration($requirements, 'mayor_permit')
            ),
            'related_documents' => $requirements->relationLoaded('relatedDocuments')
                ? $requirements->relatedDocuments->map(fn (RelatedDocument $d) => $d->toDisplayArray())->all()
                : $requirements->relatedDocuments()->get()->map(fn (RelatedDocument $d) => $d->toDisplayArray())->all(),
            // So the panel can tell the user whether their renewal was approved,
            // rejected with a reason, or is still waiting.
            'reviews' => self::reviewStates($requirements),
            'pending_reviews' => $requirements->relationLoaded('documentReviews')
                ? $requirements->documentReviews->where('status', 'pending')->count()
                : $requirements->documentReviews()->where('status', 'pending')->count(),
            'documents_verified_at' => $requirements->documents_verified_at?->format('Y-m-d H:i:s'),
            'documents_verified_by' => $requirements->documents_verified_by,
        ];
    }

    /**
     * Normalise a date cast to a plain Y-m-d string for the expiry arithmetic.
     */
    private static function dateString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }
}
