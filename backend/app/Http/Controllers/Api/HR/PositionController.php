<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Position;
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
class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request)
    {
        try {
            Log::info('Fetching positions', ['params' => $request->all(), 'user_id' => Auth::id()]);
            
            $user = Auth::user();
            $query = Position::with([
                'distributor',
                'distributorRequirements',
                'creator',
                'updater',
                'accessibilitySettings' // Load accessibility settings
            ]);

            // If user is HR Manager, only show positions for their distributor
            if ($user->role === 'hr_manager') {
                // Get the HR Manager's parent distributor
                $hrManager = HRManager::where('user_id', $user->id)->first();
                if ($hrManager && $hrManager->parent_distributor_id) {
                    $query->where('distributor_id', $hrManager->parent_distributor_id);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'HR Manager not associated with any distributor.',
                        'data' => [],
                        'departments' => [],
                        'statistics' => $this->getEmptyStatistics()
                    ]);
                }
            }
            // If user is Distributor, only show their positions
            elseif ($user->role === 'distributor') {
                $query->where('distributor_id', $user->id);
            }
            // Admin can see all positions
            elseif ($user->role === 'admin') {
                // Admin sees all positions
            }

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'active'); // Default to active positions
            }

            // Filter by department
            if ($request->has('department') && $request->department !== 'all') {
                $query->where('department', $request->department);
            }

            // Filter by distributor
            if ($request->has('distributor_id') && $request->distributor_id !== 'all') {
                $query->where('distributor_id', $request->distributor_id);
            }

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%")
                      ->orWhereHas('distributor', function($q2) use ($search) {
                          $q2->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $positions = $query->paginate($perPage);

            // Get distributors list for filters (admin only)
            $distributors = [];
            if ($user->role === 'admin') {
                $distributors = User::where('role', 'distributor')
                    ->where('status', 'active')
                    ->select('id', 'first_name', 'last_name', 'email')
                    ->get()
                    ->map(function($distributor) {
                        $requirements = DistributorRequirements::where('user_id', $distributor->id)->first();
                        return [
                            'id' => $distributor->id,
                            'name' => $distributor->full_name,
                            'email' => $distributor->email,
                            'company_name' => $requirements ? $requirements->company_name : 'Individual'
                        ];
                    });
            }

            Log::info('Positions fetched successfully', ['count' => $positions->count()]);

            return response()->json([
                'success' => true,
                'data' => $positions,
                'departments' => $this->getDepartments($user),
                'distributors' => $distributors,
                'statistics' => $this->getStatistics($user)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch positions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch positions.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => [],
                'departments' => [],
                'statistics' => $this->getEmptyStatistics()
            ], 500);
        }
    }

    // Get employee accessibility by employee ID (for sidebar)
public function getEmployeeSidebarAccessibility($id)
{
    try {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Only allow employee to access their own accessibility or HR Manager
        if ($user->id != $id && !$user->isHRManager()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to access this information.'
            ], 403);
        }
        
        $employee = Employee::where('user_id', $id)->first();
        
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }
        
        // Find position by employee's position name and distributor
        $position = Position::where('distributor_id', $employee->parent_distributor_id)
            ->where('title', $employee->position)
            ->where('status', 'active')
            ->with('accessibilitySettings')
            ->first();
        
        $accessibility = [];
        
        if ($position) {
            // Get accessibility from position_accessibilities table
            if ($position->accessibilitySettings && $position->accessibilitySettings->count() > 0) {
                $accessibility = $position->accessibilitySettings
                    ->where('is_granted', true)
                    ->pluck('permission_key')
                    ->toArray();
            } 
            // Fallback to requirements JSON field
            elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                $accessibility = $position->requirements['accessibility'];
            }
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'position' => $employee->position,
                'department' => $employee->department,
                'accessibility_keys' => $accessibility,
                'has_full_access' => $user->isHRManager(),
                'position_found' => $position ? true : false
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch employee accessibility',
            'error' => $e->getMessage()
        ], 500);
    }
}

    

    /**
     * Get employee accessibility by position ID.
     */
     public function getEmployeeAccessibility($id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Check if user is the employee themselves or HR Manager
            $employee = Employee::findOrFail($id);
            
            if ($user->id !== $employee->user_id && !$user->isHRManager()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to access this information.'
                ], 403);
            }
            
            // Find position by employee's position name and distributor
            $position = Position::where('distributor_id', $employee->parent_distributor_id)
                ->where('title', $employee->position)
                ->where('status', 'active')
                ->with('accessibilitySettings')
                ->first();
            
            $accessibility = [];
            $accessibleModules = [];
            
            if ($position) {
                // Get accessibility from position_accessibilities table
                if ($position->accessibilitySettings && $position->accessibilitySettings->count() > 0) {
                    $accessibility = $position->accessibilitySettings
                        ->where('is_granted', true)
                        ->pluck('permission_key')
                        ->toArray();
                } 
                // Fallback to requirements JSON field
                elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                    $accessibility = $position->requirements['accessibility'];
                }
                
                // Map to human-readable module names
                $moduleMap = [
                    'dashboard' => 'Dashboard',
                    'employee_list' => 'Employee List',
                    'positions_roles' => 'Positions & Roles',
                    'departments' => 'Departments',
                    'employment_status' => 'Employment Status',
                    'recruitment' => 'Recruitment Application',
                    'payroll_management' => 'Payroll Management',
                    'reports' => 'HR Reports',
                    'settings' => 'HR Settings'
                ];
                
                foreach ($accessibility as $key) {
                    if (isset($moduleMap[$key])) {
                        $accessibleModules[] = [
                            'key' => $key,
                            'name' => $moduleMap[$key]
                        ];
                    }
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'position' => $employee->position,
                    'department' => $employee->department,
                    'accessibility_keys' => $accessibility,
                    'accessible_modules' => $accessibleModules,
                    'has_full_access' => $user->isHRManager(),
                    'position_found' => $position ? true : false
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch employee accessibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get accessible modules based on permission keys.
     */
    private function getAccessibleModules($permissionKeys)
    {
        $moduleMap = [
            'dashboard' => 'Dashboard',
            'employee_list' => 'Employee List',
            'positions_roles' => 'Positions & Roles',
            'departments' => 'Departments',
            'employment_status' => 'Employment Status',
            'recruitment' => 'Recruitment Application',
            'payroll_management' => 'Payroll Management',
            'reports' => 'HR Reports',
            'settings' => 'HR Settings'
        ];

        $accessibleModules = [];
        foreach ($permissionKeys as $key) {
            if (isset($moduleMap[$key])) {
                $accessibleModules[] = [
                    'key' => $key,
                    'name' => $moduleMap[$key]
                ];
            }
        }

        return $accessibleModules;
    }

    /**
     * Get statistics for positions.
     */
    public function statistics()
    {
        try {
            Log::info('Fetching position statistics');
            
            $user = Auth::user();
            $data = $this->getStatistics($user);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => $this->getEmptyStatistics()
            ], 500);
        }
    }

    /**
     * Get all departments.
     */
    public function departments()
    {
        try {
            Log::info('Fetching departments');
            
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'data' => $this->getDepartments($user)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch departments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get all distributors (for admin).
     */
    public function distributors()
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $distributors = User::where('role', 'distributor')
                ->where('status', 'active')
                ->with('distributorRequirements')
                ->select('id', 'first_name', 'last_name', 'email')
                ->get()
                ->map(function($distributor) {
                    $companyName = $distributor->distributorRequirements ? 
                        $distributor->distributorRequirements->company_name : 
                        $distributor->full_name . ' (Individual)';
                    
                    return [
                        'id' => $distributor->id,
                        'name' => $distributor->full_name,
                        'email' => $distributor->email,
                        'company_name' => $companyName
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $distributors
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch distributors', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch distributors.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request)
    {
        Log::info('Creating new position', ['data' => $request->all(), 'user_id' => Auth::id()]);
        
        $user = Auth::user();
        $distributorId = null;

        // Determine distributor_id based on user role
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if (!$hrManager || !$hrManager->parent_distributor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'HR Manager not associated with any distributor.'
                ], 400);
            }
            $distributorId = $hrManager->parent_distributor_id;
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        } elseif ($user->role === 'admin') {
            // Admin can specify distributor_id
            if (!$request->has('distributor_id') || empty($request->distributor_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Distributor ID is required for admin users.'
                ], 422);
            }
            $distributorId = $request->distributor_id;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create positions.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'description' => 'required|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed', ['errors' => $validator->errors()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $positionData = [
                'distributor_id' => $distributorId,
                'title' => $request->title,
                'department' => $request->department,
                'description' => $request->description,
                'min_salary' => $request->min_salary,
                'max_salary' => $request->max_salary,
                'status' => $request->status ?? 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id
            ];

            // Add requirements if provided (including accessibility for HR)
            if ($request->has('requirements') && is_array($request->requirements)) {
                $positionData['requirements'] = $request->requirements;
            }

            $position = Position::create($positionData);

            // Handle accessibility for HR department - only store selected items
            if ($position->department === 'Human Resources') {
                $accessibilityData = $request->has('accessibility') 
                    ? $request->accessibility 
                    : ($request->has('requirements.accessibility') 
                        ? $request->requirements['accessibility'] 
                        : []);
                
                if (!empty($accessibilityData)) {
                    $position->updateAccessibility($accessibilityData);
                }
                // If no accessibility selected, don't store anything in position_accessibilities table
            }

            DB::commit();
            
            Log::info('Position created successfully', [
                'position_id' => $position->id,
                'distributor_id' => $distributorId,
                'has_accessibility' => $position->department === 'Human Resources' && !empty($accessibilityData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'data' => $position->load(['distributor', 'distributorRequirements', 'creator', 'updater', 'accessibilitySettings'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create position', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create position.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified position.
     */
    public function show($id)
    {
        try {
            Log::info('Fetching position', ['position_id' => $id, 'user_id' => Auth::id()]);
            
            $user = Auth::user();
            $position = Position::with([
                'distributor', 
                'distributorRequirements', 
                'creator', 
                'updater',
                'accessibilitySettings'
            ])->find($id);

            if (!$position) {
                Log::warning('Position not found', ['position_id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Position not found.'
                ], 404);
            }

            // Check authorization
            if (!$this->canAccessPosition($user, $position)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access this position.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $position
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch position', [
                'position_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch position.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified position.
     */
    public function update(Request $request, $id)
    {
        Log::info('Updating position', ['position_id' => $id, 'data' => $request->all(), 'user_id' => Auth::id()]);
        
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for position update', [
                'position_id' => $id,
                'errors' => $validator->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $position = Position::find($id);
            
            if (!$position) {
                Log::warning('Position not found for update', ['position_id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Position not found.'
                ], 404);
            }

            // Check authorization
            if (!$this->canAccessPosition($user, $position)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this position.'
                ], 403);
            }

            $updateData = array_merge(
                $request->only(['title', 'department', 'description', 'min_salary', 'max_salary', 'status']),
                ['updated_by' => $user->id]
            );

            // Handle requirements (including accessibility for HR)
            $requirements = $position->requirements ?? [];
            
            if ($request->has('requirements') && is_array($request->requirements)) {
                $requirements = array_merge($requirements, $request->requirements);
            }

            // Handle accessibility specifically for HR department
            if ($request->has('accessibility')) {
                if ($position->department === 'Human Resources' || 
                    ($request->has('department') && $request->department === 'Human Resources')) {
                    
                    $requirements['accessibility'] = $request->accessibility;
                    $position->updateAccessibility($request->accessibility);
                }
            } elseif ($request->has('department') && $request->department !== 'Human Resources') {
                // If changing from HR to non-HR department, remove accessibility
                unset($requirements['accessibility']);
                $position->accessibilitySettings()->delete();
            }

            $updateData['requirements'] = $requirements;

            $position->update($updateData);

            DB::commit();
            
            Log::info('Position updated successfully', [
                'position_id' => $id,
                'has_accessibility' => $position->department === 'Human Resources' && isset($requirements['accessibility'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'data' => $position->load(['distributor', 'distributorRequirements', 'creator', 'updater', 'accessibilitySettings'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update position', [
                'position_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified position.
     */
    public function destroy($id)
    {
        Log::info('Deleting position', ['position_id' => $id, 'user_id' => Auth::id()]);
        
        try {
            $user = Auth::user();
            $position = Position::find($id);
            
            if (!$position) {
                Log::warning('Position not found for deletion', ['position_id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Position not found.'
                ], 404);
            }

            // Check authorization
            if (!$this->canAccessPosition($user, $position)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this position.'
                ], 403);
            }

            $position->delete();
            
            Log::info('Position deleted successfully', ['position_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Position deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete position', [
                'position_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check if user can access position.
     */
    private function canAccessPosition($user, $position): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'distributor') {
            return $position->distributor_id === $user->id;
        }
        
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            return $hrManager && $position->distributor_id === $hrManager->parent_distributor_id;
        }
        
        return false;
    }

    /**
     * Get departments list.
     */
    private function getDepartments($user)
    {
        try {
            $query = Position::where('status', 'active');

            // Filter by user access
            if ($user->role === 'hr_manager') {
                $hrManager = HRManager::where('user_id', $user->id)->first();
                if ($hrManager && $hrManager->parent_distributor_id) {
                    $query->where('distributor_id', $hrManager->parent_distributor_id);
                } else {
                    return [];
                }
            } elseif ($user->role === 'distributor') {
                $query->where('distributor_id', $user->id);
            }

            $departments = $query
                ->select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->orderBy('department')
                ->get()
                ->map(function($item) {
                    return [
                        'name' => $item->department,
                        'count' => $item->count
                    ];
                })
                ->toArray();

            return $departments;
        } catch (\Exception $e) {
            Log::error('Failed to get departments in helper', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get statistics.
     */
    private function getStatistics($user)
    {
        try {
            $query = Position::query();
            $distributorQuery = Position::where('status', 'active');

            // Filter by user access
            if ($user->role === 'hr_manager') {
                $hrManager = HRManager::where('user_id', $user->id)->first();
                if ($hrManager && $hrManager->parent_distributor_id) {
                    $distributorId = $hrManager->parent_distributor_id;
                    $query->where('distributor_id', $distributorId);
                    $distributorQuery->where('distributor_id', $distributorId);
                } else {
                    return $this->getEmptyStatistics();
                }
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
                $query->where('distributor_id', $distributorId);
                $distributorQuery->where('distributor_id', $distributorId);
            }

            $totalPositions = $query->count();
            $activePositions = $query->where('status', 'active')->count();
            $departmentsCount = $distributorQuery->distinct('department')->count('department');
            $hrPositionsCount = $distributorQuery->where('department', 'Human Resources')->count();

            $positionsByDepartment = $distributorQuery
                ->select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->orderBy('count', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->department,
                        'count' => $item->count
                    ];
                })
                ->toArray();

            // Get company info for HR Manager or Distributor
            $companyInfo = [];
            if (($user->role === 'hr_manager' || $user->role === 'distributor') && isset($distributorId)) {
                $distributor = User::find($distributorId);
                if ($distributor) {
                    $requirements = DistributorRequirements::where('user_id', $distributorId)->first();
                    $companyInfo = [
                        'distributor_id' => $distributorId,
                        'distributor_name' => $distributor->full_name,
                        'company_name' => $requirements ? $requirements->company_name : $distributor->full_name . ' (Individual)',
                        'email' => $distributor->email,
                        'phone' => $distributor->phone,
                        'address' => $distributor->address
                    ];
                }
            }

            return [
                'total_positions' => $totalPositions,
                'active_positions' => $activePositions,
                'inactive_positions' => $totalPositions - $activePositions,
                'departments_count' => $departmentsCount,
                'hr_positions_count' => $hrPositionsCount,
                'positions_by_department' => $positionsByDepartment,
                'company_info' => $companyInfo
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get statistics in helper', [
                'error' => $e->getMessage()
            ]);
            
            return $this->getEmptyStatistics();
        }
    }

    /**
     * Get empty statistics.
     */
    private function getEmptyStatistics()
    {
        return [
            'total_positions' => 0,
            'active_positions' => 0,
            'inactive_positions' => 0,
            'departments_count' => 0,
            'hr_positions_count' => 0,
            'positions_by_department' => [],
            'company_info' => []
        ];
    }
}