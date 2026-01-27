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
            
            // Transform data
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
                    'job_title' => $employee->job_title,
                    'employment_type' => $employee->employment_type,
                    'employment_status' => $employee->employment_status,
                    'hire_date' => $employee->hire_date->format('Y-m-d'),
                    'salary' => $employee->formatted_salary,
                    'status' => $employee->status,
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
                ->with(['createdBy', 'hrManager', 'parentDistributor'])
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
                'job_title' => $employee->job_title,
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
            
            // Validate request
            $validator = Validator::make($request->all(), [
                // Personal Information
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:hr_employees,email',
                'phone' => 'required|string|max:20',
                'emergency_contact' => 'nullable|string|max:20',
                'address' => 'required|string|max:500',
                'date_of_birth' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'marital_status' => 'required|in:single,married,widowed,separated,divorced',
                'nationality' => 'required|string|max:100',
                
                // Employment Details
                'department' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
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
                'bank_account_number' => 'nullable|string|max:50',
                'bank_account_name' => 'nullable|string|max:255',
                
                // Government Numbers
                'sss_number' => 'nullable|string|max:20',
                'philhealth_number' => 'nullable|string|max:20',
                'pagibig_number' => 'nullable|string|max:20',
                'tin_number' => 'nullable|string|max:20',
                
                // Identification
                'valid_id_type' => 'required|string|max:100',
                'id_number' => 'required|string|max:50',
                
                // Educational Background
                'educational_attainment' => 'required|string|max:100',
                'school_graduated' => 'required|string|max:255',
                'year_graduated' => 'required|integer|min:1900|max:' . date('Y'),
                'course' => 'required|string|max:255',
                
                // File Uploads (optional during creation)
                'valid_id_photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
                'medical_certificate' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'nbi_clearance' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                'police_clearance' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
                
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Generate employee code
            $employeeCode = Employee::generateEmployeeCode($hrManager->parent_distributor_id);
            
            // Create employee data array
            $employeeData = $request->only([
                'first_name', 'middle_name', 'last_name', 'email', 'phone',
                'emergency_contact', 'address', 'date_of_birth', 'gender',
                'marital_status', 'nationality', 'department', 'position',
                'job_title', 'employment_type', 'employment_status', 'hire_date',
                'probation_end_date', 'regularization_date', 'salary',
                'salary_currency', 'payment_frequency', 'bank_name',
                'bank_account_number', 'bank_account_name', 'sss_number',
                'philhealth_number', 'pagibig_number', 'tin_number',
                'valid_id_type', 'id_number', 'educational_attainment',
                'school_graduated', 'year_graduated', 'course', 'notes'
            ]);
            
            // Add system fields
            $employeeData['employee_code'] = $employeeCode;
            $employeeData['parent_distributor_id'] = $hrManager->parent_distributor_id;
            $employeeData['hr_manager_id'] = $hrManager->id;
            $employeeData['created_by_user_id'] = $user->id;
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
                'created_by' => $user->id,
                'company' => $hrManager->parent_distributor_id,
                'timestamp' => now()
            ]);
            
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
                        'employment_status' => $employee->employment_status,
                        'hire_date' => $employee->hire_date->format('Y-m-d'),
                        'formatted_salary' => $employee->formatted_salary,
                        'company_name' => $companyName,
                        'created_by' => $user->full_name
                    ]
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Update employee
    public function update(Request $request, $id)
    {
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
                'job_title' => 'sometimes|string|max:255',
                'employment_status' => 'sometimes|in:probationary,regular,contractual,resigned,terminated,retired',
                'salary' => 'sometimes|numeric|min:0',
                'salary_currency' => 'sometimes|string|max:3',
                'status' => 'sometimes|in:active,inactive,on_leave',
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
                'department', 'position', 'job_title', 'employment_status',
                'salary', 'salary_currency', 'status', 'notes'
            ]);
            
            $employee->update($updateData);
            
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
            
            // Soft delete
            $employee->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Employee deleted successfully'
            ]);
            
        } catch (\Exception $e) {
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