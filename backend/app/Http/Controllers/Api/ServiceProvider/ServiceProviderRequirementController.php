<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceProvider\ServiceProviderRequirement;
use App\Models\ServiceProvider\ServiceProviderAddress;
use Illuminate\Support\Str;
use App\Events\Requirements\RequirementSubmitted; 
use App\Events\Requirements\RequirementStatusUpdated;
use App\Models\SupportMessage; // <-- ADDED FOR CHAT
use App\Events\SupportMessageSent; // <-- ADDED FOR CHAT

class ServiceProviderRequirementController extends Controller
{
    /**
     * Get service provider's ID verification status
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Check if user is a service provider
        if (!$user->isServiceProvider()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only service providers can access this resource.'
            ], 403);
        }

        $requirement = ServiceProviderRequirement::with('address')->where('user_id', $user->id)->first();

        if (!$requirement) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'status' => 'not_submitted',
                    'id_type' => null,
                    'id_number' => null,
                    'submitted_at' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                    'resubmission_count' => 0,
                    'id_photo_url' => null,
                    'selfie_photo_url' => null,
                    'address' => null
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'status' => $requirement->status,
                'id_type' => $requirement->valid_id_type,
                'id_number' => $requirement->id_number,
                'submitted_at' => $requirement->submitted_at,
                'reviewed_at' => $requirement->reviewed_at,
                'rejection_reason' => $requirement->rejection_reason,
                'resubmission_count' => $requirement->resubmission_count ?? 0,
                'id_photo_url' => $requirement->valid_id_photo 
                    ? asset('storage/' . $requirement->valid_id_photo)
                    : null,
                'selfie_photo_url' => $requirement->selfie_with_id_photo 
                    ? asset('storage/' . $requirement->selfie_with_id_photo)
                    : null,
                'address' => $requirement->address
            ]
        ]);
    }

    /**
     * Submit ID verification
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Check if user is a service provider
        if (!$user->isServiceProvider()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only service providers can submit ID verification.'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'id_type' => 'required|in:phil_id,passport,driver_license,umid,prc_id,voter_id,postal_id,philhealth,nbi,senior_citizen,other',
            'id_number' => 'required|string|max:50',
            // Image validation depends on if they are submitting for the first time or replacing files
            'id_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            'selfie_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            
            // Address validation strictly enforced to Cavite bounds
            'province' => 'required|string|in:Cavite',
            'city' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'block_address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|min:14.0000|max:14.6000',
            'longitude' => 'nullable|numeric|min:120.5000|max:121.1000',
        ], [
            'id_photo.max' => 'The ID photo must not exceed 5MB.',
            'selfie_photo.max' => 'The selfie photo must not exceed 5MB.',
            'latitude.min' => 'Location pinned must be inside Cavite.',
            'latitude.max' => 'Location pinned must be inside Cavite.',
            'longitude.min' => 'Location pinned must be inside Cavite.',
            'longitude.max' => 'Location pinned must be inside Cavite.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if already has pending or approved verification
        $existing = ServiceProviderRequirement::where('user_id', $user->id)
            ->first();

        if ($existing) {
            if (in_array($existing->status, ['pending', 'verified', 'approved'])) {
                 return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a ' . $existing->status . ' ID verification.'
                ], 400);
            }

            // Maximum 3 Submissions Blocker
            if ($existing->resubmission_count >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maximum resubmission attempts reached. Please contact admin via Chat.'
                ], 403);
            }
        } else {
            // Require images for initial submission
            if (!$request->hasFile('id_photo') || !$request->hasFile('selfie_photo')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => [
                        'id_photo' => ['The ID photo is required.'],
                        'selfie_photo' => ['The selfie photo is required.']
                    ]
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            // Create folder paths
            $idPhotoFolder = 'SP/ID_photos';
            $selfiePhotoFolder = 'SP/Selfie_photos';
            
            // Upload ID photo using public disk
            $idPhotoPath = null;
            if ($request->hasFile('id_photo')) {
                $idPhoto = $request->file('id_photo');
                $idPhotoName = 'sp_id_' . $user->id . '_' . time() . '_' . Str::random(10) . '.' . $idPhoto->getClientOriginalExtension();
                $idPhotoPath = Storage::disk('public')->putFileAs($idPhotoFolder, $idPhoto, $idPhotoName);
            }

            // Upload selfie photo using public disk
            $selfiePhotoPath = null;
            if ($request->hasFile('selfie_photo')) {
                $selfiePhoto = $request->file('selfie_photo');
                $selfiePhotoName = 'sp_selfie_' . $user->id . '_' . time() . '_' . Str::random(10) . '.' . $selfiePhoto->getClientOriginalExtension();
                $selfiePhotoPath = Storage::disk('public')->putFileAs($selfiePhotoFolder, $selfiePhoto, $selfiePhotoName);
            }

            // Create or update requirement using firstOrNew to prevent mass assignment array errors if column is new
            $requirement = ServiceProviderRequirement::firstOrNew(['user_id' => $user->id]);
            $requirement->valid_id_type = $request->id_type;
            $requirement->id_number = $request->id_number;
            
            if ($idPhotoPath) {
                $requirement->valid_id_photo = $idPhotoPath;
            } elseif ($existing && $existing->valid_id_photo) {
                $requirement->valid_id_photo = $existing->valid_id_photo;
            }

            if ($selfiePhotoPath) {
                $requirement->selfie_with_id_photo = $selfiePhotoPath;
            } elseif ($existing && $existing->selfie_with_id_photo) {
                $requirement->selfie_with_id_photo = $existing->selfie_with_id_photo;
            }

            $requirement->status = 'pending';
            $requirement->submitted_at = now();
            $requirement->reviewed_at = null;
            $requirement->rejection_reason = null;
            $requirement->resubmission_count = $existing ? ($existing->resubmission_count + 1) : 1;
            $requirement->save();

            // Create or update address
            ServiceProviderAddress::updateOrCreate(
                ['service_provider_requirements_id' => $requirement->id],
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

            // 🔔 Broadcast Event to Admin Frontend instantly
            event(new RequirementSubmitted($user));

            return response()->json([
                'status' => 'success',
                'message' => 'ID verification submitted successfully!',
                'data' => [
                    'status' => $requirement->status,
                    'id_type' => $requirement->valid_id_type,
                    'id_number' => $requirement->id_number,
                    'submitted_at' => $requirement->submitted_at,
                    'resubmission_count' => $requirement->resubmission_count,
                    'id_photo_url' => $requirement->valid_id_photo ? asset('storage/' . $requirement->valid_id_photo) : null,
                    'selfie_photo_url' => $requirement->selfie_with_id_photo ? asset('storage/' . $requirement->selfie_with_id_photo) : null,
                    'address' => $requirement->address
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Clean up uploaded files if error occurs
            if (isset($idPhotoPath) && Storage::disk('public')->exists($idPhotoPath)) {
                Storage::disk('public')->delete($idPhotoPath);
            }
            if (isset($selfiePhotoPath) && Storage::disk('public')->exists($selfiePhotoPath)) {
                Storage::disk('public')->delete($selfiePhotoPath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit ID verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending verifications (admin only)
     */
    public function pending(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $requirements = ServiceProviderRequirement::with(['user:id,first_name,last_name,email,phone', 'address'])
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($requirement) {
                return [
                    'id' => $requirement->id,
                    'user_id' => $requirement->user_id,
                    'user_name' => $requirement->user->full_name,
                    'user_email' => $requirement->user->email,
                    'user_phone' => $requirement->user->phone,
                    'id_type' => $requirement->valid_id_type,
                    'id_type_readable' => $requirement->getReadableIdType(),
                    'id_number' => $requirement->id_number,
                    'id_photo_url' => $requirement->valid_id_photo 
                        ? asset('storage/' . $requirement->valid_id_photo)
                        : null,
                    'selfie_photo_url' => $requirement->selfie_with_id_photo 
                        ? asset('storage/' . $requirement->selfie_with_id_photo)
                        : null,
                    'status' => $requirement->status,
                    'submitted_at' => $requirement->submitted_at,
                    'reviewed_at' => $requirement->reviewed_at,
                    'resubmission_count' => $requirement->resubmission_count ?? 0,
                    'address' => $requirement->address
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $requirements
        ]);
    }

    /**
     * Update verification status (admin only)
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requirement = ServiceProviderRequirement::find($id);

        if (!$requirement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requirement not found.'
            ], 404);
        }

        try {
            if ($request->status === 'verified') {
                $requirement->markAsVerified();
            } else {
                $requirement->markAsRejected($request->rejection_reason);
            }

            $requirement->load('user:id,first_name,last_name,email', 'address');

            // 🔔 Broadcast Event back to the Service Provider instantly
            event(new RequirementStatusUpdated($requirement->user_id, $requirement->status, $requirement->rejection_reason));

            return response()->json([
                'status' => 'success',
                'message' => 'Verification status updated successfully.',
                'data' => [
                    'id' => $requirement->id,
                    'user_id' => $requirement->user_id,
                    'user_name' => $requirement->user->full_name,
                    'user_email' => $requirement->user->email,
                    'id_type' => $requirement->valid_id_type,
                    'id_number' => $requirement->id_number,
                    'status' => $requirement->status,
                    'rejection_reason' => $requirement->rejection_reason,
                    'resubmission_count' => $requirement->resubmission_count ?? 0,
                    'submitted_at' => $requirement->submitted_at,
                    'reviewed_at' => $requirement->reviewed_at,
                    'address' => $requirement->address,
                    'id_photo_url' => $requirement->valid_id_photo 
                        ? asset('storage/' . $requirement->valid_id_photo)
                        : null,
                    'selfie_photo_url' => $requirement->selfie_with_id_photo 
                        ? asset('storage/' . $requirement->selfie_with_id_photo)
                        : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update verification status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification statistics (admin only)
     */
    public function statistics(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $stats = ServiceProviderRequirement::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = "not_submitted" THEN 1 ELSE 0 END) as not_submitted
        ')->first();

        $recentSubmissions = ServiceProviderRequirement::with('user:id,first_name,last_name')
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($requirement) {
                return [
                    'id' => $requirement->id,
                    'user_name' => $requirement->user->full_name,
                    'id_type' => $requirement->getReadableIdType(),
                    'submitted_at' => $requirement->submitted_at,
                    'days_pending' => $requirement->submitted_at ? now()->diffInDays($requirement->submitted_at) : 0,
                    'id_photo_url' => $requirement->valid_id_photo 
                        ? asset('storage/' . $requirement->valid_id_photo)
                        : null,
                    'selfie_photo_url' => $requirement->selfie_with_id_photo 
                        ? asset('storage/' . $requirement->selfie_with_id_photo)
                        : null
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $stats,
                'recent_submissions' => $recentSubmissions
            ]
        ]);
    }

    /**
     * Get Support Messages
     */
    public function getSupportMessages(Request $request)
    {
        $userId = $request->user()->id;
        $messages = SupportMessage::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages]);
    }

    /**
     * Send Support Message
     */
    public function sendSupportMessage(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $userId = $request->user()->id;

        $message = SupportMessage::create([
            'sender_id' => $userId,
            'receiver_id' => null, // Goes to Admin pool
            'message' => $request->message
        ]);

        broadcast(new SupportMessageSent($message, $userId))->toOthers();

        return response()->json(['status' => 'success', 'message_data' => $message]);
    }
}