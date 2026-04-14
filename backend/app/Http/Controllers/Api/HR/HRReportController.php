<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HRReportController extends Controller
{
    /**
     * Check RBAC Permissions for HR Reports Module (Level-Based)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        if ($user->role === 'admin') {
            return ['has_access' => true, 'distributor_id' => null, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        }
        if ($user->role === 'distributor') {
            return ['has_access' => true, 'distributor_id' => $user->id, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        }

        $distributorId = $this->getDistributorId($user);
        if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
            return ['has_access' => true, 'distributor_id' => $distributorId, 'permissions' => ['can_view' => true, 'can_manage' => true, 'can_approve' => true]];
        } 
        elseif ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')->where('distributor_id', $employee->parent_distributor_id)->where('title', $employee->position)->first();
                if ($position) {
                    $access = DB::table('position_accessibilities')->where('position_id', $position->id)->where('permission_key', 'hr_reports')->first();
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_manage' && $access->can_manage) $hasAccess = true;
                        if ($action === 'can_approve' && $access->can_approve) $hasAccess = true;
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => ['can_view' => (bool)$access->can_view, 'can_manage' => (bool)$access->can_manage, 'can_approve' => (bool)$access->can_approve,]
                            ];
                        }
                    }
                }
            }
        }
        return ['has_access' => false, 'distributor_id' => null, 'permissions' => ['can_view' => false, 'can_manage' => false, 'can_approve' => false]];
    }

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    public function getReportData(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to HR reports.'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            // Handle Parameters
            $reportType = $request->input('reportType', 'employees');
            $employeeId = $request->input('employeeId', 'all'); 
            $startDate = Carbon::parse($request->input('startDate', Carbon::now()->startOfMonth()))->startOfDay();
            $endDate = Carbon::parse($request->input('endDate', Carbon::now()->endOfMonth()))->endOfDay();

            // Fetch Employee List for Dropdowns
            $employeeList = DB::table('hr_employees')
                ->join('users', 'hr_employees.user_id', '=', 'users.id')
                ->where('hr_employees.parent_distributor_id', $distributorId)
                ->select('hr_employees.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"))
                ->orderBy('users.first_name')
                ->get();

            // ==========================================
            // 1. KEY SUMMARY METRICS (ALWAYS RETURNED)
            // ==========================================
            $totalEmployees = DB::table('hr_employees')->where('parent_distributor_id', $distributorId)->count();
            $totalPositions = DB::table('positions')->where('distributor_id', $distributorId)->count();
            $totalAttendances = DB::table('employee_attendances')
                ->where('distributor_id', $distributorId)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            $totalLeaves = DB::table('leave_requests')
                ->where('distributor_id', $distributorId)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count();

            // ==========================================
            // 2. FETCH SPECIFIC OR ALL REPORT DATA
            // ==========================================
            $resEmployees = [];
            $resRbac = [];
            $resAttendance = [];
            $resAttendanceReq = [];
            $resLeave = [];

            if (in_array($reportType, ['employees', 'all'])) {
                $q = DB::table('hr_employees')
                    ->join('users', 'hr_employees.user_id', '=', 'users.id')
                    ->where('hr_employees.parent_distributor_id', $distributorId);
                
                if ($employeeId !== 'all') {
                    $q->where('hr_employees.id', $employeeId);
                }

                $resEmployees = $q->select(
                        'hr_employees.id as emp_id',
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                        'users.email',
                        'hr_employees.position',
                        'hr_employees.department',
                        'hr_employees.status',
                        'hr_employees.created_at as joined_date'
                    )
                    ->orderBy('users.first_name')
                    ->get();
            } 
            
            if (in_array($reportType, ['rbac', 'all'])) {
                $resRbac = DB::table('positions')
                    ->leftJoin('position_accessibilities', 'positions.id', '=', 'position_accessibilities.position_id')
                    ->where('positions.distributor_id', $distributorId)
                    ->select(
                        'positions.title as position',
                        'positions.department',
                        'position_accessibilities.permission_key',
                        'position_accessibilities.can_view',
                        'position_accessibilities.can_manage',
                        'position_accessibilities.can_approve'
                    )
                    ->orderBy('positions.department')
                    ->orderBy('positions.title')
                    ->get();
            } 
            
            if (in_array($reportType, ['attendance', 'all'])) {
                $q = DB::table('employee_attendances')
                    ->join('hr_employees', 'employee_attendances.employee_id', '=', 'hr_employees.id')
                    ->join('users', 'hr_employees.user_id', '=', 'users.id')
                    ->where('employee_attendances.distributor_id', $distributorId)
                    ->whereBetween('employee_attendances.date', [$startDate, $endDate]);

                if ($employeeId !== 'all') {
                    $q->where('employee_attendances.employee_id', $employeeId);
                }

                $resAttendance = $q->select(
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                        'employee_attendances.date',
                        'employee_attendances.time_in',
                        'employee_attendances.time_out',
                        'employee_attendances.status'
                    )
                    ->orderByDesc('employee_attendances.date')
                    ->get();

                // Calculate hours worked dynamically in PHP 
                foreach ($resAttendance as $record) {
                    if ($record->time_in && $record->time_out) {
                        try {
                            $timeIn = Carbon::parse($record->time_in);
                            $timeOut = Carbon::parse($record->time_out);
                            $record->total_hours = round($timeIn->diffInMinutes($timeOut) / 60, 2);
                        } catch (\Exception $e) {
                            $record->total_hours = 0;
                        }
                    } else {
                        $record->total_hours = 0;
                    }
                }
            } 
            
            if (in_array($reportType, ['attendance_requests', 'all'])) {
                $q = DB::table('attendance_requests')
                    ->join('hr_employees', 'attendance_requests.employee_id', '=', 'hr_employees.id')
                    ->join('users', 'hr_employees.user_id', '=', 'users.id')
                    ->where('attendance_requests.distributor_id', $distributorId)
                    ->whereBetween('attendance_requests.requested_time', [$startDate, $endDate]);

                if ($employeeId !== 'all') {
                    $q->where('attendance_requests.employee_id', $employeeId);
                }

                $resAttendanceReq = $q->select(
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                        'attendance_requests.type',
                        'attendance_requests.requested_time',
                        'attendance_requests.reason',
                        'attendance_requests.status'
                    )
                    ->orderByDesc('attendance_requests.requested_time')
                    ->get();
            } 
            
            if (in_array($reportType, ['leave_requests', 'all'])) {
                $q = DB::table('leave_requests')
                    ->join('hr_employees', 'leave_requests.employee_id', '=', 'hr_employees.id')
                    ->join('users', 'hr_employees.user_id', '=', 'users.id')
                    ->where('leave_requests.distributor_id', $distributorId)
                    ->whereBetween('leave_requests.start_date', [$startDate, $endDate]);

                if ($employeeId !== 'all') {
                    $q->where('leave_requests.employee_id', $employeeId);
                }

                $resLeave = $q->select(
                        DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                        'leave_requests.type',
                        'leave_requests.start_date',
                        'leave_requests.end_date',
                        'leave_requests.reason',
                        'leave_requests.status'
                    )
                    ->orderByDesc('leave_requests.created_at')
                    ->get();
            }

            // FIX: Rock-solid routing using a standard switch statement instead of dynamic variables
            if ($reportType === 'all') {
                $tableData = [
                    'employees' => $resEmployees,
                    'rbac' => $resRbac,
                    'attendance' => $resAttendance,
                    'attendance_requests' => $resAttendanceReq,
                    'leave_requests' => $resLeave
                ];
            } else {
                switch($reportType) {
                    case 'employees': 
                        $tableData = $resEmployees; 
                        break;
                    case 'rbac': 
                        $tableData = $resRbac; 
                        break;
                    case 'attendance': 
                        $tableData = $resAttendance; 
                        break;
                    case 'attendance_requests': 
                        $tableData = $resAttendanceReq; 
                        break;
                    case 'leave_requests': 
                        $tableData = $resLeave; 
                        break;
                    default: 
                        $tableData = [];
                }
            }

            return response()->json([
                'success' => true,
                'metrics' => [
                    'totalEmployees' => $totalEmployees,
                    'totalPositions' => $totalPositions,
                    'totalAttendances' => $totalAttendances,
                    'totalLeaves' => $totalLeaves,
                ],
                'employeeList' => $employeeList, 
                'reportType' => $reportType,
                'tableData' => $tableData,
                'permissions' => $accessData['permissions']
            ]);

        } catch (\Exception $e) {
            Log::error('HR Report Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to generate HR report.', 'error' => $e->getMessage()], 500);
        }
    }
}