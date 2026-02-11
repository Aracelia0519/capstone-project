<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\AttendanceRequest;
use App\Models\Employee\EmployeeAttendance;
use App\Models\Distributor\HRManager;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
    /**
     * List requests for HR based on their distributor
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user is HR Manager
        $hrManager = HRManager::where('user_id', $user->id)->first();
        
        if (!$hrManager) {
            // Check if user is HR Employee
            $hrEmployee = Employee::where('user_id', $user->id)
                ->where('department', 'Human Resources')
                ->first();
            
            if (!$hrEmployee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access. HR access required.'
                ], 403);
            }
            
            // HR Employee: Get requests for their distributor
            $distributorId = $hrEmployee->parent_distributor_id;
        } else {
            // HR Manager: Get requests for their distributor
            $distributorId = $hrManager->parent_distributor_id;
        }
        
        // Fetch requests with employee data
        $requests = AttendanceRequest::with(['employee.user', 'employee.hrManager.user'])
            ->where('distributor_id', $distributorId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Approve a request and create attendance record
     */
    public function approve(Request $request, $id)
    {
        $attendanceReq = AttendanceRequest::with('employee')->find($id);
        
        if (!$attendanceReq) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found'
            ], 404);
        }

        if ($attendanceReq->status !== 'Pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Request already processed'
            ], 400);
        }

        $user = Auth::user();
        
        // Verify HR has permission for this distributor
        $hrManager = HRManager::where('user_id', $user->id)
            ->where('parent_distributor_id', $attendanceReq->distributor_id)
            ->first();
        
        if (!$hrManager) {
            // Check if HR Employee has permission
            $hrEmployee = Employee::where('user_id', $user->id)
                ->where('parent_distributor_id', $attendanceReq->distributor_id)
                ->where('department', 'Human Resources')
                ->first();
            
            if (!$hrEmployee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to approve requests for this distributor'
                ], 403);
            }
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
        $attendanceReq = AttendanceRequest::find($id);
        
        if (!$attendanceReq) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found'
            ], 404);
        }

        $user = Auth::user();
        
        // Verify HR has permission for this distributor
        $hrManager = HRManager::where('user_id', $user->id)
            ->where('parent_distributor_id', $attendanceReq->distributor_id)
            ->first();
        
        if (!$hrManager) {
            // Check if HR Employee has permission
            $hrEmployee = Employee::where('user_id', $user->id)
                ->where('parent_distributor_id', $attendanceReq->distributor_id)
                ->where('department', 'Human Resources')
                ->first();
            
            if (!$hrEmployee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to reject requests for this distributor'
                ], 403);
            }
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