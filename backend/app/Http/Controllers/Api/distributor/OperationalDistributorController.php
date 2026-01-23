<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Distributor\OperationalDistributor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class OperationalDistributorController extends Controller
{
    /**
     * Get all operational distributors for the current distributor
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
            
            // Check if parent distributor is verified
            $parentVerification = $user->distributorRequirement;
            if (!$parentVerification || $parentVerification->status !== 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must be verified as a distributor to manage operational distributors'
                ], 403);
            }
            
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            
            $query = OperationalDistributor::byParent($user->id);
            
            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }
            
            // Paginate results
            $operationalDistributors = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Transform data
            $transformedData = $operationalDistributors->map(function ($distributor) {
                return [
                    'id' => $distributor->id,
                    'first_name' => $distributor->first_name,
                    'last_name' => $distributor->last_name,
                    'full_name' => $distributor->full_name,
                    'email' => $distributor->email,
                    'phone' => $distributor->phone,
                    'address' => $distributor->address,
                    'valid_id_type' => $distributor->valid_id_type,
                    'id_type_name' => $distributor->id_type_name,
                    'id_number' => $distributor->id_number,
                    'valid_id_photo_url' => $distributor->valid_id_photo_url,
                    'status' => $distributor->status,
                    'status_class' => $distributor->status_class,
                    'has_user_account' => $distributor->hasUserAccount(),
                    'user_id' => $distributor->user_id,
                    'created_at' => $distributor->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $distributor->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'operational_distributors' => $transformedData,
                    'pagination' => [
                        'total' => $operationalDistributors->total(),
                        'per_page' => $operationalDistributors->perPage(),
                        'current_page' => $operationalDistributors->currentPage(),
                        'last_page' => $operationalDistributors->lastPage(),
                        'from' => $operationalDistributors->firstItem(),
                        'to' => $operationalDistributors->lastItem()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching operational distributors:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch operational distributors'
            ], 500);
        }
    }

    /**
     * Create a new operational distributor
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
            
            // Check if parent distributor is verified
            $parentVerification = $user->distributorRequirement;
            if (!$parentVerification || $parentVerification->status !== 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must be verified as a distributor to create operational distributors'
                ], 403);
            }
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email|unique:operational_distributors,email',
                'phone' => 'required|string|max:11|min:11|regex:/^[0-9]+$/',
                'address' => 'nullable|string|max:500',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8'
            ], [
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'email.required' => 'Email address is required',
                'email.email' => 'Please enter a valid email address',
                'email.unique' => 'This email is already registered',
                'phone.required' => 'Phone number is required',
                'phone.max' => 'Phone number must be exactly 11 digits',
                'phone.min' => 'Phone number must be exactly 11 digits',
                'phone.regex' => 'Phone number must contain only digits',
                'valid_id_type.required' => 'Please select a valid ID type',
                'id_number.required' => 'ID number is required',
                'valid_id_photo.required' => 'Valid ID photo is required',
                'valid_id_photo.mimes' => 'Only JPG, PNG, and PDF files are allowed',
                'valid_id_photo.max' => 'File size must be less than 5MB',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file upload
            $validIdPhotoPath = null;
            if ($request->hasFile('valid_id_photo')) {
                $file = $request->file('valid_id_photo');
                $fileName = 'operational_distributor_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'operational_distributors/valid_ids';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $validIdPhotoPath = $folderPath . '/' . $fileName;
            }
            
            // Create the user account with role 'operational_distributor'
            $userAccount = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => 'operational_distributor',
                'status' => 'pending'
            ]);
            
            // Create operational distributor linked to the user account
            $operationalDistributor = OperationalDistributor::create([
                'parent_distributor_id' => $user->id,
                'user_id' => $userAccount->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'valid_id_type' => $request->valid_id_type,
                'id_number' => $request->id_number,
                'valid_id_photo' => $validIdPhotoPath,
                'status' => 'pending'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor created successfully. User account has been created.',
                'data' => [
                    'operational_distributor' => [
                        'id' => $operationalDistributor->id,
                        'first_name' => $operationalDistributor->first_name,
                        'last_name' => $operationalDistributor->last_name,
                        'full_name' => $operationalDistributor->full_name,
                        'email' => $operationalDistributor->email,
                        'phone' => $operationalDistributor->phone,
                        'address' => $operationalDistributor->address,
                        'valid_id_type' => $operationalDistributor->valid_id_type,
                        'id_type_name' => $operationalDistributor->id_type_name,
                        'id_number' => $operationalDistributor->id_number,
                        'valid_id_photo_url' => $operationalDistributor->valid_id_photo_url,
                        'status' => $operationalDistributor->status,
                        'status_class' => $operationalDistributor->status_class,
                        'has_user_account' => true,
                        'user_id' => $operationalDistributor->user_id,
                        'created_at' => $operationalDistributor->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating operational distributor:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create operational distributor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific operational distributor
     */
    public function show($id)
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
            
            // Find operational distributor
            $operationalDistributor = OperationalDistributor::byParent($user->id)
                ->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'operational_distributor' => [
                        'id' => $operationalDistributor->id,
                        'first_name' => $operationalDistributor->first_name,
                        'last_name' => $operationalDistributor->last_name,
                        'full_name' => $operationalDistributor->full_name,
                        'email' => $operationalDistributor->email,
                        'phone' => $operationalDistributor->phone,
                        'address' => $operationalDistributor->address,
                        'valid_id_type' => $operationalDistributor->valid_id_type,
                        'id_type_name' => $operationalDistributor->id_type_name,
                        'id_number' => $operationalDistributor->id_number,
                        'valid_id_photo_url' => $operationalDistributor->valid_id_photo_url,
                        'status' => $operationalDistributor->status,
                        'status_class' => $operationalDistributor->status_class,
                        'has_user_account' => $operationalDistributor->hasUserAccount(),
                        'user_id' => $operationalDistributor->user_id,
                        'created_at' => $operationalDistributor->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $operationalDistributor->updated_at->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching operational distributor:', [
                'user_id' => Auth::id(),
                'distributor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Operational distributor not found'
            ], 404);
        }
    }

    /**
     * Update operational distributor
     */
    public function update(Request $request, $id)
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
            
            // Find operational distributor
            $operationalDistributor = OperationalDistributor::byParent($user->id)
                ->findOrFail($id);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) use ($operationalDistributor) {
                        if (User::where('email', $value)->where('id', '!=', $operationalDistributor->user_id)->exists() ||
                            OperationalDistributor::where('email', $value)->where('id', '!=', $operationalDistributor->id)->exists()) {
                            $fail('This email is already registered');
                        }
                    }
                ],
                'phone' => 'sometimes|string|max:11|min:11|regex:/^[0-9]+$/',
                'address' => 'nullable|string|max:500',
                'valid_id_type' => 'sometimes|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'sometimes|string|max:100',
                'valid_id_photo' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'status' => 'sometimes|in:active,inactive'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file upload if provided
            if ($request->hasFile('valid_id_photo')) {
                // Delete old file if exists
                if ($operationalDistributor->valid_id_photo) {
                    Storage::disk('public')->delete($operationalDistributor->valid_id_photo);
                }
                
                $file = $request->file('valid_id_photo');
                $fileName = 'operational_distributor_' . $operationalDistributor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'operational_distributors/valid_ids';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $operationalDistributor->valid_id_photo = $folderPath . '/' . $fileName;
            }
            
            // Update fields
            $updateData = $request->only([
                'first_name', 'last_name', 'email', 'phone', 'address',
                'valid_id_type', 'id_number', 'status'
            ]);
            
            $operationalDistributor->update($updateData);
            
            // Update user account if exists
            if ($operationalDistributor->hasUserAccount()) {
                $userAccount = $operationalDistributor->user;
                $userAccount->update([
                    'first_name' => $operationalDistributor->first_name,
                    'last_name' => $operationalDistributor->last_name,
                    'email' => $operationalDistributor->email,
                    'phone' => $operationalDistributor->phone,
                    'address' => $operationalDistributor->address,
                    'status' => $operationalDistributor->status === 'active' ? 'active' : 'inactive'
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor updated successfully',
                'data' => [
                    'operational_distributor' => [
                        'id' => $operationalDistributor->id,
                        'full_name' => $operationalDistributor->full_name,
                        'email' => $operationalDistributor->email,
                        'status' => $operationalDistributor->status,
                        'has_user_account' => $operationalDistributor->hasUserAccount()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating operational distributor:', [
                'user_id' => Auth::id(),
                'distributor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update operational distributor'
            ], 500);
        }
    }

    /**
     * Delete operational distributor
     */
    public function destroy($id)
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
            
            // Find operational distributor
            $operationalDistributor = OperationalDistributor::byParent($user->id)
                ->findOrFail($id);
            
            // Delete user account if exists
            if ($operationalDistributor->hasUserAccount()) {
                $operationalDistributor->user->delete();
            }
            
            // Delete valid ID photo if exists
            if ($operationalDistributor->valid_id_photo) {
                Storage::disk('public')->delete($operationalDistributor->valid_id_photo);
            }
            
            // Soft delete
            $operationalDistributor->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting operational distributor:', [
                'user_id' => Auth::id(),
                'distributor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete operational distributor'
            ], 500);
        }
    }

    /**
     * Activate operational distributor
     */
    public function activate($id)
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
            
            // Find operational distributor
            $operationalDistributor = OperationalDistributor::byParent($user->id)
                ->findOrFail($id);
            
            $operationalDistributor->activate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor activated successfully',
                'data' => [
                    'status' => $operationalDistributor->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error activating operational distributor:', [
                'user_id' => Auth::id(),
                'distributor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate operational distributor'
            ], 500);
        }
    }

    /**
     * Deactivate operational distributor
     */
    public function deactivate($id)
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
            
            // Find operational distributor
            $operationalDistributor = OperationalDistributor::byParent($user->id)
                ->findOrFail($id);
            
            $operationalDistributor->deactivate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor deactivated successfully',
                'data' => [
                    'status' => $operationalDistributor->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deactivating operational distributor:', [
                'user_id' => Auth::id(),
                'distributor_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate operational distributor'
            ], 500);
        }
    }

    /**
     * Get operational distributor statistics
     */
    public function statistics()
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
            
            $total = OperationalDistributor::byParent($user->id)->count();
            $active = OperationalDistributor::byParent($user->id)->active()->count();
            $pending = OperationalDistributor::byParent($user->id)->pending()->count();
            $inactive = $total - $active - $pending;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'pending' => $pending,
                    'inactive' => $inactive,
                    'with_accounts' => OperationalDistributor::byParent($user->id)->whereNotNull('user_id')->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching operational distributor statistics:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch statistics'
            ], 500);
        }
    }
}