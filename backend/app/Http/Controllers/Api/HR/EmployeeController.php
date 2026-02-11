<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\User;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\HRManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    // Get all employees for the current distributor (HR Manager only)
    public function index(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can access employees.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search', '');
            $department = $request->input('department', '');
            $employmentStatus = $request->input('employment_status', '');
            $status = $request->input('status', 'active');
            
            $query = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->with(['createdBy' => function($q) {
                    $q->select('id', 'first_name', 'last_name', 'email');
                }])
                ->with(['hrManager' => function($q) {
                    $q->select('id', 'first_name', 'last_name', 'email');
                }])
                ->with(['user' => function($q) {
                    $q->select('id', 'email', 'role', 'status', 'created_at');
                }]);
            
            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            // Apply department filter
            if ($department) {
                $query->where('department', $department);
            }
            
            // Apply employment status filter
            if ($employmentStatus) {
                $query->where('employment_status', $employmentStatus);
            }
            
            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }
            
            // Get unique departments for current distributor
            $departments = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->distinct('department')
                ->pluck('department')
                ->filter()
                ->values();
            
            // Paginate results
            $employees = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Transform data (excluding password)
            $transformedEmployees = $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'last_name' => $employee->last_name,
                    'full_name' => $employee->full_name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'department' => $employee->department,
                    'position' => $employee->position,
                    'employment_type' => $employee->employment_type,
                    'employment_status' => $employee->employment_status,
                    'hire_date' => $employee->hire_date->format('Y-m-d'),
                    'salary' => $employee->formatted_salary,
                    'status' => $employee->status,
                    'user_id' => $employee->user_id,
                    'user' => $employee->user ? [
                        'status' => $employee->user->status,
                        'created_at' => $employee->user->created_at->format('Y-m-d H:i:s')
                    ] : null,
                    'created_by' => $employee->createdBy ? [
                        'name' => $employee->createdBy->full_name,
                        'email' => $employee->createdBy->email
                    ] : null,
                    'hr_manager' => $employee->hrManager ? [
                        'name' => $employee->hrManager->first_name . ' ' . $employee->hrManager->last_name,
                        'email' => $employee->hrManager->email
                    ] : null,
                    'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $employee->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'employees' => $transformedEmployees,
                    'departments' => $departments,
                    'pagination' => [
                        'total' => $employees->total(),
                        'per_page' => $employees->perPage(),
                        'current_page' => $employees->currentPage(),
                        'last_page' => $employees->lastPage(),
                        'from' => $employees->firstItem(),
                        'to' => $employees->lastItem()
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Get single employee
    public function show($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can view employee details.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $employee = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->with(['createdBy', 'hrManager', 'parentDistributor', 'user'])
                ->findOrFail($id);
            
            // Get distributor company name
            $distributorRequirement = DistributorRequirements::where('user_id', $employee->parent_distributor_id)->first();
            $companyName = $distributorRequirement ? $distributorRequirement->company_name : $employee->parentDistributor->full_name;
            
            $employeeData = [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'emergency_contact' => $employee->emergency_contact,
                'address' => $employee->address,
                'date_of_birth' => $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : null,
                'age' => $employee->age,
                'gender' => $employee->gender,
                'marital_status' => $employee->marital_status,
                'nationality' => $employee->nationality,
                'department' => $employee->department,
                'position' => $employee->position,
                'employment_type' => $employee->employment_type,
                'employment_status' => $employee->employment_status,
                'hire_date' => $employee->hire_date->format('Y-m-d'),
                'probation_end_date' => $employee->probation_end_date ? $employee->probation_end_date->format('Y-m-d') : null,
                'regularization_date' => $employee->regularization_date ? $employee->regularization_date->format('Y-m-d') : null,
                'years_of_service' => $employee->years_of_service,
                'salary' => $employee->salary,
                'formatted_salary' => $employee->formatted_salary,
                'salary_currency' => $employee->salary_currency,
                'payment_frequency' => $employee->payment_frequency,
                'bank_name' => $employee->bank_name,
                'bank_account_number' => $employee->bank_account_number,
                'bank_account_name' => $employee->bank_account_name,
                'sss_number' => $employee->sss_number,
                'philhealth_number' => $employee->philhealth_number,
                'pagibig_number' => $employee->pagibig_number,
                'tin_number' => $employee->tin_number,
                'valid_id_type' => $employee->valid_id_type,
                'id_number' => $employee->id_number,
                'educational_attainment' => $employee->educational_attainment,
                'school_graduated' => $employee->school_graduated,
                'year_graduated' => $employee->year_graduated,
                'course' => $employee->course,
                'status' => $employee->status,
                'notes' => $employee->notes,
                'user_id' => $employee->user_id,
                'user' => $employee->user ? [
                    'id' => $employee->user->id,
                    'email' => $employee->user->email,
                    'role' => $employee->user->role,
                    'status' => $employee->user->status,
                    'created_at' => $employee->user->created_at->format('Y-m-d H:i:s')
                ] : null,
                'company_name' => $companyName,
                'created_by' => $employee->createdBy ? [
                    'name' => $employee->createdBy->full_name,
                    'email' => $employee->createdBy->email
                ] : null,
                'hr_manager' => $employee->hrManager ? [
                    'name' => $employee->hrManager->first_name . ' ' . $employee->hrManager->last_name,
                    'email' => $employee->hrManager->email
                ] : null,
                'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $employee->updated_at->format('Y-m-d H:i:s'),
                // Document URLs
                'valid_id_photo_url' => $employee->valid_id_photo ? Storage::url($employee->valid_id_photo) : null,
                'resume_url' => $employee->resume ? Storage::url($employee->resume) : null,
                'employment_contract_url' => $employee->employment_contract ? Storage::url($employee->employment_contract) : null,
                'medical_certificate_url' => $employee->medical_certificate ? Storage::url($employee->medical_certificate) : null,
                'nbi_clearance_url' => $employee->nbi_clearance ? Storage::url($employee->nbi_clearance) : null,
                'police_clearance_url' => $employee->police_clearance ? Storage::url($employee->police_clearance) : null,
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => $employeeData
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    // Create new employee
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can create employees.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            // Define custom error messages
            $messages = [
                'email.unique' => 'The email address is already in use by another user or employee.',
                'phone.required' => 'A contact number is required.',
                'phone.regex' => 'The phone number must be exactly 11 digits and contain only numbers.',
                'emergency_contact.regex' => 'The emergency contact must be exactly 11 digits and contain only numbers.',
                'password.confirmed' => 'The password confirmation does not match.',
                'hire_date.date' => 'Please provide a valid hire date.',
                'date_of_birth.before' => 'The employee must be at least 1 day old.',
                'salary.min' => 'Salary cannot be negative.',
                'valid_id_photo.required' => 'A valid ID photo is required.',
                'resume.required' => 'A resume is required.',
                'employment_contract.required' => 'An employment contract is required.',
                'medical_certificate.required' => 'A medical certificate is required.',
                'nbi_clearance.required' => 'An NBI clearance is required.',
                'police_clearance.required' => 'A police clearance is required.',
                'bank_account_number.regex' => 'Bank account number must contain only numbers.',
                'sss_number.regex' => 'SSS number must contain only numbers.',
                'philhealth_number.regex' => 'PhilHealth number must contain only numbers.',
                'pagibig_number.regex' => 'Pag-IBIG number must contain only numbers.',
                'tin_number.regex' => 'TIN number must contain only numbers.',
                'id_number.regex' => 'ID number must contain only numbers.',
            ];

            // Validate request
            $validator = Validator::make($request->all(), [
                // Personal Information
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:hr_employees,email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
                // Updated: strictly 11 digits
                'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],
                'emergency_contact' => ['nullable', 'string', 'regex:/^[0-9]{11}$/'],
                'address' => 'required|string|max:500',
                'date_of_birth' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'marital_status' => 'required|in:single,married,widowed,separated,divorced',
                'nationality' => 'required|string|max:100',
                
                // Employment Details
                'department' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'employment_type' => 'required|in:full_time,part_time,contract,probationary,intern',
                'employment_status' => 'required|in:probationary,regular,contractual,resigned,terminated,retired',
                'hire_date' => 'required|date',
                'probation_end_date' => 'nullable|date|after:hire_date',
                'regularization_date' => 'nullable|date|after:hire_date',
                
                // Compensation
                'salary' => 'required|numeric|min:0',
                'salary_currency' => 'required|string|max:3',
                'payment_frequency' => 'required|in:monthly,bi_monthly,weekly,daily',
                
                // Bank Details
                'bank_name' => 'nullable|string|max:255',
                // Updated: numbers only
                'bank_account_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'bank_account_name' => 'nullable|string|max:255',
                
                // Government Numbers (Updated: numbers only)
                'sss_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'philhealth_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'pagibig_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'tin_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                
                // Identification
                'valid_id_type' => 'required|string|max:100',
                // Updated: numbers only
                'id_number' => ['required', 'string', 'regex:/^[0-9]+$/'],
                
                // Educational Background
                'educational_attainment' => 'required|string|max:100',
                'school_graduated' => 'required|string|max:255',
                'year_graduated' => 'required|integer|min:1900|max:' . date('Y'),
                'course' => 'required|string|max:255',
                
                // File Uploads (NOW REQUIRED)
                'valid_id_photo' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'required|file|mimes:pdf|max:5120',
                'medical_certificate' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'nbi_clearance' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'police_clearance' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                
                'notes' => 'nullable|string'
            ], $messages);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please check the form for errors.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // GENERATE EMPLOYEE CODE - Logic moved here to prevent Model dependency error
            // Format: EMP-{ParentDistributorID}-{Year}-{Sequence}
            // e.g., EMP-1-2024-0001
            $prefix = 'EMP-' . $hrManager->parent_distributor_id . '-' . date('Y') . '-';
            
            $lastEmployee = Employee::where('employee_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastEmployee) {
                // Extract the sequence number
                $lastSequence = intval(substr($lastEmployee->employee_code, -4));
                $sequence = $lastSequence + 1;
            } else {
                $sequence = 1;
            }
            
            $employeeCode = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            // Step 1: Create User account
            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password, // Hash the password!
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => 'employee', // New role for employees
                'status' => 'active' // Employees should be active by default
            ];
            
            $employeeUser = User::create($userData);
            
            // Step 2: Create employee data array
            $employeeData = $request->only([
                'first_name', 'middle_name', 'last_name', 'email', 
                'phone', 'emergency_contact', 'address', 'date_of_birth', 'gender',
                'marital_status', 'nationality', 'department', 'position',
                'employment_type', 'employment_status', 'hire_date',
                'probation_end_date', 'regularization_date', 'salary',
                'salary_currency', 'payment_frequency', 'bank_name',
                'bank_account_number', 'bank_account_name', 'sss_number',
                'philhealth_number', 'pagibig_number', 'tin_number',
                'valid_id_type', 'id_number', 'educational_attainment',
                'school_graduated', 'year_graduated', 'course', 'notes'
            ]);
            
            // Add system fields
            $employeeData['password'] = $userData['password']; // Store hashed password in employee table too if needed
            $employeeData['employee_code'] = $employeeCode;
            $employeeData['parent_distributor_id'] = $hrManager->parent_distributor_id;
            $employeeData['hr_manager_id'] = $hrManager->id;
            $employeeData['created_by_user_id'] = $user->id;
            $employeeData['user_id'] = $employeeUser->id; // Link to User table
            $employeeData['status'] = 'active';
            
            // Handle file uploads
            $fileFields = [
                'valid_id_photo' => 'hr/employees/documents/valid_ids',
                'resume' => 'hr/employees/documents/resumes',
                'employment_contract' => 'hr/employees/documents/contracts',
                'medical_certificate' => 'hr/employees/documents/medical',
                'nbi_clearance' => 'hr/employees/documents/clearances',
                'police_clearance' => 'hr/employees/documents/clearances'
            ];
            
            foreach ($fileFields as $field => $path) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $fileName = time() . '_' . $employeeCode . '_' . $field . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs($path, $fileName, 'public');
                    $employeeData[$field] = $filePath;
                }
            }
            
            // Create employee
            $employee = Employee::create($employeeData);
            
            // Log the creation
            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'user_id' => $employeeUser->id,
                'created_by' => $user->id
            ]);
            
            // Commit transaction
            DB::commit();
            
            // Get company name for response
            $distributorRequirement = DistributorRequirements::where('user_id', $hrManager->parent_distributor_id)->first();
            $companyName = $distributorRequirement ? $distributorRequirement->company_name : 'Unknown Company';
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employee created successfully',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'full_name' => $employee->full_name,
                        'email' => $employee->email,
                        'department' => $employee->department,
                        'position' => $employee->position,
                        'company_name' => $companyName,
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee creation failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage(), // Return exact error for debugging
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Update employee
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can update employees.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $employee = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->findOrFail($id);
            
            // Validate request
            $validator = Validator::make($request->all(), [
                // Only allow updating certain fields
                'department' => 'sometimes|string|max:255',
                'position' => 'sometimes|string|max:255',
                'employment_status' => 'sometimes|in:probationary,regular,contractual,resigned,terminated,retired',
                'salary' => 'sometimes|numeric|min:0',
                'salary_currency' => 'sometimes|string|max:3',
                'status' => 'sometimes|in:active,inactive,on_leave',
                'password' => 'nullable|string|min:8|confirmed',
                'password_confirmation' => 'nullable|string|min:8',
                'notes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Update employee
            $updateData = $request->only([
                'department', 'position', 'employment_status',
                'salary', 'salary_currency', 'status', 'notes'
            ]);
            
            // Only update password if provided
            if ($request->filled('password')) {
                $hashedPassword = Hash::make($request->password);
                $updateData['password'] = $hashedPassword;
                
                // Also update the corresponding User account password
                if ($employee->user_id) {
                    $user = User::find($employee->user_id);
                    if ($user) {
                        $user->update(['password' => $hashedPassword]);
                    }
                }
            }
            
            $employee->update($updateData);
            
            // Update User status if employee status changed
            if ($request->has('status') && $employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $userStatus = $request->status === 'active' ? 'active' : 'inactive';
                    $user->update(['status' => $userStatus]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employee updated successfully',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'full_name' => $employee->full_name,
                        'department' => $employee->department,
                        'position' => $employee->position,
                        'employment_status' => $employee->employment_status,
                        'formatted_salary' => $employee->formatted_salary,
                        'status' => $employee->status
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Delete employee (soft delete)
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can delete employees.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $employee = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->findOrFail($id);
            
            // Also deactivate the User account
            if ($employee->user_id) {
                $userAccount = User::find($employee->user_id);
                if ($userAccount) {
                    $userAccount->update(['status' => 'inactive']);
                }
            }
            
            // Soft delete employee
            $employee->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employee deleted successfully and user account deactivated'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Regularize probationary employee
    public function regularize($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can regularize employees.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $employee = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->findOrFail($id);
            
            if (!$employee->isProbationary()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee is not in probationary status'
                ], 400);
            }
            
            if (!$employee->canBeRegularized()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employee cannot be regularized yet. Probation period not completed.'
                ], 400);
            }
            
            if ($employee->regularize()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Employee regularized successfully',
                    'data' => [
                        'employee' => [
                            'id' => $employee->id,
                            'full_name' => $employee->full_name,
                            'employment_status' => $employee->employment_status,
                            'regularization_date' => $employee->regularization_date
                        ]
                    ]
                ]);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to regularize employee'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to regularize employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Get employee statistics
    public function statistics()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is an HR Manager
            if (!$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only HR Managers can view statistics.'
                ], 403);
            }
            
            // Get HR Manager details
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'HR Manager profile not found.'
                ], 404);
            }
            
            $totalEmployees = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)->count();
            $activeEmployees = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)->active()->count();
            $probationaryEmployees = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)->probationary()->count();
            
            // Department distribution
            $departmentStats = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->get()
                ->map(function ($item) {
                    return [
                        'department' => $item->department,
                        'count' => $item->count
                    ];
                });
            
            // Employment type distribution
            $employmentTypeStats = Employee::where('parent_distributor_id', $hrManager->parent_distributor_id)
                ->select('employment_type', DB::raw('count(*) as count'))
                ->groupBy('employment_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'employment_type' => $item->employment_type,
                        'count' => $item->count
                    ];
                });
            
            // Get company name
            $distributorRequirement = DistributorRequirements::where('user_id', $hrManager->parent_distributor_id)->first();
            $companyName = $distributorRequirement ? $distributorRequirement->company_name : 'Unknown Company';
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'company_name' => $companyName,
                    'statistics' => [
                        'total_employees' => $totalEmployees,
                        'active_employees' => $activeEmployees,
                        'probationary_employees' => $probationaryEmployees,
                        'department_distribution' => $departmentStats,
                        'employment_type_distribution' => $employmentTypeStats
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}