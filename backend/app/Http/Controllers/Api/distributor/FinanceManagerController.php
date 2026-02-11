<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Finance\FinanceManager;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class FinanceManagerController extends Controller
{
    /**
     * Get all finance managers for the current distributor
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
                    'message' => 'You must be verified as a distributor to manage finance managers'
                ], 403);
            }
            
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            $employmentType = $request->input('employment_type', '');
            
            $query = FinanceManager::byParent($user->id);
            
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
            $financeManagers = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Transform data
            $transformedData = $financeManagers->map(function ($manager) {
                return [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,
                    'full_name' => $manager->full_name,
                    'email' => $manager->email,
                    'phone' => $manager->phone,
                    'valid_id_type' => $manager->valid_id_type,
                    'id_type_name' => $manager->id_type_name,
                    'id_number' => $manager->id_number,
                    'valid_id_photo_url' => $manager->valid_id_photo_url,
                    'employment_type' => $manager->employment_type,
                    'employment_type_name' => $manager->employment_type_name,
                    'hire_date' => $manager->hire_date,
                    'formatted_hire_date' => $manager->formatted_hire_date,
                    'salary' => $manager->salary,
                    'formatted_salary' => $manager->formatted_salary,
                    'position' => $manager->position,
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
                    'finance_managers' => $transformedData,
                    'pagination' => [
                        'total' => $financeManagers->total(),
                        'per_page' => $financeManagers->perPage(),
                        'current_page' => $financeManagers->currentPage(),
                        'last_page' => $financeManagers->lastPage(),
                        'from' => $financeManagers->firstItem(),
                        'to' => $financeManagers->lastItem()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching finance managers:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch finance managers'
            ], 500);
        }
    }

    /**
     * Create a new finance manager
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
                    'message' => 'You must be verified as a distributor to create finance managers'
                ], 403);
            }
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email|unique:finance_managers,email',
                'phone' => 'required|string|max:11|min:11|regex:/^[0-9]+$/',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'employment_type' => 'required|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'required|date',
                'salary' => 'required|numeric|min:0',
                'position' => 'nullable|string|max:255',
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
                'valid_id_type.required' => 'Please select a valid ID type',
                'id_number.required' => 'ID number is required',
                'valid_id_photo.required' => 'Valid ID photo is required',
                'valid_id_photo.mimes' => 'Only JPG, PNG, and PDF files are allowed',
                'valid_id_photo.max' => 'File size must be less than 5MB',
                'employment_type.required' => 'Employment type is required',
                'hire_date.required' => 'Hire date is required',
                'salary.required' => 'Salary is required',
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
            $employmentContractPath = null;
            
            // Upload valid ID photo
            if ($request->hasFile('valid_id_photo')) {
                $file = $request->file('valid_id_photo');
                $fileName = 'finance_manager_valid_id_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/valid_ids';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $validIdPhotoPath = $folderPath . '/' . $fileName;
            }
            
            // Upload resume if provided
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $fileName = 'finance_manager_resume_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/resumes';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $resumePath = $folderPath . '/' . $fileName;
            }
            
            // Upload employment contract if provided
            if ($request->hasFile('employment_contract')) {
                $file = $request->file('employment_contract');
                $fileName = 'finance_manager_contract_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/contracts';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $employmentContractPath = $folderPath . '/' . $fileName;
            }
            
            $userAccount = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'role' => 'finance_manager', 
                'status' => 'active'
            ]);
            
            // Create finance manager linked to the user account
            $financeManager = FinanceManager::create([
                'parent_distributor_id' => $user->id,
                'user_id' => $userAccount->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'valid_id_type' => $request->valid_id_type,
                'id_number' => $request->id_number,
                'valid_id_photo' => $validIdPhotoPath,
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'position' => $request->position ?? 'Finance Manager',
                'resume' => $resumePath,
                'employment_contract' => $employmentContractPath,
                'status' => 'active'
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager created successfully. User account has been created.',
                'data' => [
                    'finance_manager' => [
                        'id' => $financeManager->id,
                        'first_name' => $financeManager->first_name,
                        'last_name' => $financeManager->last_name,
                        'full_name' => $financeManager->full_name,
                        'email' => $financeManager->email,
                        'phone' => $financeManager->phone,
                        'valid_id_type' => $financeManager->valid_id_type,
                        'id_type_name' => $financeManager->id_type_name,
                        'id_number' => $financeManager->id_number,
                        'valid_id_photo_url' => $financeManager->valid_id_photo_url,
                        'employment_type' => $financeManager->employment_type,
                        'employment_type_name' => $financeManager->employment_type_name,
                        'hire_date' => $financeManager->hire_date,
                        'formatted_hire_date' => $financeManager->formatted_hire_date,
                        'salary' => $financeManager->salary,
                        'formatted_salary' => $financeManager->formatted_salary,
                        'position' => $financeManager->position,
                        'resume_url' => $financeManager->resume_url,
                        'employment_contract_url' => $financeManager->employment_contract_url,
                        'status' => $financeManager->status,
                        'status_class' => $financeManager->status_class,
                        'has_user_account' => true,
                        'user_id' => $financeManager->user_id,
                        'created_at' => $financeManager->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating finance manager:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create finance manager',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific finance manager
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'finance_manager' => [
                        'id' => $financeManager->id,
                        'first_name' => $financeManager->first_name,
                        'last_name' => $financeManager->last_name,
                        'full_name' => $financeManager->full_name,
                        'email' => $financeManager->email,
                        'phone' => $financeManager->phone,
                        'valid_id_type' => $financeManager->valid_id_type,
                        'id_type_name' => $financeManager->id_type_name,
                        'id_number' => $financeManager->id_number,
                        'valid_id_photo_url' => $financeManager->valid_id_photo_url,
                        'employment_type' => $financeManager->employment_type,
                        'employment_type_name' => $financeManager->employment_type_name,
                        'hire_date' => $financeManager->hire_date,
                        'formatted_hire_date' => $financeManager->formatted_hire_date,
                        'salary' => $financeManager->salary,
                        'formatted_salary' => $financeManager->formatted_salary,
                        'position' => $financeManager->position,
                        'resume_url' => $financeManager->resume_url,
                        'employment_contract_url' => $financeManager->employment_contract_url,
                        'status' => $financeManager->status,
                        'status_class' => $financeManager->status_class,
                        'has_user_account' => $financeManager->hasUserAccount(),
                        'user_id' => $financeManager->user_id,
                        'created_at' => $financeManager->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $financeManager->updated_at->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching finance manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Finance manager not found'
            ], 404);
        }
    }

    /**
     * Update finance manager
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) use ($financeManager) {
                        if (User::where('email', $value)->where('id', '!=', $financeManager->user_id)->exists() ||
                            FinanceManager::where('email', $value)->where('id', '!=', $financeManager->id)->exists()) {
                            $fail('This email is already registered');
                        }
                    }
                ],
                'phone' => 'sometimes|string|max:11|min:11|regex:/^[0-9]+$/',
                'valid_id_type' => 'sometimes|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'sometimes|string|max:100',
                'valid_id_photo' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'employment_type' => 'sometimes|string|in:full_time,part_time,contract,temporary',
                'hire_date' => 'sometimes|date',
                'salary' => 'sometimes|numeric|min:0',
                'position' => 'nullable|string|max:255',
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
            if ($request->hasFile('valid_id_photo')) {
                // Delete old file if exists
                if ($financeManager->valid_id_photo) {
                    Storage::disk('public')->delete($financeManager->valid_id_photo);
                }
                
                $file = $request->file('valid_id_photo');
                $fileName = 'finance_manager_' . $financeManager->id . '_valid_id_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/valid_ids';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $financeManager->valid_id_photo = $folderPath . '/' . $fileName;
            }
            
            if ($request->hasFile('resume')) {
                // Delete old file if exists
                if ($financeManager->resume) {
                    Storage::disk('public')->delete($financeManager->resume);
                }
                
                $file = $request->file('resume');
                $fileName = 'finance_manager_' . $financeManager->id . '_resume_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/resumes';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $financeManager->resume = $folderPath . '/' . $fileName;
            }
            
            if ($request->hasFile('employment_contract')) {
                // Delete old file if exists
                if ($financeManager->employment_contract) {
                    Storage::disk('public')->delete($financeManager->employment_contract);
                }
                
                $file = $request->file('employment_contract');
                $fileName = 'finance_manager_' . $financeManager->id . '_contract_' . time() . '.' . $file->getClientOriginalExtension();
                $folderPath = 'finance_managers/contracts';
                
                $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                $financeManager->employment_contract = $folderPath . '/' . $fileName;
            }
            
            // Update fields
            $updateData = $request->only([
                'first_name', 'last_name', 'email', 'phone',
                'valid_id_type', 'id_number', 'employment_type', 'hire_date',
                'salary', 'position', 'status'
            ]);
            
            $financeManager->update($updateData);
            
            // Update user account if exists
            if ($financeManager->hasUserAccount()) {
                $userAccount = $financeManager->user;
                $userAccount->update([
                    'first_name' => $financeManager->first_name,
                    'last_name' => $financeManager->last_name,
                    'email' => $financeManager->email,
                    'phone' => $financeManager->phone,
                    'status' => $financeManager->status === 'active' ? 'active' : 'inactive'
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager updated successfully',
                'data' => [
                    'finance_manager' => [
                        'id' => $financeManager->id,
                        'full_name' => $financeManager->full_name,
                        'email' => $financeManager->email,
                        'status' => $financeManager->status,
                        'has_user_account' => $financeManager->hasUserAccount()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating finance manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update finance manager'
            ], 500);
        }
    }

    /**
     * Delete finance manager
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            // Delete user account if exists
            if ($financeManager->hasUserAccount()) {
                $financeManager->user->delete();
            }
            
            // Delete files if exist
            if ($financeManager->valid_id_photo) {
                Storage::disk('public')->delete($financeManager->valid_id_photo);
            }
            if ($financeManager->resume) {
                Storage::disk('public')->delete($financeManager->resume);
            }
            if ($financeManager->employment_contract) {
                Storage::disk('public')->delete($financeManager->employment_contract);
            }
            
            // Soft delete
            $financeManager->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting finance manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete finance manager'
            ], 500);
        }
    }

    /**
     * Activate finance manager
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            $financeManager->activate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager activated successfully',
                'data' => [
                    'status' => $financeManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error activating finance manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to activate finance manager'
            ], 500);
        }
    }

    /**
     * Deactivate finance manager
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            $financeManager->deactivate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager deactivated successfully',
                'data' => [
                    'status' => $financeManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deactivating finance manager:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to deactivate finance manager'
            ], 500);
        }
    }

    /**
     * Put finance manager on leave
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
            
            // Find finance manager
            $financeManager = FinanceManager::byParent($user->id)
                ->findOrFail($id);
            
            $financeManager->putOnLeave();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Finance Manager put on leave successfully',
                'data' => [
                    'status' => $financeManager->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error putting finance manager on leave:', [
                'user_id' => Auth::id(),
                'manager_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to put finance manager on leave'
            ], 500);
        }
    }

    /**
     * Get finance manager statistics
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
            
            $total = FinanceManager::byParent($user->id)->count();
            $active = FinanceManager::byParent($user->id)->active()->count();
            $pending = FinanceManager::byParent($user->id)->pending()->count();
            $onLeave = FinanceManager::byParent($user->id)->onLeave()->count();
            $inactive = $total - $active - $pending - $onLeave;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'pending' => $pending,
                    'inactive' => $inactive,
                    'on_leave' => $onLeave,
                    'with_accounts' => FinanceManager::byParent($user->id)->whereNotNull('user_id')->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching finance manager statistics:', [
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