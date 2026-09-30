<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Supplier\SupplierRequirements;
use App\Models\Supplier\SupplierAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Events\Requirements\DocumentsRenewed;
use App\Events\Requirements\RequirementSubmitted;
use App\Models\IdentityVerificationResult;
use App\Services\IdentityVerificationService;
use App\Services\DocumentSubmission;
use App\Support\Documents\DocumentExpiry;

class SupplierRequirementController extends Controller
{
    /**
     * Get supplier's business verification status
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $requirements = SupplierRequirements::with('address')->where('user_id', $user->id)->first();
            
            if (!$requirements) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'is_verified' => false,
                        'verification_status' => 'none',
                        'has_submitted' => false,
                        'resubmission_count' => 0
                    ]
                ], 200);
            }
            
            $photoUrls = $requirements->getAllPhotoUrls();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $requirements->id,
                    'company_name' => $requirements->company_name,
                    'valid_id_type' => $requirements->valid_id_type,
                    'id_type_name' => $requirements->id_type_name,
                    'id_number' => $requirements->id_number,
                    'business_registration_number' => $requirements->business_registration_number,
                    'address' => $requirements->address, 
                    'status' => $requirements->status,
                    'is_verified' => $requirements->status === 'approved',
                    'verification_status' => $requirements->status,
                    'status_class' => $requirements->status_class,
                    'rejection_reason' => $requirements->rejection_reason,
                    'is_complete' => $requirements->is_complete,
                    'has_submitted' => true,
                    'photos' => $photoUrls,
                    'documents' => DocumentSubmission::payload($requirements->load('relatedDocuments')),
                    'resubmission_count' => $requirements->resubmission_count ?? 0,
                    'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirements->updated_at->format('Y-m-d H:i:s'),
                    'verification_result' => IdentityVerificationService::formatResult(
                        IdentityVerificationResult::where('user_id', $user->id)
                            ->where('requirement_type', 'supplier')
                            ->latest()
                            ->first()
                    )
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching supplier requirements:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch business verification data'
            ], 500);
        }
    }

    /**
     * Submit business verification
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'dti_certificate_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'mayor_permit_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'barangay_clearance_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'business_registration_number' => 'required|string|max:100',
                'business_registration_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                
                // Automatic identity verification (selfie + face recognition + OCR)
                'selfie_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
                'face_detected' => 'sometimes|in:0,1,true,false',
                'face_match' => 'sometimes|in:0,1,true,false',
                'face_similarity' => 'sometimes|numeric|between:0,1',
                'ocr_text' => 'sometimes|nullable|string',
                'ocr_id_number' => 'sometimes|nullable|string',
                
                // Address Validation - strict bounding box for Cavite
                'province' => 'required|string|in:Cavite',
                'city' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'block_address' => 'required|string|max:1000',
                'latitude' => 'nullable|numeric|min:14.0000|max:14.6000',
                'longitude' => 'nullable|numeric|min:120.5000|max:121.1000',
            ] + DocumentSubmission::rules(), [
                'latitude.min' => 'Location pinned must be inside Cavite.',
                'latitude.max' => 'Location pinned must be inside Cavite.',
                'longitude.min' => 'Location pinned must be inside Cavite.',
                'longitude.max' => 'Location pinned must be inside Cavite.',
            ] + DocumentSubmission::messages());
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $existing = SupplierRequirements::where('user_id', $user->id)->first();
                
            if ($existing) {
                if (in_array($existing->status, ['pending', 'approved'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You already have a ' . $existing->status . ' business verification submission'
                    ], 400);
                }

                // Maximum 3 Submissions Blocker
                if ($existing->resubmission_count >= 3) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Maximum resubmission attempts reached. Please contact admin via Chat.'
                    ], 403);
                }
            }
            
            DB::beginTransaction();
            
            try {
                $uploadData = [];
                $photoFields = [
                    'valid_id_photo',
                    'dti_certificate_photo',
                    'mayor_permit_photo',
                    'barangay_clearance_photo',
                    'business_registration_photo'
                ];
                
                foreach ($photoFields as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $fileName = $user->id . '_' . $field . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $folderPath = 'supplier_verification';
                        $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                        $uploadData[$field] = $folderPath . '/' . $fileName;
                    }
                }
                
                // Handle face selfie upload (for facial recognition matching)
                $selfieDbPath = null;
                if ($request->hasFile('selfie_photo')) {
                    $selfieFile = $request->file('selfie_photo');
                    $selfieName = $user->id . '_selfie_' . time() . '_' . uniqid() . '.' . $selfieFile->getClientOriginalExtension();
                    $selfiePath = Storage::disk('public')->putFileAs('supplier_verification_selfies', $selfieFile, $selfieName);
                    $selfieDbPath = $selfiePath;
                }
                
                // Directly assigning to bypass model $fillable restrictions
                $requirements = SupplierRequirements::firstOrNew(['user_id' => $user->id]);
                $requirements->company_name = $request->company_name;
                $requirements->valid_id_type = $request->valid_id_type;
                $requirements->id_number = $request->id_number;
                $requirements->valid_id_photo = $uploadData['valid_id_photo'];
                $requirements->dti_certificate_photo = $uploadData['dti_certificate_photo'];
                $requirements->mayor_permit_photo = $uploadData['mayor_permit_photo'];
                $requirements->barangay_clearance_photo = $uploadData['barangay_clearance_photo'];
                $requirements->business_registration_number = $request->business_registration_number;
                $requirements->business_registration_photo = $uploadData['business_registration_photo'];
                $requirements->status = 'pending';
                $requirements->rejection_reason = null;
                $requirements->resubmission_count = $existing ? ($existing->resubmission_count + 1) : 1;

                // Expiration dates first: these also clear any earlier admin
                // verification, which described the files being replaced.
                DocumentSubmission::saveExpirations($request, $requirements);
                $requirements->save();

                DocumentSubmission::saveRelatedDocuments($request, $requirements, 'supplier_verification');

                SupplierAddress::updateOrCreate(
                    ['supplier_requirements_id' => $requirements->id],
                    [
                        'province' => 'Cavite', 
                        'city' => $request->city,
                        'barangay' => $request->barangay,
                        'block_address' => $request->block_address,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                    ]
                );

                DB::commit();

                // Save the automatic identity verification result (face match + OCR checks).
                // NOTE: the requirement & user status stay 'pending' - the admin decides activation.
                $verificationResult = IdentityVerificationService::saveResult(
                    $user,
                    'supplier',
                    $requirements->id,
                    'supplier',
                    $selfieDbPath,
                    array_merge($request->all(), ['typed_id_number' => $request->id_number])
                );

                // Broadcast Event to Admin UI Here
                event(new RequirementSubmitted($user));

                $photoUrls = $requirements->getAllPhotoUrls();
                $requirements->load('address');
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Business verification submitted successfully! Your documents are now pending review.',
                    'data' => [
                        'id' => $requirements->id,
                        'company_name' => $requirements->company_name,
                        'address' => $requirements->address,
                        'status' => 'pending',
                        'has_submitted' => true,
                        'photos' => $photoUrls,
                        'documents' => DocumentSubmission::payload($requirements->refresh()),
                        'resubmission_count' => $requirements->resubmission_count,
                        'verification_result' => IdentityVerificationService::formatResult($verificationResult)
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error submitting supplier verification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit business verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update Supplier Info (Company name, ID numbers only)
     */
    public function updateSupplierInfo(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $existing = SupplierRequirements::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update information while verification is ' . $existing->status
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'business_registration_number' => 'required|string|max:100',
                'valid_id_type' => 'required|string',
                'id_number' => 'required|string|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $requirements = SupplierRequirements::where('user_id', $user->id)->first();
            
            if (!$requirements) {
                $requirements = SupplierRequirements::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'business_registration_number' => $request->business_registration_number,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                    'status' => 'draft'
                ]);
            } else {
                $requirements->update([
                    'company_name' => $request->company_name,
                    'business_registration_number' => $request->business_registration_number,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier information updated successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update info'
            ], 500);
        }
    }

    /**
     * Submit a replacement for one of the supplier's dated documents.
     *   POST /requirements/documents/{documentKey}
     *
     * Separate from store() because the two answer different situations. store() is
     * a first application and refuses anyone already approved -- correct, since
     * re-sending the whole packet would reset an established account. But that same
     * refusal left an approved user with no way to replace an expiring permit, which
     * made the admin's revoke-termination action unreachable: it requires documents
     * newer than the termination, and there was no way to submit any.
     *
     * One document per request, named in the URL. The old endpoint put both
     * documents in a single multipart body, which meant one request could open two
     * reviews, one could be half-filled without the server noticing, and the client
     * had to name the document twice -- once in the field name, once in the payload.
     * Splitting it means the reviews match the documents one to one, and a supplier
     * renewing a Mayor's Permit is not made to re-upload a DTI Certificate that is
     * still valid.
     *
     * POST, not PUT, and the reason is a PHP one worth writing down so nobody
     * "corrects" it later: PHP only runs its multipart parser for POST. A PUT or
     * PATCH carrying multipart/form-data leaves $_POST and $_FILES empty and the
     * body sitting unread in php://input, so $request->file() returns null and every
     * field reads as missing -- a 422 complaining that no file was attached by a
     * client that demonstrably attached one. Measured, not assumed.
     *
     * POST is also the more truthful verb. Each submission opens a review and a
     * later submission supersedes the earlier one, so this creates a resource
     * rather than idempotently replacing one. Hence 201 and a Location header.
     */
    public function replaceDocument(Request $request, string $documentKey)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'supplier') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // 404 rather than 422 for an unknown document: "dti_certficate" is a
        // misspelling the caller needs to see, and folding it into a validation
        // failure would report it as a problem with the file they just uploaded.
        if (! DocumentExpiry::isTrackable($documentKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown document',
                'errors' => ['documentKey' => ['No such business document for this account.']]
            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            DocumentSubmission::documentRules(),
            DocumentSubmission::documentMessages($documentKey)
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requirements = SupplierRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $review = DocumentSubmission::applyDocument($request, $requirements, $documentKey, 'supplier_verification');

        $label = DocumentExpiry::label($documentKey);

        $documents = DocumentSubmission::payload($requirements->refresh()->load('relatedDocuments'));

        // Pushed so the admin's renewals queue gains a card without anyone
        // reloading it. Sent even though the user's own panel is the one that
        // navigated here -- the point is that the queue is a shared screen.
        event(new DocumentsRenewed($user->id, 'supplier', [$documentKey], $documents));

        return response()->json([
            'status' => 'success',
            'message' => $label . ' renewed and awaiting review by an administrator.',
            'data' => [
                'document' => $documents[$documentKey],
                'review'   => $review->toDisplayArray(),
                'documents' => $documents,
            ]
        ], 201)
            ->header('Location', url("/api/supplier/requirements/reviews/{$review->id}"));
    }

    /**
     * Read back one of the supplier's own document reviews.
     *
     * Exists so the Location header on a submission resolves to something. Scoped
     * through the authenticated user's own reviews, so an id belonging to another
     * account is a 404 rather than a disclosure of what that account submitted.
     */
    public function showReview(Request $request, $reviewId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'supplier') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = SupplierRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $review = $requirements->documentReviews()->find($reviewId);

        if (!$review) {
            return response()->json([
                'status' => 'error',
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Review retrieved',
            'data' => ['review' => $review->toDisplayArray()]
        ], 200);
    }

    /**
     * Read back one of the supplier's own extra documents.
     *
     * The only reason this exists is to give `storeRelatedDocument` a Location
     * header that points at something fetchable, which is what makes that header
     * worth sending. Scoped through the authenticated user's requirements row, so
     * another account's document id is a 404.
     */
    public function showRelatedDocument(Request $request, $documentId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'supplier') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = SupplierRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $document = $requirements->relatedDocuments()->find($documentId);

        if (!$document) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Document retrieved',
            'data' => ['document' => $document->toDisplayArray()]
        ], 200);
    }

    /**
     * Attach one extra document.  POST /requirements/related-documents
     *
     * Additive, and stays that way: a rejected document is not removed along with
     * the rejection, so the user can replace it later. A fresh upload is always
     * 'pending' because nothing an user attaches has been looked at yet.
     */
    public function storeRelatedDocument(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'supplier') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make(
            $request->all(),
            DocumentSubmission::singleRelatedDocumentRules(),
            DocumentSubmission::singleRelatedDocumentMessages()
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requirements = SupplierRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $document = DocumentSubmission::addRelatedDocument($request, $requirements, 'supplier_verification');

        $documents = DocumentSubmission::payload($requirements->refresh()->load('relatedDocuments'));

        event(new DocumentsRenewed($user->id, 'supplier', [], $documents));

        return response()->json([
            'status' => 'success',
            'message' => 'Document added',
            'data' => [
                'document' => $document->toDisplayArray(),
                'documents' => $documents,
            ]
        ], 201)
            ->header('Location', url("/api/supplier/requirements/related-documents/{$document->id}"));
    }

    /**
     * Remove one of the supplier's own extra documents.
     *
     * Resolved through the authenticated user's own requirements row rather than
     * by primary key, so a guessed document id belonging to another account is a
     * 404 instead of a deletion. The stored file is removed with the row.
     */
    public function destroyRelatedDocument(Request $request, $documentId)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'supplier') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = SupplierRequirements::where('user_id', $user->id)->first();

        if (! $requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $document = $requirements->relatedDocuments()->find($documentId);

        if (! $document) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document not found'
            ], 404);
        }

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $documents = DocumentSubmission::payload($requirements->refresh()->load('relatedDocuments'));

        // Pushed for the same reason as an upload: the panel this was removed from
        // should redraw without a reload, and the admin's view of the account's
        // documents is now out of date too.
        event(new DocumentsRenewed($user->id, 'supplier', [], $documents));

        return response()->json([
            'status' => 'success',
            'message' => 'Document removed',
            'data' => ['documents' => $documents],
        ], 200);
    }
}