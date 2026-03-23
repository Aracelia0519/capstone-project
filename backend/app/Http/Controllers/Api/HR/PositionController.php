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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    private function checkAccess($user, $action = 'can_view')
    {
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
            ];
        }

        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager && $hrManager->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]
                ];
            }
        } 
        
        elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'positions_roles') 
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
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
            'distributor_id' => null,
            'permissions' => ['can_view' => false, 'can_manage' => false, 'can_approve' => false]
        ];
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to access positions.',
                    'data' => [],
                    'departments' => [],
                    'statistics' => $this->getEmptyStatistics()
                ], 403);
            }

            $query = Position::with([
                'distributor',
                'distributorRequirements',
                'creator',
                'updater',
                'accessibilitySettings' 
            ]);

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $accessData['distributor_id']);
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'active');
            }

            if ($request->has('department') && $request->department !== 'all') {
                $query->where('department', $request->department);
            }

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

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $positions = $query->paginate($perPage);

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

            return response()->json([
                'success' => true,
                'data' => $positions,
                'departments' => $this->getDepartments($user, $accessData),
                'distributors' => $distributors,
                'statistics' => $this->getStatistics($user, $accessData),
                'permissions' => $accessData['permissions']
            ]);

        } catch (\Exception $e) {
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

    public function getEmployeeSidebarAccessibility($id)
    {
        try {
            $user = Auth::user();
            $employee = Employee::where('user_id', $id)->first();
            
            if (!$employee) return response()->json(['status' => 'error', 'message' => 'Not found.'], 404);
            
            $canView = false;
            // FIXED: Replaced $user->isHRManager() with $user->role === 'hr_manager'
            if ($user->id == $id || $user->role === 'hr_manager' || $user->role === 'admin' || $user->role === 'distributor') {
                $canView = true;
            } elseif ($user->role === 'employee') {
                $accessData = $this->checkAccess($user, 'can_view');
                if ($accessData['has_access'] && $accessData['distributor_id'] === $employee->parent_distributor_id) {
                    $canView = true;
                }
            }

            if (!$canView) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
            
            $position = Position::where('distributor_id', $employee->parent_distributor_id)
                ->where('title', $employee->position)
                ->where('status', 'active')
                ->with('accessibilitySettings')
                ->first();
            
            $accessibilityKeys = [];
            $accessibilityDetails = [];
            
            if ($position) {
                if ($position->accessibilitySettings && $position->accessibilitySettings->count() > 0) {
                    $settings = $position->accessibilitySettings->where('is_granted', true);
                    $accessibilityKeys = $settings->pluck('permission_key')->toArray();
                    $accessibilityDetails = $settings->mapWithKeys(function ($item) {
                        return [$item->permission_key => [
                            'view' => $item->can_view,
                            'manage' => $item->can_manage,
                            'approve' => $item->can_approve,
                        ]];
                    })->toArray();
                } 
                elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                    $accessibilityKeys = $position->requirements['accessibility'];
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee_id' => $employee->id,
                    'accessibility_keys' => $accessibilityKeys,
                    'accessibility_details' => $accessibilityDetails,
                    // FIXED: Replaced $user->isHRManager() with $user->role === 'hr_manager'
                    'has_full_access' => ($user->role === 'hr_manager') 
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function getEmployeeAccessibility($id)
    {
        try {
            $user = Auth::user();
            $employee = Employee::findOrFail($id);
            
            $canView = false;
            // FIXED: Replaced $user->isHRManager() with $user->role === 'hr_manager'
            if ($user->id == $id || $user->role === 'hr_manager' || $user->role === 'admin' || $user->role === 'distributor') {
                $canView = true;
            } elseif ($user->role === 'employee') {
                $accessData = $this->checkAccess($user, 'can_view');
                if ($accessData['has_access'] && $accessData['distributor_id'] === $employee->parent_distributor_id) {
                    $canView = true;
                }
            }
            
            if (!$canView) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }
            
            $position = Position::where('distributor_id', $employee->parent_distributor_id)
                ->where('title', $employee->position)
                ->where('status', 'active')
                ->with('accessibilitySettings')
                ->first();
            
            $accessibilityKeys = [];
            $accessibilityDetails = [];
            $accessibleModules = [];
            
            if ($position) {
                if ($position->accessibilitySettings && $position->accessibilitySettings->count() > 0) {
                    $settings = $position->accessibilitySettings->where('is_granted', true);
                    $accessibilityKeys = $settings->pluck('permission_key')->toArray();

                    $accessibilityDetails = $settings->mapWithKeys(function ($item) {
                        return [$item->permission_key => [
                            'view' => $item->can_view,
                            'manage' => $item->can_manage,
                            'approve' => $item->can_approve,
                        ]];
                    })->toArray();
                } 
                elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                    $accessibilityKeys = $position->requirements['accessibility'];
                }
                
                $moduleMap = [];
                $options = \App\Models\HR\PositionAccessibility::getPermissionOptions();
                foreach($options as $option) { $moduleMap[$option['key']] = $option['label']; }

                foreach ($accessibilityKeys as $key) {
                    if (isset($moduleMap[$key])) {
                        $accessibleModules[] = [
                            'key' => $key,
                            'name' => $moduleMap[$key],
                            'permissions' => $accessibilityDetails[$key] ?? ['view'=>true,'manage'=>true,'approve'=>true]
                        ];
                    }
                }
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'accessibility_keys' => $accessibilityKeys,
                    'accessibility_details' => $accessibilityDetails,
                    'accessible_modules' => $accessibleModules,
                    // FIXED: Replaced $user->isHRManager() with $user->role === 'hr_manager'
                    'has_full_access' => ($user->role === 'hr_manager')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error'], 500);
        }
    }

    public function statistics() {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');
        
        if (!$accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        return response()->json(['success' => true, 'data' => $this->getStatistics($user, $accessData)]);
    }

    public function departments() {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');
        
        if (!$accessData['has_access']) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        return response()->json(['success' => true, 'data' => $this->getDepartments($user, $accessData)]);
    }

    public function distributors() {
        $user = Auth::user();
        if ($user->role !== 'admin') return response()->json(['success' => false], 403);
        $distributors = User::where('role', 'distributor')->where('status', 'active')->get();
        return response()->json(['success' => true, 'data' => $distributors]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $accessData = $this->checkAccess($user, 'can_manage'); // Replaced can_create
        if (!$accessData['has_access']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to create positions.'
            ], 403);
        }

        $distributorId = null;

        if ($user->role === 'admin') {
            $distributorId = $request->distributor_id;
        } else {
            $distributorId = $accessData['distributor_id'];
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'description' => 'required|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

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

            if ($request->has('requirements') && is_array($request->requirements)) {
                $positionData['requirements'] = $request->requirements;
            }

            $position = Position::create($positionData);

            if (in_array($position->department, Position::RBAC_DEPARTMENTS)) {
                $accessibilityData = $request->has('accessibility') 
                    ? $request->accessibility 
                    : ($request->has('requirements.accessibility') ? $request->requirements['accessibility'] : []);
                
                if (!empty($accessibilityData)) {
                    $position->updateAccessibility($accessibilityData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Position created successfully.',
                'data' => $position->load(['distributor', 'distributorRequirements', 'creator', 'updater', 'accessibilitySettings'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $position = Position::with(['distributor', 'accessibilitySettings'])->find($id);

        if (!$position) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        if (!$this->canAccessPosition($user, $position, 'can_view')) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        return response()->json(['success' => true, 'data' => $position]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        try {
            DB::beginTransaction();

            $position = Position::find($id);
            if (!$position) return response()->json(['success' => false, 'message' => 'Not found'], 404);
            if (!$this->canAccessPosition($user, $position, 'can_manage')) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

            $updateData = array_merge(
                $request->only(['title', 'department', 'description', 'min_salary', 'max_salary', 'status']),
                ['updated_by' => $user->id]
            );

            $requirements = $position->requirements ?? [];
            if ($request->has('requirements') && is_array($request->requirements)) {
                $requirements = array_merge($requirements, $request->requirements);
            }

            $accessibilityData = null;
            if ($request->has('accessibility')) {
                $accessibilityData = $request->accessibility;
            } elseif ($request->has('requirements.accessibility')) {
                $accessibilityData = $request->input('requirements.accessibility');
            }

            if ($accessibilityData !== null) {
                if (in_array($position->department, Position::RBAC_DEPARTMENTS) || 
                    ($request->has('department') && in_array($request->department, Position::RBAC_DEPARTMENTS))) {
                    $position->updateAccessibility($accessibilityData);
                }
            } elseif ($request->has('department') && !in_array($request->department, Position::RBAC_DEPARTMENTS)) {
                unset($requirements['accessibility']);
                $position->accessibilitySettings()->delete();
            }

            $updateData['requirements'] = $requirements;
            $position->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.',
                'data' => $position->load(['distributor', 'distributorRequirements', 'creator', 'updater', 'accessibilitySettings'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $position = Position::find($id);
        
        if (!$position) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        if (!$this->canAccessPosition($user, $position, 'can_manage')) return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);

        $position->delete();
        return response()->json(['success' => true, 'message' => 'Position deleted successfully.']);
    }

    private function canAccessPosition($user, $position, $action = 'can_view'): bool
    {
        $accessData = $this->checkAccess($user, $action);
        
        if (!$accessData['has_access']) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }
        
        return $position->distributor_id === $accessData['distributor_id'];
    }

    private function getDepartments($user, $accessData = null)
    {
        if (!$accessData) $accessData = $this->checkAccess($user, 'can_view');
        if (!$accessData['has_access']) return [];

        $query = Position::where('status', 'active');
        if ($user->role !== 'admin') {
            $query->where('distributor_id', $accessData['distributor_id']);
        }

        return $query->select('department', DB::raw('count(*) as count'))
            ->groupBy('department')->orderBy('department')->get()
            ->map(function($item) { return ['name' => $item->department, 'count' => $item->count]; })->toArray();
    }

    private function getStatistics($user, $accessData = null)
    {
        if (!$accessData) $accessData = $this->checkAccess($user, 'can_view');
        if (!$accessData['has_access']) return $this->getEmptyStatistics();

        $query = Position::query();
        $distributorQuery = Position::where('status', 'active');

        if ($user->role !== 'admin') {
            $distributorId = $accessData['distributor_id'];
            $query->where('distributor_id', $distributorId);
            $distributorQuery->where('distributor_id', $distributorId);
        }

        $totalPositions = $query->count();
        $activePositions = $query->where('status', 'active')->count();

        return [
            'total_positions' => $totalPositions,
            'active_positions' => $activePositions,
            'departments_count' => $distributorQuery->distinct('department')->count('department'),
        ];
    }

    private function getEmptyStatistics() {
        return ['total_positions' => 0, 'active_positions' => 0, 'departments_count' => 0];
    }
}