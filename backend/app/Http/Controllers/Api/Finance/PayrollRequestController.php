<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\FinancePayroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollRequestController extends Controller
{
    // List all payroll requests
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $search = $request->query('search');

        $query = FinancePayroll::with(['user', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by Status
        if ($status !== 'All') {
            $query->where('status', strtolower($status));
        }

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('payroll_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $payrolls = $query->paginate(15);

        return response()->json($payrolls);
    }

    // Approve a payroll request
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $payroll = FinancePayroll::findOrFail($id);

            if ($payroll->status !== 'pending') {
                return response()->json(['message' => 'Only pending requests can be approved.'], 400);
            }

            $payroll->status = 'approved';
            $payroll->approved_by = Auth::id();
            $payroll->pay_date = Carbon::now(); // Set pay date to approval date
            $payroll->save();

            DB::commit();

            return response()->json([
                'message' => 'Payroll request approved successfully.',
                'data' => $payroll
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error approving request.', 'error' => $e->getMessage()], 500);
        }
    }

    // Reject (Cancel) a payroll request
    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $payroll = FinancePayroll::findOrFail($id);

            if ($payroll->status !== 'pending') {
                return response()->json(['message' => 'Only pending requests can be rejected.'], 400);
            }

            // You can log the rejection reason if you add a 'remarks' column to DB
            $payroll->status = 'rejected'; 
            $payroll->approved_by = Auth::id(); // Track who rejected it
            $payroll->save();

            DB::commit();

            return response()->json([
                'message' => 'Payroll request rejected.',
                'data' => $payroll
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error rejecting request.', 'error' => $e->getMessage()], 500);
        }
    }
    
    // Get single details
    public function show($id)
    {
        $payroll = FinancePayroll::with(['user', 'creator', 'approver'])->findOrFail($id);
        return response()->json($payroll);
    }
}