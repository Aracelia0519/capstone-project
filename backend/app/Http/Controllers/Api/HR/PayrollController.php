<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HR\Payroll;
use App\Models\User;
use App\Models\Distributor\DistributorPayrollSetting;
use App\Models\Distributor\DistributorWorkingHour; 
use App\Models\Employee\EmployeeAttendance;
use App\Models\Employee\LeaveRequest; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // Fetch all payroll history
    public function index()
    {
        $payrolls = Payroll::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($payrolls);
    }

    // STEP 1: Calculate Payroll (Preview) before saving
    public function calculate(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');
        
        $payrollData = [];

        // Parse dates
        $start = Carbon::parse($periodStart);
        $end = Carbon::parse($periodEnd);
        $month = $end->month;
        $year = $end->year;

        // Check for existing payrolls for this month to avoid duplicates
        $existingPayrollUserIds = Payroll::whereYear('period_end', $year)
            ->whereMonth('period_end', $month)
            ->pluck('user_id')
            ->toArray();

        // 1. Fetch HR Employees
        $employees = DB::table('hr_employees')
            ->join('users', 'hr_employees.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id', 
                'hr_employees.id as hr_employee_id', 
                'hr_employees.parent_distributor_id', 
                'users.first_name', 
                'users.last_name', 
                'hr_employees.salary', 
                'hr_employees.position', 
                'hr_employees.department', 
                'hr_employees.employment_type',
                'hr_employees.hire_date'
            )
            ->where('hr_employees.status', 'active')
            ->get();

        foreach ($employees as $emp) {
            if (in_array($emp->user_id, $existingPayrollUserIds)) continue;
            
            // Calculate Attendance Deductions (This is where the logic resides)
            $attendanceData = $this->calculateAttendanceDeductions($emp, $start, $end);
            
            $payrollData[] = $this->computePayrollItem($emp, 'Employee', $attendanceData);
        }

        // 2. Fetch HR Managers
        $hrManagers = DB::table('hr_managers')
            ->join('users', 'hr_managers.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id', 
                'users.first_name', 
                'users.last_name', 
                'hr_managers.salary', 
                'hr_managers.position', 
                DB::raw("'Human Resources' as department"), 
                'hr_managers.employment_type'
            )
            ->where('hr_managers.status', 'active')
            ->get();

        foreach ($hrManagers as $mgr) {
            if (in_array($mgr->user_id, $existingPayrollUserIds)) continue;
            $payrollData[] = $this->computePayrollItem($mgr, 'HR Manager');
        }

        // 3. Fetch Finance Managers
        $financeManagers = DB::table('finance_managers')
            ->join('users', 'finance_managers.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id', 
                'users.first_name', 
                'users.last_name', 
                'finance_managers.salary', 
                'finance_managers.position', 
                DB::raw("'Finance' as department"), 
                'finance_managers.employment_type'
            )
            ->where('finance_managers.status', 'active')
            ->get();

        foreach ($financeManagers as $mgr) {
            if (in_array($mgr->user_id, $existingPayrollUserIds)) continue;
            $payrollData[] = $this->computePayrollItem($mgr, 'Finance Manager');
        }

        // 4. Fetch Operational Distributors
        $opsDistributors = DB::table('operational_distributors')
            ->join('users', 'operational_distributors.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id', 
                'users.first_name', 
                'users.last_name', 
                'operational_distributors.salary', 
                DB::raw("IFNULL(operational_distributors.position, 'Operational Distributor') as position"), 
                DB::raw("'Operations' as department"), 
                'operational_distributors.employment_type'
            )
            ->where('operational_distributors.status', 'active')
            ->get();

        foreach ($opsDistributors as $ops) {
            if (in_array($ops->user_id, $existingPayrollUserIds)) continue;
            $payrollData[] = $this->computePayrollItem($ops, 'Operational Distributor');
        }
        
        return response()->json([
            'payroll_items' => $payrollData,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);
    }

    /**
     * CORE LOGIC: Calculates Expected Days & Late Deductions
     * Based on DistributorWorkingHour and DistributorPayrollSetting
     */
    private function calculateAttendanceDeductions($employee, $periodStart, $periodEnd)
    {
        // 1. Determine Effective Start Date
        $hireDate = Carbon::parse($employee->hire_date);
        
        // If hired after the period ends, no salary
        if ($hireDate->gt($periodEnd)) {
            return $this->zeroAttendance();
        }

        // Use the later date: Period Start or Hire Date
        $effectiveStart = $hireDate->gt($periodStart) ? $hireDate : $periodStart;
        
        // CRITICAL FIX: Force Start to 00:00:00 and End to 23:59:59
        // This ensures dates like "Feb 28" are fully included in the loop
        $effectiveStart->startOfDay(); 
        $periodEnd->endOfDay();
        
        // 2. Fetch Settings & Working Hours specific to the Distributor
        $distributorId = $employee->parent_distributor_id;
        
        $settings = DistributorPayrollSetting::where('distributor_id', $distributorId)->first();
        
        // Keyed by 'Monday', 'Tuesday', etc.
        $workingHours = DistributorWorkingHour::where('distributor_id', $distributorId)
            ->where('is_open', 1)
            ->get()
            ->keyBy('day_of_week'); 

        // 3. Calculate "Expected" Working Days
        // Logic: Iterate through dates, check if day name exists in WorkingHours
        $expectedWorkingDays = 0;
        $current = $effectiveStart->copy();
        
        while ($current->lte($periodEnd)) {
            $dayName = $current->format('l'); // e.g., "Monday"
            if ($workingHours->has($dayName)) {
                $expectedWorkingDays++;
            }
            $current->addDay();
        }

        if ($expectedWorkingDays == 0) return $this->zeroAttendance();

        // 4. Calculate Daily Rate
        // Note: Fluctuates based on the number of working days in the month
        $monthlySalary = floatval($employee->salary ?? 0);
        $dailyRate = $monthlySalary / $expectedWorkingDays; 
        
        // 5. Fetch Actual Attendance (Present or Late)
        $daysPresent = EmployeeAttendance::where('employee_id', $employee->hr_employee_id)
            ->whereBetween('date', [$effectiveStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
            ->whereIn('status', ['Present', 'Late']) 
            ->distinct('date')
            ->count('date');

        // 6. Fetch Approved Leaves
        $leaves = LeaveRequest::where('employee_id', $employee->hr_employee_id)
            ->where('status', 'Approved')
            ->where(function($query) use ($effectiveStart, $periodEnd) {
                // Check for overlapping dates
                $query->whereBetween('start_date', [$effectiveStart, $periodEnd])
                      ->orWhereBetween('end_date', [$effectiveStart, $periodEnd]);
            })
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            
            $loopDate = $leaveStart->copy();
            while ($loopDate->lte($leaveEnd)) {
                // Only count if date is within payroll period and is a valid working day
                if ($loopDate->between($effectiveStart, $periodEnd)) {
                    $dayName = $loopDate->format('l');
                    if ($workingHours->has($dayName)) {
                        if ($leave->is_paid) {
                            $paidLeaveDays++;
                        } else {
                            $unpaidLeaveDays++;
                        }
                    }
                }
                $loopDate->addDay();
            }
        }

        // 7. Calculate Absents (Unauthorized)
        // Formula: Expected - (Worked + Paid Leaves + Unpaid Leaves)
        $unauthorizedAbsentDays = max(0, $expectedWorkingDays - ($daysPresent + $paidLeaveDays + $unpaidLeaveDays));
        
        $absentAmount = $unauthorizedAbsentDays * $dailyRate;
        $unpaidLeaveAmount = $unpaidLeaveDays * $dailyRate;

        // 8. Calculate Lates
        // Depends on DistributorWorkingHour (start_time) and DistributorPayrollSetting (policy)
        $lateCount = 0;
        $lateAmount = 0;
        
        // Rate reference
        $hourlyRate = $dailyRate / 8;
        $minuteRate = $hourlyRate / 60; 

        // Get actual attendance records to check timestamps
        $attendances = EmployeeAttendance::where('employee_id', $employee->hr_employee_id)
            ->whereBetween('date', [$effectiveStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
            ->get();

        foreach ($attendances as $att) {
            // Only check lates if status is Late or Present (with time_in)
            if (($att->status === 'Late' || $att->status === 'Present') && $att->time_in) {
                $dayName = Carbon::parse($att->date)->format('l');
                
                // If it's a working day, check the time
                if ($workingHours->has($dayName)) {
                    $schedStartStr = $workingHours[$dayName]->start_time; // e.g. "09:00:00"
                    
                    if ($schedStartStr) {
                        $actualIn = Carbon::parse($att->date . ' ' . $att->time_in);
                        $expectedIn = Carbon::parse($att->date . ' ' . $schedStartStr);
                        
                        // Apply Grace Period
                        // Default to 0 if null, so we don't accidentally ignore lates
                        $graceMinutes = $settings->late_deduction_minutes ?? 0;
                        $expectedInWithGrace = $expectedIn->copy()->addMinutes($graceMinutes);

                        if ($actualIn->gt($expectedInWithGrace)) {
                            $lateCount++;
                            // Calculate minutes late relative to original start time (not grace time)
                            $minutesLate = $expectedIn->diffInMinutes($actualIn); 

                            // === APPLY POLICY STRICTLY ===
                            if ($settings && $settings->late_deduction_policy === 'fixed_block') {
                                // Block Logic: 50 pesos per 15 mins
                                $blockMinutes = $settings->late_deduction_minutes > 0 ? $settings->late_deduction_minutes : 15;
                                $blockPrice = $settings->late_deduction_amount ?? 0;
                                
                                // How many full blocks?
                                $blocks = floor($minutesLate / $blockMinutes);
                                if ($blocks < 1) $blocks = 1; // Minimum 1 charge if late
                                
                                $lateAmount += ($blocks * $blockPrice);
                                
                            } elseif ($settings && $settings->late_deduction_policy === 'prorated') {
                                // Prorated Logic: Fixed amount per minute (e.g., 2 pesos / min)
                                $pricePerMinute = $settings->late_deduction_amount ?? 0;
                                $lateAmount += ($minutesLate * $pricePerMinute);
                            
                            } else {
                                // Default/Fallback: Salary based deduction
                                $lateAmount += ($minutesLate * $minuteRate); 
                            }
                        }
                    }
                }
            }
        }

        return [
            'expected_days' => $expectedWorkingDays,
            'days_present' => $daysPresent,
            'absent_days' => $unauthorizedAbsentDays,
            'absent_amount' => round($absentAmount, 2),
            'late_count' => $lateCount,
            'late_amount' => round($lateAmount, 2),
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'unpaid_leave_amount' => round($unpaidLeaveAmount, 2)
        ];
    }
    
    private function zeroAttendance() {
        return [
            'expected_days' => 0,
            'days_present' => 0,
            'absent_days' => 0,
            'absent_amount' => 0,
            'late_count' => 0,
            'late_amount' => 0,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'unpaid_leave_amount' => 0
        ];
    }

    private function computePayrollItem($data, $type, $attendanceData = null)
    {
        $salary = floatval($data->salary ?? 0); 
        
        // Fetch deduction amounts from attendance data
        $absentDeduction = abs($attendanceData ? $attendanceData['absent_amount'] : 0);
        $lateDeduction = abs($attendanceData ? $attendanceData['late_amount'] : 0);
        $unpaidLeaveDeduction = abs($attendanceData ? $attendanceData['unpaid_leave_amount'] : 0);
        
        // Statutory Deductions (Standard PH Tables - Simplified)
        $sss = $salary * 0.045;
        if ($sss > 1350) $sss = 1350; 

        $philhealth = $salary * 0.025;
        if ($philhealth < 250) $philhealth = 250;
        if ($philhealth > 2500) $philhealth = 2500;

        $pagibig = ($salary * 0.02);
        if ($pagibig > 200) $pagibig = 200;

        $statutoryDeductions = $sss + $philhealth + $pagibig;
        
        // Calculate Taxable Income
        // Taxable = Basic - (Statutory + Absences + Lates + Unpaid Leaves)
        $attendanceDeductions = $absentDeduction + $lateDeduction + $unpaidLeaveDeduction;
        $taxableIncome = $salary - $statutoryDeductions - $attendanceDeductions;
        
        if ($taxableIncome < 0) $taxableIncome = 0;

        // Withholding Tax Calculation (TRAIN Law - Monthly)
        $tax = 0;
        if ($taxableIncome > 20833 && $taxableIncome <= 33332) {
            $tax = ($taxableIncome - 20833) * 0.15;
        } elseif ($taxableIncome > 33332 && $taxableIncome <= 66666) {
            $tax = 1875 + ($taxableIncome - 33333) * 0.20;
        } elseif ($taxableIncome > 66666 && $taxableIncome <= 166666) {
            $tax = 8541.67 + ($taxableIncome - 66667) * 0.25;
        } elseif ($taxableIncome > 166666 && $taxableIncome <= 666666) {
            $tax = 33541.67 + ($taxableIncome - 166667) * 0.30;
        }
        
        $totalDeductions = $statutoryDeductions + $tax + $attendanceDeductions;
        $netPay = $salary - $totalDeductions;

        return [
            'user_id' => $data->user_id,
            'name' => $data->first_name . ' ' . $data->last_name,
            'department' => $data->department,
            'position' => $data->position ?? $type,
            'employment_type' => $data->employment_type ?? 'full_time',
            'user_type' => $type,
            'basic_salary' => $salary,
            'gross_pay' => $salary,
            'deductions' => [
                'sss' => round($sss, 2),
                'philhealth' => round($philhealth, 2),
                'pagibig' => round($pagibig, 2),
                'tax' => round($tax, 2),
                'absent' => round($absentDeduction, 2), 
                'late' => round($lateDeduction, 2),
                'leave_unpaid' => round($unpaidLeaveDeduction, 2),
                'total' => round($totalDeductions, 2)
            ],
            'attendance_info' => $attendanceData, // Pass the breakdown to frontend
            'net_pay' => round($netPay, 2)
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_items' => 'required|array',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'approved_by' => 'nullable'
        ]);

        $batchCode = 'PAY-' . Carbon::now()->format('Ymd') . '-' . rand(100, 999);
        $savedRecords = [];

        DB::beginTransaction();
        try {
            foreach ($request->payroll_items as $item) {
                $userExists = User::find($item['user_id']);
                if (!$userExists) continue;

                $payroll = Payroll::create([
                    'payroll_code' => $batchCode . '-' . $item['user_id'],
                    'user_id' => $item['user_id'],
                    'user_type' => $item['user_type'],
                    'department' => $item['department'],
                    'position' => $item['position'],
                    'period_start' => $request->period_start,
                    'period_end' => $request->period_end,
                    'pay_date' => Carbon::now(),
                    'basic_salary' => $item['basic_salary'],
                    'gross_pay' => $item['gross_pay'],
                    'sss_contribution' => $item['deductions']['sss'],
                    'philhealth_contribution' => $item['deductions']['philhealth'],
                    'pagibig_contribution' => $item['deductions']['pagibig'],
                    'withholding_tax' => $item['deductions']['tax'],
                    'total_deductions' => $item['deductions']['total'],
                    'net_pay' => $item['net_pay'],
                    'status' => 'pending',
                    'created_by' => Auth::id(), 
                ]);
                
                $savedRecords[] = $payroll;
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Payroll processed successfully', 
                'batch_code' => $batchCode,
                'count' => count($savedRecords)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}