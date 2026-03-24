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
use Illuminate\Support\Facades\Mail;

class EmployeeController extends Controller
{
    /**
     * Check RBAC Permissions for HR Modules (Level-Based)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager) {
                return [
                    'has_access' => true,
                    'parent_distributor_id' => $hrManager->parent_distributor_id,
                    'hr_manager_id' => $hrManager->id,
                    'permissions' => [
                        'can_view' => true,
                        'can_manage' => true,
                        'can_approve' => true,
                    ]
                ];
            }
        } elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                // Check position accessibilities
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'employee_list') // Permission key for this module
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'parent_distributor_id' => $employee->parent_distributor_id,
                                'hr_manager_id' => $employee->hr_manager_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_manage' => (bool)$access->can_manage,
                                    'can_approve' => (bool)$access->can_approve,
                                ]
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'has_access' => false,
            'permissions' => [
                'can_view' => false,
                'can_manage' => false,
                'can_approve' => false,
            ]
        ];
    }

    /**
     * Helper to generate absolute file URL from a storage path.
     * Uses url(Storage::url()) so it always resolves to the full APP_URL-based URL.
     */
    private function fileUrl(?string $path): ?string
    {
        if (!$path) return null;
        return url(Storage::url($path));
    }

    // Get all employees for the current distributor (HR Manager & Permitted Employees)
    public function index(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to access employees.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search', '');
            $department = $request->input('department', '');
            $employmentStatus = $request->input('employment_status', '');
            $status = $request->input('status', 'active');
            
            $query = Employee::where('parent_distributor_id', $parentDistributorId)
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
            $departments = Employee::where('parent_distributor_id', $parentDistributorId)
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
                    'permissions' => $accessData['permissions'],
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
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to view employee details.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $employee = Employee::where('parent_distributor_id', $parentDistributorId)
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

                // FIX: Use url(Storage::url()) to generate absolute URLs so the
                // Vue frontend (running on a different port in dev) can resolve them.
                'valid_id_photo_url'         => $this->fileUrl($employee->valid_id_photo),
                'resume_url'                 => $this->fileUrl($employee->resume),
                'employment_contract_url'    => $this->fileUrl($employee->employment_contract),
                'medical_certificate_url'    => $this->fileUrl($employee->medical_certificate),
                'nbi_clearance_url'          => $this->fileUrl($employee->nbi_clearance),
                'police_clearance_url'       => $this->fileUrl($employee->police_clearance),
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
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to create employees.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            $hrManagerId = $accessData['hr_manager_id']; 
            
            // Define custom error messages
            $messages = [
                'email.unique' => 'The email address is already in use by another user or employee.',
                'phone.required' => 'A contact number is required.',
                'phone.regex' => 'The phone number must be exactly 11 digits and contain only numbers.',
                'emergency_contact.regex' => 'The emergency contact must be exactly 11 digits and contain only numbers.',
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
                'bank_account_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'bank_account_name' => 'nullable|string|max:255',
                
                // Government Numbers
                'sss_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'philhealth_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'pagibig_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                'tin_number' => ['nullable', 'string', 'regex:/^[0-9]+$/'],
                
                // Identification
                'valid_id_type' => 'required|string|max:100',
                'id_number' => ['required', 'string', 'regex:/^[0-9]+$/'],
                
                // Educational Background
                'educational_attainment' => 'required|string|max:100',
                'school_graduated' => 'required|string|max:255',
                'year_graduated' => 'required|integer|min:1900|max:' . date('Y'),
                'course' => 'required|string|max:255',
                
                // File Uploads
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
            
            // GENERATE EMPLOYEE CODE
            $prefix = 'EMP-' . $parentDistributorId . '-' . date('Y') . '-';
            
            $lastEmployee = Employee::where('employee_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastEmployee) {
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
                'password' => $request->last_name, 
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => 'employee', 
                'status' => 'active' 
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
            
            $employeeData['password'] = Hash::make($request->last_name); 
            $employeeData['employee_code'] = $employeeCode;
            $employeeData['parent_distributor_id'] = $parentDistributorId;
            $employeeData['hr_manager_id'] = $hrManagerId;
            $employeeData['created_by_user_id'] = $user->id;
            $employeeData['user_id'] = $employeeUser->id;
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
            
            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'user_id' => $employeeUser->id,
                'created_by' => $user->id
            ]);
            
            DB::commit();
            
            // Get company name for response and email
            $distributorRequirement = DistributorRequirements::where('user_id', $parentDistributorId)->first();
            $companyName = $distributorRequirement ? $distributorRequirement->company_name : 'Unknown Company';

            // --- SEND WELCOME EMAIL ---
            try {
                $portalUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')) . '/login';
                
                $htmlContent = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
                        <h2 style='color: #2563eb;'>Welcome to {$companyName}!</h2>
                        <p>Hi {$request->first_name},</p>
                        <p>Your employee account has been successfully created. You can now log into the employee portal.</p>
                        
                        <div style='background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0;'>Your Login Credentials</h3>
                            <ul style='list-style-type: none; padding: 0;'>
                                <li><strong>Portal URL:</strong> <a href='{$portalUrl}' style='color: #2563eb;'>{$portalUrl}</a></li>
                                <li><strong>Email:</strong> {$request->email}</li>
                                <li><strong>Temporary Password:</strong> {$request->last_name}</li>
                            </ul>
                        </div>
                        
                        <p style='color: #dc2626; font-weight: bold;'>Security Notice: Please change your password immediately upon your first login.</p>
                        
                        <br>
                        <p>Best Regards,<br><strong>HR Department, {$companyName}</strong></p>
                    </div>
                ";

                Mail::html($htmlContent, function ($message) use ($request, $companyName) {
                    $message->to($request->email)
                            ->subject("Welcome to {$companyName} - Your Account Details");
                });

            } catch (\Exception $mailException) {
                Log::error('Failed to send welcome email: ' . $mailException->getMessage());
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employee created successfully',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'employee_code' => $employee->employee_code,
                        'full_name' => $employee->full_name,
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee creation failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Update employee details and optionally replace documents
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to update employees.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $employee = Employee::where('parent_distributor_id', $parentDistributorId)->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes', 'string', 'email', 'max:255',
                    Rule::unique('hr_employees', 'email')->ignore($employee->id),
                    Rule::unique('users', 'email')->ignore($employee->user_id),
                ],
                'phone' => ['sometimes', 'string', 'regex:/^[0-9]{11}$/'],
                'date_of_birth' => 'sometimes|date|before:today',
                'department' => 'sometimes|string|max:255',
                'position' => 'sometimes|string|max:255',
                'employment_status' => 'sometimes|in:probationary,regular,contractual,resigned,terminated,retired',
                'salary' => 'sometimes|numeric|min:0',
                'salary_currency' => 'sometimes|string|max:3',
                'status' => 'sometimes|in:active,inactive,on_leave',
                // Document overrides (nullable because they aren't required to be updated every time)
                'valid_id_photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
                'medical_certificate' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'nbi_clearance' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'police_clearance' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Define file fields for checking replacements
            $fileFields = [
                'valid_id_photo' => 'hr/employees/documents/valid_ids',
                'resume' => 'hr/employees/documents/resumes',
                'employment_contract' => 'hr/employees/documents/contracts',
                'medical_certificate' => 'hr/employees/documents/medical',
                'nbi_clearance' => 'hr/employees/documents/clearances',
                'police_clearance' => 'hr/employees/documents/clearances'
            ];
            
            $updateData = $request->except(array_merge(array_keys($fileFields), ['_method']));
            
            // Handle file replacements
            foreach ($fileFields as $field => $path) {
                if ($request->hasFile($field)) {
                    // Delete old file if exists
                    if ($employee->$field) {
                        Storage::disk('public')->delete($employee->$field);
                    }
                    
                    $file = $request->file($field);
                    $fileName = time() . '_' . $employee->employee_code . '_' . $field . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs($path, $fileName, 'public');
                    $updateData[$field] = $filePath;
                }
            }
            
            // Update User linked account
            if ($employee->user_id) {
                $userAccount = User::find($employee->user_id);
                if ($userAccount) {
                    $userData = [];
                    if ($request->has('first_name')) $userData['first_name'] = $request->first_name;
                    if ($request->has('last_name')) $userData['last_name'] = $request->last_name;
                    if ($request->has('email')) $userData['email'] = $request->email;
                    if ($request->has('phone')) $userData['phone'] = $request->phone;
                    if ($request->has('address')) $userData['address'] = $request->address;
                    if ($request->has('status')) $userData['status'] = $request->status === 'active' ? 'active' : 'inactive';
                    
                    if (!empty($userData)) {
                        $userAccount->update($userData);
                    }
                }
            }
            
            $employee->update($updateData);
            
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
                        'status' => $employee->status
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee update failed: ' . $e->getMessage());
            
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
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to delete employees.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $employee = Employee::where('parent_distributor_id', $parentDistributorId)
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
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_manage');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to regularize employees.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $employee = Employee::where('parent_distributor_id', $parentDistributorId)
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
            
            // RBAC Access Check
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You do not have permission to view statistics.'
                ], 403);
            }
            
            $parentDistributorId = $accessData['parent_distributor_id'];
            
            $totalEmployees = Employee::where('parent_distributor_id', $parentDistributorId)->count();
            $activeEmployees = Employee::where('parent_distributor_id', $parentDistributorId)->active()->count();
            $probationaryEmployees = Employee::where('parent_distributor_id', $parentDistributorId)->probationary()->count();
            
            // Department distribution
            $departmentStats = Employee::where('parent_distributor_id', $parentDistributorId)
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
            $employmentTypeStats = Employee::where('parent_distributor_id', $parentDistributorId)
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
            $distributorRequirement = DistributorRequirements::where('user_id', $parentDistributorId)->first();
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

    public function getEmployeeAccessibility($id)
    {
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'Employee not found'], 404);
            }
            
            $position = DB::table('positions')
                ->where('distributor_id', $employee->parent_distributor_id)
                ->where('title', $employee->position)
                ->first();
                
            if ($position) {
                $accessibilities = DB::table('position_accessibilities')
                    ->where('position_id', $position->id)
                    ->get();
                    
                return response()->json([
                    'status' => 'success',
                    'data' => $accessibilities
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load accessibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}