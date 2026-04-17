<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HRDashboardController extends Controller
{
    /**
     * Check RBAC Permissions for HR Dashboard (Level-Based)
     * Matches HRReportController exactly.
     */
    private function checkAccess($user, $action = 'can_view')
    {
        if ($user->role === 'admin') {
            return ['has_access' => true, 'distributor_id' => null];
        }
        if ($user->role === 'distributor') {
            return ['has_access' => true, 'distributor_id' => $user->id];
        }

        $distributorId = $this->getDistributorId($user);
        if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
            return ['has_access' => true, 'distributor_id' => $distributorId];
        } 
        elseif ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')->where('distributor_id', $employee->parent_distributor_id)->where('title', $employee->position)->first();
                if ($position) {
                    $access = DB::table('position_accessibilities')->where('position_id', $position->id)->where('permission_key', 'hr_dashboard')->first();
                    if ($access && $access->$action) {
                        return [
                            'has_access' => true,
                            'distributor_id' => $employee->parent_distributor_id,
                        ];
                    }
                }
            }
        }
        return ['has_access' => false, 'distributor_id' => null];
    }

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'hr_manager') return DB::table('hr_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'finance_manager') return DB::table('finance_managers')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id') ?? $user->id;
        return $user->id;
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to HR Dashboard'], 403);
            }

            $distributorId = $accessData['distributor_id'];
            $today = Carbon::today()->toDateString();
            $thirtyDaysAgo = Carbon::now()->subDays(30);

            // ==========================================
            // 1. KEY METRICS (From hr_employees)
            // ==========================================
            $baseQuery = DB::table('hr_employees');
            if ($distributorId) {
                $baseQuery->where('parent_distributor_id', $distributorId);
            }

            $totalEmployees = (clone $baseQuery)->count();
            
            // Using correct column 'status' from DB ('active', 'inactive', 'on_leave')
            $activeEmployees = (clone $baseQuery)->where('status', 'active')->count();
            $onLeave = (clone $baseQuery)->where('status', 'on_leave')->count();
            $newHires = (clone $baseQuery)->where('hire_date', '>=', $thirtyDaysAgo)->count();

            // ==========================================
            // 2. ATTENDANCE METRICS (From employee_attendances)
            // ==========================================
            $attQuery = DB::table('employee_attendances')->whereDate('date', $today);
            if ($distributorId) {
                $attQuery->where('distributor_id', $distributorId);
            }

            $present = (clone $attQuery)->whereIn('status', ['Present', 'Half-day'])->count();
            $late = (clone $attQuery)->where('status', 'Late')->count();
            $absent = (clone $attQuery)->where('status', 'Absent')->count();
            
            // On Leave today (From leave_requests)
            $leaveQuery = DB::table('leave_requests')
                ->where('status', 'Approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);
            if ($distributorId) {
                $leaveQuery->where('distributor_id', $distributorId);
            }
            $onLeaveToday = $leaveQuery->count();

            $attendanceData = [
                ['name' => 'Present', 'value' => $present, 'color' => 'bg-emerald-500'],
                ['name' => 'Absent', 'value' => $absent, 'color' => 'bg-red-500'],
                ['name' => 'Late', 'value' => $late, 'color' => 'bg-amber-500'],
                ['name' => 'On Leave', 'value' => $onLeaveToday, 'color' => 'bg-blue-500'],
            ];

            // ==========================================
            // 3. DEPARTMENT DISTRIBUTION
            // ==========================================
            $deptQuery = DB::table('hr_employees')->where('status', 'active');
            if ($distributorId) {
                $deptQuery->where('parent_distributor_id', $distributorId);
            }

            // Using direct 'department' string column instead of relational ID
            $deptResult = $deptQuery->select('department', DB::raw('COUNT(id) as count'))
                ->groupBy('department')
                ->get();
                
            $departmentData = [];
            $colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500'];
            foreach($deptResult as $index => $dept) {
                $departmentData[] = [
                    'name' => $dept->department ?: 'Unassigned',
                    'count' => $dept->count,
                    'color' => $colors[$index % count($colors)]
                ];
            }

            // ==========================================
            // 4. RECENT ACTIVITIES 
            // ==========================================
            // Recent Leaves
            $recentLeavesQuery = DB::table('leave_requests')
                ->join('hr_employees', 'leave_requests.employee_id', '=', 'hr_employees.id')
                ->join('users', 'hr_employees.user_id', '=', 'users.id')
                ->select('leave_requests.status', 'leave_requests.updated_at as date', 'users.first_name', 'users.last_name', 'leave_requests.type as context', DB::raw("'leave' as type"))
                ->orderBy('leave_requests.updated_at', 'desc')
                ->limit(3);

            // Recent Onboardings
            $recentHiresQuery = DB::table('hr_employees')
                ->join('users', 'hr_employees.user_id', '=', 'users.id')
                ->select(DB::raw("'Approved' as status"), 'hr_employees.created_at as date', 'users.first_name', 'users.last_name', DB::raw("COALESCE(hr_employees.department, 'Unassigned') as context"), DB::raw("'hire' as type"))
                ->orderBy('hr_employees.created_at', 'desc')
                ->limit(3);

            if ($distributorId) {
                $recentLeavesQuery->where('leave_requests.distributor_id', $distributorId);
                $recentHiresQuery->where('hr_employees.parent_distributor_id', $distributorId);
            }

            // Merge and map
            $activitiesRaw = $recentLeavesQuery->get()->concat($recentHiresQuery->get())->sortByDesc('date')->take(5)->values();

            $recentActivities = $activitiesRaw->map(function($act) {
                return [
                    'id' => uniqid(),
                    'action' => $act->type === 'leave' ? "Leave Request " . $act->status : "New Employee Onboarded",
                    'details' => trim($act->first_name . ' ' . $act->last_name) . ($act->type === 'leave' ? " - " . $act->context : " joined " . $act->context),
                    'time' => Carbon::parse($act->date)->diffForHumans(),
                    'iconType' => $act->type === 'leave' ? 'calendar' : 'user-plus',
                    'iconBg' => $act->type === 'leave' ? ($act->status == 'Approved' ? 'bg-emerald-500/20 text-emerald-600' : 'bg-amber-500/20 text-amber-600') : 'bg-indigo-500/20 text-indigo-600'
                ];
            });

            return response()->json([
                'success' => true,
                'stats' => [
                    'totalEmployees' => $totalEmployees,
                    'activeEmployees' => $activeEmployees,
                    'onLeave' => $onLeave,
                    'newHires' => $newHires,
                ],
                'attendanceData' => $attendanceData,
                'departmentData' => $departmentData,
                'recentActivities' => $recentActivities
            ]);

        } catch (\Exception $e) {
            Log::error('HR Dashboard Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load dashboard data.', 'error' => $e->getMessage()], 500);
        }
    }
}