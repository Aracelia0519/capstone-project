<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Client\ClientRequirement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientRequirementController extends Controller
{
    /**
     * Get client's ID verification status
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $requirement = ClientRequirement::where('user_id', $user->id)->first();
            
            if (!$requirement) {
                return response()->json([
                    'status' => 'success',
                    'id_verification' => null
                ], 200);
            }
            
            return response()->json([
                'status' => 'success',
                'id_verification' => [
                    'id' => $requirement->id,
                    'id_type' => $requirement->valid_id_type,
                    'id_type_name' => $requirement->id_type_name,
                    'id_number' => $requirement->id_number,
                    'id_photo_url' => $requirement->valid_id_photo 
                        ? asset('storage/' . $requirement->valid_id_photo)
                        : null,
                    'status' => $requirement->status,
                    'status_class' => $requirement->status_class,
                    'rejection_reason' => $requirement->rejection_reason,
                    'submitted_at' => $requirement->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirement->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching ID verification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch ID verification data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit ID verification
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'id_type' => 'required|string|in:philid,passport,driver_license,umid,prc,voter,postal,philhealth,nbi,senior_citizen,other',
                'id_number' => 'required|string|max:100',
                'id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120' // 5MB max
            ], [
                'id_type.required' => 'Please select an ID type',
                'id_type.in' => 'Please select a valid ID type',
                'id_number.required' => 'Please enter your ID number',
                'id_photo.required' => 'Please upload a photo of your ID',
                'id_photo.mimes' => 'Only JPG, PNG, and PDF files are allowed',
                'id_photo.max' => 'File size must be less than 5MB'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if user already has a pending or approved submission
            $existing = ClientRequirement::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a ' . $existing->status . ' ID verification submission'
                ], 400);
            }
            
            // Handle file upload
            $file = $request->file('id_photo');
            $fileName = $user->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $folderPath = 'id_verification';
            
            // Store the file in the public disk
            $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
            
            // Log for debugging
            Log::info('File uploaded:', [
                'user_id' => $user->id,
                'file_name' => $fileName,
                'storage_path' => $path,
                'full_path' => storage_path('app/public/' . $folderPath . '/' . $fileName),
                'url' => asset('storage/' . $folderPath . '/' . $fileName),
                'file_exists' => Storage::disk('public')->exists($folderPath . '/' . $fileName)
            ]);
            
            // The path to store in database
            $dbFilePath = $folderPath . '/' . $fileName;
            
            // Create or update client requirement
            $requirement = ClientRequirement::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'valid_id_type' => $request->id_type,
                    'id_number' => $request->id_number,
                    'valid_id_photo' => $dbFilePath,
                    'status' => 'pending',
                    'rejection_reason' => null
                ]
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'ID verification submitted successfully! Your ID is now pending review.',
                'id_verification' => [
                    'id' => $requirement->id,
                    'id_type' => $requirement->valid_id_type,
                    'id_type_name' => $requirement->id_type_name,
                    'id_number' => $requirement->id_number,
                    'id_photo_url' => asset('storage/' . $dbFilePath),
                    'status' => $requirement->status,
                    'status_class' => $requirement->status_class,
                    'submitted_at' => $requirement->created_at->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error submitting ID verification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit ID verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update ID verification (for admin)
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
            
            $requirement = ClientRequirement::findOrFail($id);
            
            $requirement->status = $request->status;
            
            if ($request->status == 'rejected') {
                $requirement->rejection_reason = $request->rejection_reason;
            } else {
                $requirement->rejection_reason = null;
            }
            
            $requirement->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'ID verification status updated successfully',
                'id_verification' => [
                    'id' => $requirement->id,
                    'user_id' => $requirement->user_id,
                    'status' => $requirement->status,
                    'rejection_reason' => $requirement->rejection_reason,
                    'updated_at' => $requirement->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error updating ID verification:', [
                'admin_id' => Auth::id(),
                'verification_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update ID verification',
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
            
            $query = ClientRequirement::pending()
                ->with(['user:id,first_name,last_name,email,phone,created_at']);
            
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
                });
            }
            
            $requirements = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->through(function ($requirement) {
                    return [
                        'id' => $requirement->id,
                        'user' => [
                            'id' => $requirement->user->id,
                            'name' => $requirement->user->first_name . ' ' . $requirement->user->last_name,
                            'email' => $requirement->user->email,
                            'phone' => $requirement->user->phone,
                            'joined_at' => $requirement->user->created_at->format('Y-m-d H:i:s')
                        ],
                        'id_type' => $requirement->valid_id_type,
                        'id_type_name' => $requirement->id_type_name,
                        'id_number' => $requirement->id_number,
                        'id_photo_url' => $requirement->valid_id_photo 
                            ? asset('storage/' . $requirement->valid_id_photo)
                            : null,
                        'status' => $requirement->status,
                        'status_class' => $requirement->status_class,
                        'submitted_at' => $requirement->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $requirement->updated_at->format('Y-m-d H:i:s')
                    ];
                });
            
            return response()->json([
                'status' => 'success',
                'pending_verifications' => $requirements->items(),
                'pagination' => [
                    'total' => $requirements->total(),
                    'per_page' => $requirements->perPage(),
                    'current_page' => $requirements->currentPage(),
                    'last_page' => $requirements->lastPage(),
                    'from' => $requirements->firstItem(),
                    'to' => $requirements->lastItem()
                ],
                'count' => $requirements->total()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching pending verifications:', [
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
}