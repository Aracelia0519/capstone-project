<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\LeaveRequest;
use App\Models\Employee\EmployeeAttendance;
use App\Models\Distributor\HRManager;
use App\Models\Distributor\DistributorWorkingHour;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;


class LeaveRequestController extends Controller
{
    /**
     * Check RBAC Permissions for HR Modules (Specifically leave_request)
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
        
        // Operational Distributor
        elseif ($user->role === 'operational_distributor') {
            $opDist = $user->operationalDistributor; 
            if ($opDist && $opDist->parent_distributor_id) {
                return [
                    'has_access' => true,
                    'distributor_id' => $opDist->parent_distributor_id,
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
                        ->where('permission_key', 'leave_request') // Permission key for this module
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
     * Display a listing of leave requests for the current distributor.
     */
    public function index()
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_view');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized or Distributor not found.'], 403);
        }

        $distributorId = $accessData['distributor_id'];

        // Fetch requests belonging to this distributor
        $query = LeaveRequest::with(['employee'])
            ->orderBy('created_at', 'desc');

        if ($distributorId) {
            $query->where('distributor_id', $distributorId);
        }

        $requests = $query->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'type' => ucfirst($request->type),
                'startDate' => $request->start_date->format('Y-m-d'),
                'endDate' => $request->end_date->format('Y-m-d'),
                'days' => $request->duration,
                'reason' => $request->reason,
                'status' => $request->status,
                'isPaid' => $request->is_paid,
                'appliedOn' => $request->created_at->format('Y-m-d'),
                'rejectionReason' => $request->rejection_reason,
                'employee' => [
                    'name' => $request->employee ? $request->employee->full_name : 'Unknown',
                    'initials' => $request->employee ? substr($request->employee->first_name, 0, 1) . substr($request->employee->last_name, 0, 1) : '??',
                    'avatar' => $request->employee->valid_id_photo ?? '',
                    'department' => $request->employee->department ?? 'General',
                ]
            ];
        });

        return response()->json([
            'data' => $requests,
            'permissions' => $accessData['permissions']
        ]);
    }

    /**
     * Update the status of a leave request (Approve, Reject, Cancel).
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $accessData = $this->checkAccess($user, 'can_update');

        if (!$accessData['has_access']) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to update leave requests.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected,Cancelled',
            'rejection_reason' => 'nullable|string|required_if:status,Rejected',
            'is_paid' => 'boolean'
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($user->role !== 'admin' && $leaveRequest->distributor_id !== $accessData['distributor_id']) {
            return response()->json(['message' => 'Unauthorized to update request for this distributor'], 403);
        }

        $leaveRequest->status = $validated['status'];
        
        if ($validated['status'] === 'Approved') {
            $leaveRequest->approved_by = $user->id;
            $leaveRequest->rejection_reason = null;
            
            // Update the is_paid status based on input (default to true if not provided)
            $leaveRequest->is_paid = $request->input('is_paid', true);

            // --- START: CREATE ATTENDANCE RECORDS FOR LEAVE ---
            try {
                // 1. Fetch Working Hours for this Distributor
                $workingHours = DistributorWorkingHour::where('distributor_id', $leaveRequest->distributor_id)
                    ->get()
                    ->keyBy('day_of_week'); // Key by 'Monday', 'Tuesday', etc.

                // 2. Iterate through date range
                $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);

                foreach ($period as $date) {
                    $dayOfWeek = $date->format('l'); // e.g., "Monday"
                    
                    // Retrieve schedule for this specific day
                    $schedule = $workingHours->get($dayOfWeek);

                    // --- LOGIC CHANGE START: Check for Active Working Hours ---
                    // If no schedule found OR is_open is false OR times are missing -> SKIP THIS DAY
                    if (!$schedule || !$schedule->is_open || !$schedule->start_time || !$schedule->end_time) {
                        continue; // Do not create attendance for this day
                    }
                    // --- LOGIC CHANGE END ---

                    $hoursWorked = 0.00;

                    // Only calculate hours if the leave is marked as PAID
                    if ($leaveRequest->is_paid) {
                        $start = Carbon::parse($schedule->start_time);
                        $end = Carbon::parse($schedule->end_time);
                        
                        // Calculate absolute difference in hours to prevent negative values
                        $hoursWorked = abs($end->diffInHours($start)); 
                    }

                    // Create/Update Attendance Record (Only if we passed the check above)
                    EmployeeAttendance::updateOrCreate(
                        [
                            'employee_id' => $leaveRequest->employee_id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'distributor_id' => $leaveRequest->distributor_id,
                            'status' => 'Leave',
                            'notes' => 'Leave Request Approved: ' . $leaveRequest->type . ' - ' . $leaveRequest->reason,
                            'hours_worked' => $hoursWorked, // 0.00 if unpaid, positive float if paid
                            'time_in' => null,
                            'time_out' => null,
                            'lat_in' => null,
                            'long_in' => null,
                            'lat_out' => null,
                            'long_out' => null,
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::error("Failed to generate attendance for leave request {$id}: " . $e->getMessage());
            }
            // --- END: CREATE ATTENDANCE RECORDS FOR LEAVE ---

        } elseif ($validated['status'] === 'Rejected') {
            $leaveRequest->rejection_reason = $validated['rejection_reason'];
            $leaveRequest->approved_by = $user->id;
        } elseif ($validated['status'] === 'Cancelled') {
            $leaveRequest->rejection_reason = $request->input('rejection_reason', 'Cancelled by HR');
        }

        $leaveRequest->save();

        return response()->json([
            'message' => "Leave request successfully {$validated['status']}.",
            'data' => $leaveRequest
        ]);
    }
}