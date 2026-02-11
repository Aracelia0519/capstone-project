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
                    'is_complete' => $requirements->is_complete,
                    'has_submitted' => true,
                    'photos' => $photoUrls,
                    'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirements->updated_at->format('Y-m-d H:i:s')
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
                
                // New Address Validation
                'province' => 'required|string|in:Cavite',
                'city' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'block_address' => 'required|string|max:1000',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if user already has a pending or approved submission
            $existing = DistributorRequirements::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a ' . $existing->status . ' business verification submission'
                ], 400);
            }
            
            // Use Transaction to ensure both Requirements and Address are saved
            DB::beginTransaction();
            
            try {
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
                
                // Create distributor requirement
                $requirements = DistributorRequirements::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                    'valid_id_photo' => $uploadData['valid_id_photo'],
                    'dti_certificate_photo' => $uploadData['dti_certificate_photo'],
                    'mayor_permit_photo' => $uploadData['mayor_permit_photo'],
                    'barangay_clearance_photo' => $uploadData['barangay_clearance_photo'],
                    'business_registration_number' => $request->business_registration_number,
                    'business_registration_photo' => $uploadData['business_registration_photo'],
                    'status' => 'pending',
                    'rejection_reason' => null
                ]);

                // Create Address
                DistributorAddress::create([
                    'distributor_requirements_id' => $requirements->id,
                    'province' => 'Cavite', // Enforced as per request
                    'city' => $request->city,
                    'barangay' => $request->barangay,
                    'block_address' => $request->block_address,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                DB::commit();

                $photoUrls = $requirements->getAllPhotoUrls();
                $requirements->load('address'); // Load the new address
                
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
                        'is_complete' => $requirements->is_complete,
                        'has_submitted' => true,
                        'photos' => $photoUrls,
                        'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s')
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
}