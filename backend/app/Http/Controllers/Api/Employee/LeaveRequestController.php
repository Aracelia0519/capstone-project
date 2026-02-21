<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\LeaveRequest;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Get leave history and current balances
     */
    public function index()
    {
        $user = Auth::user();
        
        // Find the HR Employee record linked to this User
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        // Fetch History
        $history = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate Balances (Dynamic Logic)
        // Updated defaults to align with the new 30-day max limits and 150 max maternity
        $defaults = [
            'vacation' => 30,
            'sick' => 30,
            'emergency' => 30,
            'maternity' => 150
        ];

        // Sum used approved leaves
        $used = [
            'vacation' => LeaveRequest::where('employee_id', $employee->id)->where('type', 'vacation')->where('status', 'Approved')->sum('duration'),
            'sick' => LeaveRequest::where('employee_id', $employee->id)->where('type', 'sick')->where('status', 'Approved')->sum('duration'),
            'emergency' => LeaveRequest::where('employee_id', $employee->id)->where('type', 'emergency')->where('status', 'Approved')->sum('duration'),
            'maternity' => LeaveRequest::where('employee_id', $employee->id)->where('type', 'maternity')->where('status', 'Approved')->sum('duration'),
        ];

        $balances = [
            [
                'type' => 'Vacation Leave',
                'value' => 'vacation',
                'available' => $defaults['vacation'] - $used['vacation'],
                'total' => $defaults['vacation'],
                'percentage' => round((($defaults['vacation'] - $used['vacation']) / $defaults['vacation']) * 100),
                'icon' => 'fas fa-umbrella-beach text-blue-600',
                'colorClass' => 'bg-blue-50',
                'progressClass' => '[&>div]:bg-blue-500'
            ],
            [
                'type' => 'Sick Leave',
                'value' => 'sick',
                'available' => $defaults['sick'] - $used['sick'],
                'total' => $defaults['sick'],
                'percentage' => round((($defaults['sick'] - $used['sick']) / $defaults['sick']) * 100),
                'icon' => 'fas fa-heartbeat text-red-600',
                'colorClass' => 'bg-red-50',
                'progressClass' => '[&>div]:bg-red-500'
            ],
            [
                'type' => 'Emergency Leave',
                'value' => 'emergency',
                'available' => $defaults['emergency'] - $used['emergency'],
                'total' => $defaults['emergency'],
                'percentage' => round((($defaults['emergency'] - $used['emergency']) / $defaults['emergency']) * 100),
                'icon' => 'fas fa-exclamation-triangle text-amber-600',
                'colorClass' => 'bg-amber-50',
                'progressClass' => '[&>div]:bg-amber-500'
            ],
            [
                'type' => 'Maternity Leave',
                'value' => 'maternity',
                'available' => $defaults['maternity'] - $used['maternity'],
                'total' => $defaults['maternity'],
                'percentage' => round((($defaults['maternity'] - $used['maternity']) / $defaults['maternity']) * 100),
                'icon' => 'fas fa-baby text-purple-600',
                'colorClass' => 'bg-purple-50',
                'progressClass' => '[&>div]:bg-purple-500'
            ],
        ];

        return response()->json([
            'history' => $history,
            'balances' => $balances
        ]);
    }

    /**
     * Store a new leave request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:vacation,sick,emergency,maternity',
            'duration' => 'required|numeric|min:0.5',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        // Dynamic Duration Validations
        $validator->after(function ($validator) use ($request) {
            if ($request->type !== 'maternity' && $request->duration > 30) {
                $validator->errors()->add('duration', 'Maximum leave duration is 30 days.');
            }
            if ($request->type === 'maternity' && $request->duration > 150) {
                $validator->errors()->add('duration', 'Maximum maternity leave duration is 150 days.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        // Check for overlapping dates
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', 'Rejected')
            ->where('status', '!=', 'Cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlap) {
            return response()->json(['message' => 'You already have a leave request for these dates.'], 409);
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'distributor_id' => $employee->parent_distributor_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $request->duration,
            'reason' => $request->reason,
            'status' => 'Pending'
        ]);

        return response()->json([
            'message' => 'Leave request submitted successfully',
            'data' => $leaveRequest
        ], 201);
    }
}