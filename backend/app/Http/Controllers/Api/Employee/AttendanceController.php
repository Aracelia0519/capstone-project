<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Added for file upload
use App\Models\Employee\EmployeeAttendance;
use App\Models\Employee\AttendanceRequest; // Added
use App\Models\HR\Employee; 
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\DistributorWorkingHour;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private $allowedRadius = 500; 

    public function getSchedule()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee record not found'], 404);
        }

        $today = Carbon::now()->format('l');

        $schedule = DistributorWorkingHour::where('distributor_id', $employee->parent_distributor_id)
            ->where('day_of_week', $today)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee record not found'], 404);
        }

        $monthlyRecords = EmployeeAttendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $today = Carbon::today()->format('Y-m-d');
        $todayRecord = EmployeeAttendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'history' => $monthlyRecords,
                'today' => $todayRecord
            ]
        ]);
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee record not found'], 404);
        }

        $today = Carbon::today()->format('Y-m-d');
        $existing = EmployeeAttendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'You have already clocked in today.'], 400);
        }

        $locationCheck = $this->validateLocation($employee->parent_distributor_id, $request->latitude, $request->longitude);
        
        if (!$locationCheck['valid']) {
            return response()->json([
                'status' => 'error', 
                'code' => 'LOCATION_INVALID', // Added specific code for frontend
                'message' => 'You are too far from the office location. ' . $locationCheck['message']
            ], 403);
        }

        $now = Carbon::now();
        $dayName = $now->format('l');
        
        $schedule = DistributorWorkingHour::where('distributor_id', $employee->parent_distributor_id)
            ->where('day_of_week', $dayName)
            ->first();

        $status = 'Present';

        if ($schedule) {
            if ($schedule->is_open && $schedule->start_time) {
                $startTime = Carbon::parse($today . ' ' . $schedule->start_time);
                $gracePeriod = $startTime->copy()->addMinutes(15);

                if ($now->greaterThan($gracePeriod)) {
                    $status = 'Late';
                }
            }
        }

        $attendance = EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'distributor_id' => $employee->parent_distributor_id, 
            'date' => $today,
            'time_in' => $now->format('H:i:s'),
            'lat_in' => $request->latitude,
            'long_in' => $request->longitude,
            'status' => $status
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked in successfully',
            'data' => $attendance
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
             return response()->json(['status' => 'error', 'message' => 'Employee record not found'], 404);
        }

        $today = Carbon::today()->format('Y-m-d');
        $attendance = EmployeeAttendance::where('employee_id', $employee->id)->where('date', $today)->first();

        if (!$attendance) {
            return response()->json(['status' => 'error', 'message' => 'You have not clocked in yet.'], 400);
        }

        if ($attendance->time_out) {
            return response()->json(['status' => 'error', 'message' => 'You have already clocked out today.'], 400);
        }

        $locationCheck = $this->validateLocation($employee->parent_distributor_id, $request->latitude, $request->longitude);
        if (!$locationCheck['valid']) {
             return response()->json([
                'status' => 'error', 
                'code' => 'LOCATION_INVALID', // Added specific code for frontend
                'message' => 'You must be at the office to clock out. ' . $locationCheck['message']
            ], 403);
        }

        $now = Carbon::now();
        $timeIn = Carbon::parse($attendance->time_in);
        $duration = $timeIn->diffInHours($now);

        $attendance->update([
            'time_out' => $now->format('H:i:s'),
            'lat_out' => $request->latitude,
            'long_out' => $request->longitude,
            'hours_worked' => $duration
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Clocked out successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Submit Attendance Request (Manual Override)
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'type' => 'required|in:Clock In,Clock Out',
            'reason' => 'required|string|max:500',
            'photo' => 'required|image|max:5120', // Max 5MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found'], 404);
        }

        // --- NEW VALIDATION: Check for existing Pending or Approved requests ---
        $today = Carbon::now()->format('Y-m-d');
        
        $existingRequest = AttendanceRequest::where('employee_id', $employee->id)
            ->where('type', $request->type)
            ->whereDate('requested_time', $today)
            ->where('status', '!=', 'Rejected') // Allow them to resubmit if the previous one was Rejected
            ->first();

        if ($existingRequest) {
            return response()->json([
                'status' => 'error',
                'message' => "You already have a {$existingRequest->status} {$request->type} request for today."
            ], 400);
        }
        // ---------------------------------------------------------------------

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('attendance_proofs', 'public');
        }

        $attendanceRequest = AttendanceRequest::create([
            'employee_id' => $employee->id,
            'distributor_id' => $employee->parent_distributor_id,
            'type' => $request->type,
            'requested_time' => Carbon::now(),
            'reason' => $request->reason,
            'photo_proof' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'Pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request submitted successfully. HR will review your submission.',
            'data' => $attendanceRequest
        ]);
    }

    private function validateLocation($distributorId, $userLat, $userLong)
    {
        $requirements = DistributorRequirements::where('user_id', $distributorId)->first();
        
        if (!$requirements) {
             return ['valid' => false, 'message' => 'Office location configuration missing.'];
        }
        
        $address = \Illuminate\Support\Facades\DB::table('distributor_addresses')
                    ->where('distributor_requirements_id', $requirements->id)
                    ->first();

        if (!$address || !$address->latitude || !$address->longitude) {
            return ['valid' => false, 'message' => 'Office coordinates invalid or not set.'];
        }

        $officeLat = $address->latitude;
        $officeLong = $address->longitude;

        $earthRadius = 6371000;

        $latFrom = deg2rad($userLat);
        $lonFrom = deg2rad($userLong);
        $latTo = deg2rad($officeLat);
        $lonTo = deg2rad($officeLong);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;

        if ($distance <= $this->allowedRadius) {
            return ['valid' => true, 'distance' => $distance];
        }

        return [
            'valid' => false, 
            'message' => "You are " . round($distance) . " meters away. Allowed radius: {$this->allowedRadius}m."
        ];
    }
}