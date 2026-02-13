<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\EmployeePayroll;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Fetch only 'paid' payrolls for the logged-in employee
        $payrolls = EmployeePayroll::where('user_id', $userId)
            ->where('status', 'paid')
            ->orderBy('period_end', 'desc') // Latest first
            ->get();

        // Calculate YTD Earnings (Sum of Net Pay for the current year)
        $currentYear = Carbon::now()->year;
        $ytdEarnings = EmployeePayroll::where('user_id', $userId)
            ->where('status', 'paid')
            ->whereYear('paid_at', $currentYear)
            ->sum('net_pay');

        return response()->json([
            'payrolls' => $payrolls,
            'ytd_earnings' => $ytdEarnings,
            'count' => $payrolls->count()
        ]);
    }

    public function show($id)
    {
        $payroll = EmployeePayroll::where('user_id', Auth::id())
            ->where('status', 'paid')
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($payroll);
    }
}