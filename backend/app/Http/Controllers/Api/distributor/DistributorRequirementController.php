<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\DistributorAddress; // Import the new model
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Import DB for transactions
use App\Events\Requirements\DocumentsRenewed; // <-- Added Import
use App\Events\Requirements\RequirementSubmitted; // <-- Added Import
use App\Models\IdentityVerificationResult;
use App\Services\IdentityVerificationService;
use App\Services\DocumentSubmission;
use App\Support\Documents\DocumentExpiry;

class DistributorRequirementController extends Controller
{
    /**
     * Get distributor's business verification status
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Only allow distributor users
            if ($user->role !== 'distributor') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Eager load the address
            $requirements = DistributorRequirements::with('address')->where('user_id', $user->id)->first();
            
            if (!$requirements) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'is_verified' => false,
                        'verification_status' => 'none',
                        'has_submitted' => false
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
                    
                    // Include Address Data
                    'address' => $requirements->address, 
                    
                    'status' => $requirements->status,
                    'is_verified' => $requirements->status === 'approved',
                    'verification_status' => $requirements->status,
                    'status_class' => $requirements->status_class,
                    'rejection_reason' => $requirements->rejection_reason,
                    'resubmission_count' => $requirements->resubmission_count ?? 0, // NEW
                    'is_complete' => $requirements->is_complete,
                    'has_submitted' => true,
                    'photos' => $photoUrls,
                    'documents' => DocumentSubmission::payload($requirements->load('relatedDocuments')),
                    'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirements->updated_at->format('Y-m-d H:i:s'),
                    'verification_result' => IdentityVerificationService::formatResult(
                        IdentityVerificationResult::where('user_id', $user->id)
                            ->where('requirement_type', 'distributor')
                            ->latest()
                            ->first()
                    )
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching distributor requirements:', [
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
            
            // Only allow distributor users
            if ($user->role !== 'distributor') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Validate request
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
                
                // New Address Validation mapped properly to Cavite constraints
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
            
            // Use Transaction to ensure both Requirements and Address are saved
            DB::beginTransaction();
            
            try {
                // Check if user already has a pending or approved submission
                $existing = DistributorRequirements::where('user_id', $user->id)->first();
                    
                if ($existing) {
                    if (in_array($existing->status, ['pending', 'approved'])) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'You already have a ' . $existing->status . ' business verification submission'
                        ], 400);
                    }

                    // NEW: Maximum Resubmission limitation
                    if ($existing->resubmission_count >= 3) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Maximum resubmission attempts reached. Please contact admin via Chat.'
                        ], 403);
                    }
                }
                
                // Handle file uploads
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
                        $folderPath = 'distributor_verification';
                        
                        // Store the file
                        $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                        $uploadData[$field] = $folderPath . '/' . $fileName;
                    }
                }
                
                // Handle face selfie upload (for facial recognition matching)
                $selfieDbPath = null;
                if ($request->hasFile('selfie_photo')) {
                    $selfieFile = $request->file('selfie_photo');
                    $selfieName = $user->id . '_selfie_' . time() . '_' . uniqid() . '.' . $selfieFile->getClientOriginalExtension();
                    $selfiePath = Storage::disk('public')->putFileAs('distributor_verification_selfies', $selfieFile, $selfieName);
                    $selfieDbPath = $selfiePath;
                }
                
                // Fetch or Initialize Distributor Requirements (Resubmission Friendly)
                $requirements = DistributorRequirements::firstOrNew(['user_id' => $user->id]);
                $requirements->company_name = $request->company_name;
                $requirements->valid_id_type = $request->valid_id_type;
                $requirements->id_number = $request->id_number;
                
                if (isset($uploadData['valid_id_photo'])) $requirements->valid_id_photo = $uploadData['valid_id_photo'];
                if (isset($uploadData['dti_certificate_photo'])) $requirements->dti_certificate_photo = $uploadData['dti_certificate_photo'];
                if (isset($uploadData['mayor_permit_photo'])) $requirements->mayor_permit_photo = $uploadData['mayor_permit_photo'];
                if (isset($uploadData['barangay_clearance_photo'])) $requirements->barangay_clearance_photo = $uploadData['barangay_clearance_photo'];
                if (isset($uploadData['business_registration_photo'])) $requirements->business_registration_photo = $uploadData['business_registration_photo'];
                
                $requirements->business_registration_number = $request->business_registration_number;
                $requirements->status = 'pending';
                $requirements->rejection_reason = null;
                $requirements->resubmission_count = $existing ? ($existing->resubmission_count + 1) : 1;

                // Expiration dates first: these also clear any earlier admin
                // verification, which described the files being replaced.
                DocumentSubmission::saveExpirations($request, $requirements);
                $requirements->save();

                DocumentSubmission::saveRelatedDocuments($request, $requirements, 'distributor_verification');

                // Create or Update Address
                DistributorAddress::updateOrCreate(
                    ['distributor_requirements_id' => $requirements->id],
                    [
                        'province' => 'Cavite', // Enforced as per request
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
                    'distributor',
                    $requirements->id,
                    'distributor',
                    $selfieDbPath,
                    array_merge($request->all(), ['typed_id_number' => $request->id_number])
                );

                $photoUrls = $requirements->getAllPhotoUrls();
                $requirements->load('address'); // Load the new address

                // 🔔 Fire the real-time event to notify admins
                event(new RequirementSubmitted($user));
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Business verification submitted successfully! Your documents are now pending review.',
                    'data' => [
                        'id' => $requirements->id,
                        'company_name' => $requirements->company_name,
                        'valid_id_type' => $requirements->valid_id_type,
                        'id_type_name' => $requirements->id_type_name,
                        'id_number' => $requirements->id_number,
                        'business_registration_number' => $requirements->business_registration_number,
                        'address' => $requirements->address, // Return address
                        'status' => $requirements->status,
                        'is_verified' => false,
                        'verification_status' => 'pending',
                        'status_class' => $requirements->status_class,
                        'resubmission_count' => $requirements->resubmission_count, // NEW
                        'is_complete' => $requirements->is_complete,
                        'has_submitted' => true,
                        'photos' => $photoUrls,
                        'documents' => DocumentSubmission::payload($requirements->refresh()),
                        'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s'),
                        'verification_result' => IdentityVerificationService::formatResult($verificationResult)
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error submitting distributor verification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit business verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update business verification status (for admin)
     */
    public function update(Request $request, $id)
    {
        try {
            // Only admin can update status
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,approved,rejected',
                'rejection_reason' => 'required_if:status,rejected|string|max:255'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $requirements = DistributorRequirements::findOrFail($id);
            
            $requirements->status = $request->status;
            
            if ($request->status == 'rejected') {
                $requirements->rejection_reason = $request->rejection_reason;
            } else {
                $requirements->rejection_reason = null;
            }
            
            $requirements->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Business verification status updated successfully',
                'data' => [
                    'id' => $requirements->id,
                    'user_id' => $requirements->user_id,
                    'status' => $requirements->status,
                    'is_verified' => $requirements->status === 'approved',
                    'rejection_reason' => $requirements->rejection_reason,
                    'updated_at' => $requirements->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error updating distributor verification:', [
                'admin_id' => Auth::id(),
                'verification_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update business verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending verifications (for admin)
     */
    public function pending(Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            
            $query = DistributorRequirements::pending()
                ->with(['user:id,first_name,last_name,email,phone,created_at', 'address']); // Load address
            
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%")
                      ->orWhere('business_registration_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }
            
            $requirements = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            $formattedRequirements = $requirements->map(function ($requirement) {
                $photoUrls = $requirement->getAllPhotoUrls();
                
                return [
                    'id' => $requirement->id,
                    'user' => [
                        'id' => $requirement->user->id,
                        'name' => $requirement->user->full_name,
                        'email' => $requirement->user->email,
                        'phone' => $requirement->user->phone,
                        'joined_at' => $requirement->user->created_at->format('Y-m-d H:i:s')
                    ],
                    'company_name' => $requirement->company_name,
                    'valid_id_type' => $requirement->valid_id_type,
                    'id_type_name' => $requirement->id_type_name,
                    'id_number' => $requirement->id_number,
                    'business_registration_number' => $requirement->business_registration_number,
                    
                    'address' => $requirement->address, // Return Address
                    
                    'status' => $requirement->status,
                    'is_verified' => $requirement->status === 'approved',
                    'status_class' => $requirement->status_class,
                    'is_complete' => $requirement->is_complete,
                    'photos' => $photoUrls,
                    'submitted_at' => $requirement->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirement->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'pending_verifications' => $formattedRequirements,
                    'pagination' => [
                        'total' => $requirements->total(),
                        'per_page' => $requirements->perPage(),
                        'current_page' => $requirements->currentPage(),
                        'last_page' => $requirements->lastPage(),
                        'from' => $requirements->firstItem(),
                        'to' => $requirements->lastItem()
                    ],
                    'count' => $requirements->total()
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching pending distributor verifications:', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch pending verifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification statistics (for admin)
     */
    public function statistics(Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            $total = DistributorRequirements::count();
            $pending = DistributorRequirements::pending()->count();
            $approved = DistributorRequirements::approved()->count();
            $rejected = DistributorRequirements::rejected()->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'approved' => $approved,
                    'rejected' => $rejected,
                    'completion_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching distributor verification statistics:', [
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch verification statistics'
            ], 500);
        }
    }

    /**
     * Update distributor information
     */
    public function updateDistributorInfo(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Only allow distributor users
            if ($user->role !== 'distributor') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Check if user has submitted verification
            $existing = DistributorRequirements::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update distributor information while verification is ' . $existing->status
                ], 400);
            }
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'business_registration_number' => 'required|string|max:100',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if distributor requirements already exist
            $requirements = DistributorRequirements::where('user_id', $user->id)->first();
            
            if (!$requirements) {
                // Create new record
                $requirements = DistributorRequirements::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'business_registration_number' => $request->business_registration_number,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                    'status' => 'draft'
                ]);
            } else {
                // Update existing record
                $requirements->company_name = $request->company_name;
                $requirements->business_registration_number = $request->business_registration_number;
                $requirements->valid_id_type = $request->valid_id_type;
                $requirements->id_number = $request->id_number;
                $requirements->save();
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Distributor information updated successfully',
                'data' => [
                    'company_name' => $requirements->company_name,
                    'business_registration_number' => $requirements->business_registration_number,
                    'valid_id_type' => $requirements->valid_id_type,
                    'id_type_name' => $requirements->id_type_name,
                    'id_number' => $requirements->id_number,
                    'has_submitted' => false
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error updating distributor information:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update distributor information'
            ], 500);
        }
    }

    /**
     * Submit a replacement for one of the distributor's dated documents.
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
     * Splitting it means the reviews match the documents one to one, and a
     * distributor renewing a Mayor's Permit is not made to re-upload a DTI
     * Certificate that is still valid.
     *
     * POST, not PUT, and the reason is a PHP one worth writing down so nobody
     * "corrects" it later: PHP only runs its multipart parser for POST. A PUT or
     * PATCH carrying multipart/form-data leaves $_POST and $_FILES empty and the
     * body sitting unread in php://input, so $request->file() returns null and every
     * field reads as missing -- a 422 complaining that no file was attached, sent by
     * a client that demonstrably attached one. Measured, not assumed.
     *
     * POST is also the more truthful verb. Each submission opens a review and a
     * later submission supersedes the earlier one, so this creates a resource
     * rather than idempotently replacing one. Hence 201 and a Location header.
     */
    public function replaceDocument(Request $request, string $documentKey)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'distributor') {
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

        $requirements = DistributorRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $review = DocumentSubmission::applyDocument($request, $requirements, $documentKey, 'distributor_verification');

        $label = DocumentExpiry::label($documentKey);

        $documents = DocumentSubmission::payload($requirements->refresh()->load('relatedDocuments'));

        // Pushed so the admin's renewals queue gains a card without anyone
        // reloading it. Sent even though the user's own panel is the one that
        // navigated here -- the point is that the queue is a shared screen.
        event(new DocumentsRenewed($user->id, 'distributor', [$documentKey], $documents));

        return response()->json([
            'status' => 'success',
            'message' => $label . ' renewed and awaiting review by an administrator.',
            'data' => [
                'document' => $documents[$documentKey],
                'review'   => $review->toDisplayArray(),
                'documents' => $documents,
            ]
        ], 201)
            ->header('Location', url("/api/distributor/requirements/reviews/{$review->id}"));
    }

    /**
     * Read back one of the distributor's own document reviews.
     *
     * Exists so the Location header on a submission resolves to something. Scoped
     * through the authenticated user's own reviews, so an id belonging to another
     * account is a 404 rather than a disclosure of what that account submitted.
     */
    public function showReview(Request $request, $reviewId)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'distributor') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = DistributorRequirements::where('user_id', $user->id)->first();

        if (! $requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $review = $requirements->documentReviews()->find($reviewId);

        if (! $review) {
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
     * Read back one of the distributor's own extra documents.
     *
     * The only reason this exists is to give `storeRelatedDocument` a Location
     * header that points at something fetchable, which is what makes that header
     * worth sending. Scoped through the authenticated user's requirements row, so
     * another account's document id is a 404.
     */
    public function showRelatedDocument(Request $request, $documentId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'distributor') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = DistributorRequirements::where('user_id', $user->id)->first();

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
     * 'pending' because nothing a user attaches has been looked at yet.
     */
    public function storeRelatedDocument(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'distributor') {
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

        $requirements = DistributorRequirements::where('user_id', $user->id)->first();

        if (!$requirements) {
            return response()->json([
                'status' => 'error',
                'message' => 'No business verification submission found'
            ], 404);
        }

        $document = DocumentSubmission::addRelatedDocument($request, $requirements, 'distributor_verification');

        $documents = DocumentSubmission::payload($requirements->refresh()->load('relatedDocuments'));

        event(new DocumentsRenewed($user->id, 'distributor', [], $documents));

        return response()->json([
            'status' => 'success',
            'message' => 'Document added',
            'data' => [
                'document' => $document->toDisplayArray(),
                'documents' => $documents,
            ]
        ], 201)
            ->header('Location', url("/api/distributor/requirements/related-documents/{$document->id}"));
    }

    /**
     * Remove one of the distributor's own extra documents.
     *
     * Looked up through the authenticated user's own requirements row rather than
     * by primary key, so a guessed document id belonging to another account is a
     * 404 instead of a deletion. The file goes with the row; leaving orphans in
     * storage would be worse than a missed tidy-up.
     */
    public function destroyRelatedDocument(Request $request, $documentId)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'distributor') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $requirements = DistributorRequirements::where('user_id', $user->id)->first();

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
        event(new DocumentsRenewed($user->id, 'distributor', [], $documents));

        return response()->json([
            'status' => 'success',
            'message' => 'Document removed',
            'data' => ['documents' => $documents],
        ], 200);
    }
}