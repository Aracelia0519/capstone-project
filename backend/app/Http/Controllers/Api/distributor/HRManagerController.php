<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Distributor\HRManager;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HRManagerController extends Controller
{
    /**
     * Get all HR managers for the current distributor
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
                    'message' => 'You must be verified as a distributor to manage HR managers'
                ], 403);
            }
            
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            $employmentType = $request->input('employment_type', '');
            
            $query = HRManager::byParent($user->id);
            
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
            $hrManagers = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Transform data
            $transformedData = $hrManagers->map(function ($manager) {
                return [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,
                    'full_name' => $manager->full_name,
                    'email' => $manager->email,
                    'phone' => $manager->phone,
                    'address' => $manager->address,
                    'position' => $manager->position,
                    'employment_type' => $manager->employment_type,
                    'employment_type_name' => $manager->employment_type_name,
                    'hire_date' => $manager->hire_date,
                    'formatted_hire_date' => $manager->formatted_hire_date,
                    'salary' => $manager->salary,
                    'formatted_salary' => $manager->formatted_salary,
                    'valid_id_type' => $manager->valid_id_type,
                    'id_type_name' => $manager->id_type_name,
                    'id_number' => $manager->id_number,
                    'valid_id_photo_url' => $manager->valid_id_photo_url,
                    'resume_url' => $manager->resume_url,
                    'employment_contract_url' => $manager->employment_contract_url,
                    'status' => $manager->status,
                    'status_class' => $manager->status_class,
                    'has_user_account' => $manager->hasUserAccount(),
                    'user_id' => $manager->user_id,
                    'created_at' => $manager->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $manager->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'hr_managers' => $transformedData,
                    'pagination' => [
                        'total' => $hrManagers->total(),
                        'per_page' => $hrManagers->perPage(),
                        'current_page' => $hrManagers->currentPage(),
                        'last_page' => $hrManagers->lastPage(),
                        'from' => $hrManagers->firstItem(),
                        'to' => $hrManagers->lastItem()
                    ],
                    'employment_types' => [
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary'
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching HR managers:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch HR managers'
            ], 500);
        }
    }

    /**
     * Create a new HR manager
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
                    'message' => 'You must be verified as a distributor to create HR managers'
                ], 403);
            }
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email|unique:hr_managers,email',
                'phone' => 'required|string|max:11|min:11|regex:/^[0-9]+$/',
                'address' => 'nullable|string|max:500',
                'employment_type' => 'required|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'required|date',
                'salary' => 'required|numeric|min:0',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
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
                'employment_type.required' => 'Employment type is required',
                'hire_date.required' => 'Hire date is required',
                'hire_date.date' => 'Please enter a valid date',
                'salary.required' => 'Salary is required',
                'salary.numeric' => 'Salary must be a number',
                'salary.min' => 'Salary must be at least 0',
                'valid_id_type.required' => 'Please select a valid ID type',
                'id_number.required' => 'ID number is required',
                'valid_id_photo.required' => 'Valid ID photo is required',
                'valid_id_photo.mimes' => 'Only JPG, PNG, and PDF files are allowed',
                'valid_id_photo.max' => 'File size must be less than 5MB',
                'resume.mimes' => 'Only PDF, DOC, and DOCX files are allowed',
                'resume.max' => 'File size must be less than 5MB',
                'employment_contract.mimes' => 'Only PDF files are allowed',
                'employment_contract.max' => 'File size must be less than 5MB',
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
            
            // Handle file uploads
            $validIdPhotoPath = null;
            $resumePath = null;
            $contractPath = null;
            
            // Upload valid ID photo
            if ($request->hasFile('valid_id_photo')) {
                $file = $request->file('valid_id_photo');
                $fileName = 'hr_manager_id_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $validIdPhotoPath = $folderPath . '/' . $fileName;
            }
            
            // Upload resume
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $fileName = 'hr_manager_resume_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $resumePath = $folderPath . '/' . $fileName;
            }
            
            // Upload employment contract
            if ($request->hasFile('employment_contract')) {
                $file = $request->file('employment_contract');
                $fileName = 'hr_manager_contract_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $contractPath = $folderPath . '/' . $fileName;
            }
            
            // Create the user account with role 'hr_manager'
            $userAccount = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => 'hr_manager',
                'status' => 'active'
            ]);
            
            // Create HR manager linked to the user account
            $hrManager = HRManager::create([
                'parent_distributor_id' => $user->id,
                'user_id' => $userAccount->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'position' => 'HR Manager',
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'valid_id_type' => $request->valid_id_type,
                'id_number' => $request->id_number,
                'valid_id_photo' => $validIdPhotoPath,
                'resume' => $resumePath,
                'employment_contract' => $contractPath,
                'status' => 'active'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager created successfully. User account has been created.',
                'data' => [
                    'hr_manager' => [
                        'id' => $hrManager->id,
                        'first_name' => $hrManager->first_name,
                        'last_name' => $hrManager->last_name,
                        'full_name' => $hrManager->full_name,
                        'email' => $hrManager->email,
                        'phone' => $hrManager->phone,
                        'address' => $hrManager->address,
                        'position' => $hrManager->position,
                        'employment_type' => $hrManager->employment_type,
                        'employment_type_name' => $hrManager->employment_type_name,
                        'hire_date' => $hrManager->hire_date,
                        'formatted_hire_date' => $hrManager->formatted_hire_date,
                        'salary' => $hrManager->salary,
                        'formatted_salary' => $hrManager->formatted_salary,
                        'valid_id_type' => $hrManager->valid_id_type,
                        'id_type_name' => $hrManager->id_type_name,
                        'id_number' => $hrManager->id_number,
                        'valid_id_photo_url' => $hrManager->valid_id_photo_url,
                        'resume_url' => $hrManager->resume_url,
                        'employment_contract_url' => $hrManager->employment_contract_url,
                        'status' => $hrManager->status,
                        'status_class' => $hrManager->status_class,
                        'has_user_account' => true,
                        'user_id' => $hrManager->user_id,
                        'created_at' => $hrManager->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating HR manager:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create HR Manager',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific HR manager
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'hr_manager' => [
                        'id' => $hrManager->id,
                        'first_name' => $hrManager->first_name,
                        'last_name' => $hrManager->last_name,
                        'full_name' => $hrManager->full_name,
                        'email' => $hrManager->email,
                        'phone' => $hrManager->phone,
                        'address' => $hrManager->address,
                        'position' => $hrManager->position,
                        'employment_type' => $hrManager->employment_type,
                        'employment_type_name' => $hrManager->employment_type_name,
                        'hire_date' => $hrManager->hire_date,
                        'formatted_hire_date' => $hrManager->formatted_hire_date,
                        'salary' => $hrManager->salary,
                        'formatted_salary' => $hrManager->formatted_salary,
                        'valid_id_type' => $hrManager->valid_id_type,
                        'id_type_name' => $hrManager->id_type_name,
                        'id_number' => $hrManager->id_number,
                        'valid_id_photo_url' => $hrManager->valid_id_photo_url,
                        'resume_url' => $hrManager->resume_url,
                        'employment_contract_url' => $hrManager->employment_contract_url,
                        'status' => $hrManager->status,
                        'status_class' => $hrManager->status_class,
                        'has_user_account' => $hrManager->hasUserAccount(),
                        'user_id' => $hrManager->user_id,
                        'created_at' => $hrManager->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $hrManager->updated_at->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching HR manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'HR Manager not found'
            ], 404);
        }
    }

    /**
     * Update HR manager
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) use ($hrManager) {
                        if (User::where('email', $value)->where('id', '!=', $hrManager->user_id)->exists() ||
                            HRManager::where('email', $value)->where('id', '!=', $hrManager->id)->exists()) {
                            $fail('This email is already registered');
                        }
                    }
                ],
                'phone' => 'sometimes|string|max:11|min:11|regex:/^[0-9]+$/',
                'address' => 'nullable|string|max:500',
                'employment_type' => 'sometimes|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'sometimes|date',
                'salary' => 'sometimes|numeric|min:0',
                'valid_id_type' => 'sometimes|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'sometimes|string|max:100',
                'valid_id_photo' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
                'status' => 'sometimes|in:active,inactive,pending,on_leave'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Handle file uploads if provided
            $updateData = $request->only([
                'first_name', 'last_name', 'email', 'phone', 'address',
                'employment_type', 'hire_date', 'salary',
                'valid_id_type', 'id_number', 'status'
            ]);
            
            // Handle valid ID photo upload
            if ($request->hasFile('valid_id_photo')) {
                // Delete old file if exists
                if ($hrManager->valid_id_photo) {
                    Storage::disk('public')->delete($hrManager->valid_id_photo);
                }
                
                $file = $request->file('valid_id_photo');
                $fileName = 'hr_manager_id_' . $hrManager->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $updateData['valid_id_photo'] = $folderPath . '/' . $fileName;
            }
            
            // Handle resume upload
            if ($request->hasFile('resume')) {
                // Delete old file if exists
                if ($hrManager->resume) {
                    Storage::disk('public')->delete($hrManager->resume);
                }
                
                $file = $request->file('resume');
                $fileName = 'hr_manager_resume_' . $hrManager->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $updateData['resume'] = $folderPath . '/' . $fileName;
            }
            
            // Handle employment contract upload
            if ($request->hasFile('employment_contract')) {
                // Delete old file if exists
                if ($hrManager->employment_contract) {
                    Storage::disk('public')->delete($hrManager->employment_contract);
                }
                
                $file = $request->file('employment_contract');
                $fileName = 'hr_manager_contract_' . $hrManager->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'hr_managers/documents';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $updateData['employment_contract'] = $folderPath . '/' . $fileName;
            }
            
            // Update HR manager
            $hrManager->update($updateData);
            
            // Update user account if exists
            if ($hrManager->hasUserAccount()) {
                $userAccount = $hrManager->user;
                $userAccount->update([
                    'first_name' => $hrManager->first_name,
                    'last_name' => $hrManager->last_name,
                    'email' => $hrManager->email,
                    'phone' => $hrManager->phone,
                    'address' => $hrManager->address,
                    'status' => $hrManager->status === 'active' ? 'active' : 'inactive'
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager updated successfully',
                'data' => [
                    'hr_manager' => [
                        'id' => $hrManager->id,
                        'full_name' => $hrManager->full_name,
                        'email' => $hrManager->email,
                        'position' => $hrManager->position,
                        'status' => $hrManager->status,
                        'has_user_account' => $hrManager->hasUserAccount()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating HR manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update HR Manager'
            ], 500);
        }
    }

    /**
     * Delete HR manager
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            // Delete user account if exists
            if ($hrManager->hasUserAccount()) {
                $hrManager->user->delete();
            }
            
            // Delete files if exists
            $files = [
                $hrManager->valid_id_photo,
                $hrManager->resume,
                $hrManager->employment_contract
            ];
            
            foreach ($files as $file) {
                if ($file) {
                    Storage::disk('public')->delete($file);
                }
            }
            
            // Soft delete
            $hrManager->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting HR manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete HR Manager'
            ], 500);
        }
    }

    /**
     * Activate HR manager
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            $hrManager->activate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager activated successfully',
                'data' => [
                    'status' => $hrManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error activating HR manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate HR Manager'
            ], 500);
        }
    }

    /**
     * Deactivate HR manager
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            $hrManager->deactivate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager deactivated successfully',
                'data' => [
                    'status' => $hrManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deactivating HR manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate HR Manager'
            ], 500);
        }
    }

    /**
     * Put HR manager on leave
     */
    public function putOnLeave($id)
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
            
            // Find HR manager
            $hrManager = HRManager::byParent($user->id)
                ->findOrFail($id);
            
            $hrManager->putOnLeave();
            
            return response()->json([
                'status' => 'success',
                'message' => 'HR Manager put on leave successfully',
                'data' => [
                    'status' => $hrManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error putting HR manager on leave:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to put HR Manager on leave'
            ], 500);
        }
    }

    /**
     * Get HR manager statistics
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
            
            $total = HRManager::byParent($user->id)->count();
            $active = HRManager::byParent($user->id)->active()->count();
            $pending = HRManager::byParent($user->id)->pending()->count();
            $onLeave = HRManager::byParent($user->id)->where('status', 'on_leave')->count();
            $inactive = HRManager::byParent($user->id)->where('status', 'inactive')->count();
            
            // Employment type distribution
            $employmentTypes = [
                'full_time' => 'Full Time',
                'part_time' => 'Part Time',
                'contract' => 'Contract',
                'temporary' => 'Temporary'
            ];
            
            $employmentStats = [];
            foreach ($employmentTypes as $key => $name) {
                $count = HRManager::byParent($user->id)->where('employment_type', $key)->count();
                if ($count > 0) {
                    $employmentStats[] = [
                        'employment_type' => $key,
                        'employment_type_name' => $name,
                        'count' => $count,
                        'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
                    ];
                }
            }
            
            // Salary statistics
            $totalSalary = HRManager::byParent($user->id)->whereNotNull('salary')->sum('salary');
            $averageSalary = $total > 0 ? round($totalSalary / $total, 2) : 0;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'pending' => $pending,
                    'on_leave' => $onLeave,
                    'inactive' => $inactive,
                    'with_accounts' => HRManager::byParent($user->id)->whereNotNull('user_id')->count(),
                    'employment_distribution' => $employmentStats,
                    'salary_statistics' => [
                        'total_salary' => $totalSalary,
                        'average_salary' => $averageSalary,
                        'formatted_total_salary' => '₱' . number_format($totalSalary, 2),
                        'formatted_average_salary' => '₱' . number_format($averageSalary, 2)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching HR manager statistics:', [
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