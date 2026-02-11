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
            $employmentType = $request->input('employment_type', '');
            
            $query = OperationalDistributor::byParent($user->id);
            
            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
                });
            }
            
            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply employment type filter
            if ($employmentType) {
                $query->where('employment_type', $employmentType);
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
                    
                    // Employment Data
                    'employment_type' => $distributor->employment_type,
                    'employment_type_name' => $distributor->employment_type_name,
                    'hire_date' => $distributor->hire_date ? $distributor->hire_date->format('Y-m-d') : null,
                    'salary' => $distributor->salary,
                    'position' => $distributor->position,
                    
                    // Documents
                    'valid_id_type' => $distributor->valid_id_type,
                    'id_type_name' => $distributor->id_type_name,
                    'id_number' => $distributor->id_number,
                    'valid_id_photo_url' => $distributor->valid_id_photo_url,
                    'resume_url' => $distributor->resume_url,
                    'employment_contract_url' => $distributor->employment_contract_url,
                    
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
                'address' => 'nullable|string',
                
                // Employment info
                'employment_type' => 'required|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'required|date',
                'salary' => 'required|numeric|min:0',
                'position' => 'nullable|string|max:255',
                
                // IDs
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                
                // Optional docs
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
                
                // Account
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8'
            ], [
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'email.required' => 'Email address is required',
                'email.unique' => 'This email is already registered',
                'phone.required' => 'Phone number is required',
                'employment_type.required' => 'Employment type is required',
                'hire_date.required' => 'Hire date is required',
                'salary.required' => 'Salary is required',
                'valid_id_type.required' => 'Please select a valid ID type',
                'valid_id_photo.required' => 'Valid ID photo is required',
                'password.confirmed' => 'Password confirmation does not match'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file uploads
            $validIdPhotoPath = null;
            $resumePath = null;
            $contractPath = null;
            
            if ($request->hasFile('valid_id_photo')) {
                $file = $request->file('valid_id_photo');
                $fileName = 'op_dist_id_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'operational_distributors/valid_ids';
                $validIdPhotoPath = $file->storeAs($folderPath, $fileName, 'public');
            }

            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $fileName = 'op_dist_resume_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'operational_distributors/resumes';
                $resumePath = $file->storeAs($folderPath, $fileName, 'public');
            }

            if ($request->hasFile('employment_contract')) {
                $file = $request->file('employment_contract');
                $fileName = 'op_dist_contract_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'operational_distributors/contracts';
                $contractPath = $file->storeAs($folderPath, $fileName, 'public');
            }
            
            // Create the user account
            $userAccount = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password, // Mutator hashes this
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => 'operational_distributor',
                'status' => 'active' // Set active by default for new operational distributors
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
                
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'position' => $request->position ?? 'Operational Distributor',
                
                'valid_id_type' => $request->valid_id_type,
                'id_number' => $request->id_number,
                'valid_id_photo' => $validIdPhotoPath,
                'resume' => $resumePath,
                'employment_contract' => $contractPath,
                
                'status' => 'active'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor created successfully.',
                'data' => [
                    'operational_distributor' => $operationalDistributor
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating operational distributor:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
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
                        
                        'employment_type' => $operationalDistributor->employment_type,
                        'employment_type_name' => $operationalDistributor->employment_type_name,
                        'hire_date' => $operationalDistributor->hire_date ? $operationalDistributor->hire_date->format('Y-m-d') : null,
                        'salary' => $operationalDistributor->salary,
                        'position' => $operationalDistributor->position,
                        
                        'valid_id_type' => $operationalDistributor->valid_id_type,
                        'id_type_name' => $operationalDistributor->id_type_name,
                        'id_number' => $operationalDistributor->id_number,
                        'valid_id_photo_url' => $operationalDistributor->valid_id_photo_url,
                        'resume_url' => $operationalDistributor->resume_url,
                        'employment_contract_url' => $operationalDistributor->employment_contract_url,
                        
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
                'address' => 'nullable|string',
                
                'employment_type' => 'sometimes|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'sometimes|date',
                'salary' => 'sometimes|numeric|min:0',
                
                'valid_id_type' => 'sometimes|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'sometimes|string|max:100',
                
                'valid_id_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
                
                'status' => 'sometimes|in:active,inactive'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file updates
            if ($request->hasFile('valid_id_photo')) {
                if ($operationalDistributor->valid_id_photo) {
                    Storage::disk('public')->delete($operationalDistributor->valid_id_photo);
                }
                $file = $request->file('valid_id_photo');
                $fileName = 'op_dist_id_' . $operationalDistributor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $operationalDistributor->valid_id_photo = $file->storeAs('operational_distributors/valid_ids', $fileName, 'public');
            }

            if ($request->hasFile('resume')) {
                if ($operationalDistributor->resume) {
                    Storage::disk('public')->delete($operationalDistributor->resume);
                }
                $file = $request->file('resume');
                $fileName = 'op_dist_resume_' . $operationalDistributor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $operationalDistributor->resume = $file->storeAs('operational_distributors/resumes', $fileName, 'public');
            }

            if ($request->hasFile('employment_contract')) {
                if ($operationalDistributor->employment_contract) {
                    Storage::disk('public')->delete($operationalDistributor->employment_contract);
                }
                $file = $request->file('employment_contract');
                $fileName = 'op_dist_contract_' . $operationalDistributor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $operationalDistributor->employment_contract = $file->storeAs('operational_distributors/contracts', $fileName, 'public');
            }
            
            // Update main data
            $operationalDistributor->fill($request->except(['valid_id_photo', 'resume', 'employment_contract', 'password', 'password_confirmation']));
            $operationalDistributor->save();
            
            // Update user account if exists
            if ($operationalDistributor->hasUserAccount()) {
                $userUpdateData = [
                    'first_name' => $operationalDistributor->first_name,
                    'last_name' => $operationalDistributor->last_name,
                    'email' => $operationalDistributor->email,
                    'phone' => $operationalDistributor->phone,
                    'address' => $operationalDistributor->address,
                    'status' => $operationalDistributor->status === 'active' ? 'active' : 'inactive'
                ];
                
                // Allow password update
                if ($request->filled('password')) {
                    $userUpdateData['password'] = $request->password;
                }
                
                $operationalDistributor->user->update($userUpdateData);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Operational distributor updated successfully'
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
            
            // Delete files
            if ($operationalDistributor->valid_id_photo) {
                Storage::disk('public')->delete($operationalDistributor->valid_id_photo);
            }
            if ($operationalDistributor->resume) {
                Storage::disk('public')->delete($operationalDistributor->resume);
            }
            if ($operationalDistributor->employment_contract) {
                Storage::disk('public')->delete($operationalDistributor->employment_contract);
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