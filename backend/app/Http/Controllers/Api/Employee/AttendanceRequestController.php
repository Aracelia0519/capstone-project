<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\AttendanceRequest;
use App\Models\Employee\EmployeeAttendance;
use App\Models\Distributor\HRManager;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
    /**
     * Check RBAC Permissions for HR Modules (Specifically attendance_request)
     */
    private function checkAccess($user, $action = 'can_view')
    {
        // Admin
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // Distributor
        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // HR Manager
        if ($user->role === 'hr_manager') {
            $hrManager = HRManager::where('user_id', $user->id)->first();
            if ($hrManager && $hrManager->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $hrManager->parent_distributor_id,
                    'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
                ];
            }
        } 
        
        // Employee with specific RBAC
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
                        ->where('permission_key', 'attendance_request') // Permission key for this module
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_create' && $access->can_create) $hasAccess = true;
                        if ($action === 'can_update' && $access->can_update) $hasAccess = true;
                        if ($action === 'can_delete' && $access->can_delete) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_create' => (bool)$access->can_create,
                                    'can_update' => (bool)$access->can_update,
                                    'can_delete' => (bool)$access->can_delete,
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
            'permissions' => ['can_view' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false]
        ];
    }

    /**
     * List requests for HR based on their distributor
     */
    public function index()
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');
        
        if (!$accessData['has_access']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access. You do not have permission to view attendance requests.'
            ], 403);
        }
        
        $distributorId = $accessData['distributor_id'];
        
        // Fetch requests with employee data
        $query = AttendanceRequest::with(['employee.user', 'employee.hrManager.user'])
            ->orderBy('created_at', 'desc');
            
        if ($distributorId) {
            $query->where('distributor_id', $distributorId);
        }
        
        $requests = $query->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $requests,
            'permissions' => $accessData['permissions']
        ]);
    }

    /**
     * Approve a request and create attendance record
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_update');
        
        if (!$accessData['has_access']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have permission to approve attendance requests.'
            ], 403);
        }

        $attendanceReq = AttendanceRequest::with('employee')->find($id);
        
        if (!$attendanceReq) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found'
            ], 404);
        }

        // Validate Distributor ownership (unless Admin)
        if ($user->role !== 'admin' && $attendanceReq->distributor_id !== $accessData['distributor_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to approve requests for this distributor'
            ], 403);
        }

        if ($attendanceReq->status !== 'Pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Request already processed'
            ], 400);
        }

        // Create or Update the actual attendance record
        $reqDate = Carbon::parse($attendanceReq->requested_time)->format('Y-m-d');
        $reqTime = Carbon::parse($attendanceReq->requested_time)->format('H:i:s');

        if ($attendanceReq->type === 'Clock In') {
            // Check if already exists to prevent duplicate
            $exists = EmployeeAttendance::where('employee_id', $attendanceReq->employee_id)
                ->where('date', $reqDate)
                ->first();
            
            if (!$exists) {
                $attendance = EmployeeAttendance::create([
                    'employee_id' => $attendanceReq->employee_id,
                    'distributor_id' => $attendanceReq->distributor_id,
                    'date' => $reqDate,
                    'time_in' => $reqTime,
                    'lat_in' => $attendanceReq->latitude,
                    'long_in' => $attendanceReq->longitude,
                    'status' => 'Present',
                    'notes' => 'Manual Approval: ' . $attendanceReq->reason
                ]);
            } else {
                // Update existing record
                $exists->update([
                    'time_in' => $reqTime,
                    'lat_in' => $attendanceReq->latitude,
                    'long_in' => $attendanceReq->longitude,
                    'notes' => $exists->notes . ' | Manual In: ' . $attendanceReq->reason
                ]);
            }
        } else {
            // Clock Out
            $attendance = EmployeeAttendance::where('employee_id', $attendanceReq->employee_id)
                ->where('date', $reqDate)
                ->first();
            
            if ($attendance) {
                // Calculate hours
                $timeIn = Carbon::parse($attendance->time_in);
                $timeOut = Carbon::parse($reqTime);
                $hours = $timeIn->diffInHours($timeOut, true);

                $attendance->update([
                    'time_out' => $reqTime,
                    'lat_out' => $attendanceReq->latitude,
                    'long_out' => $attendanceReq->longitude,
                    'hours_worked' => $hours,
                    'notes' => ($attendance->notes ?? '') . ' | Manual Out: ' . $attendanceReq->reason
                ]);
            } else {
                // Create new attendance record if clock out without clock in
                EmployeeAttendance::create([
                    'employee_id' => $attendanceReq->employee_id,
                    'distributor_id' => $attendanceReq->distributor_id,
                    'date' => $reqDate,
                    'time_out' => $reqTime,
                    'lat_out' => $attendanceReq->latitude,
                    'long_out' => $attendanceReq->longitude,
                    'status' => 'Present',
                    'notes' => 'Manual Clock Out Only: ' . $attendanceReq->reason
                ]);
            }
        }

        // Update the request
        $attendanceReq->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
            'admin_remarks' => $request->remarks ?? 'Approved by HR'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request approved successfully'
        ]);
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_update');
        
        if (!$accessData['has_access']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have permission to reject attendance requests.'
            ], 403);
        }

        $attendanceReq = AttendanceRequest::find($id);
        
        if (!$attendanceReq) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found'
            ], 404);
        }

        // Validate Distributor ownership (unless Admin)
        if ($user->role !== 'admin' && $attendanceReq->distributor_id !== $accessData['distributor_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to reject requests for this distributor'
            ], 403);
        }

        if ($attendanceReq->status !== 'Pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Request already processed'
            ], 400);
        }

        $attendanceReq->update([
            'status' => 'Rejected',
            'approved_by' => $user->id,
            'admin_remarks' => $request->remarks ?? 'Rejected by HR'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request rejected'
        ]);
    }
}