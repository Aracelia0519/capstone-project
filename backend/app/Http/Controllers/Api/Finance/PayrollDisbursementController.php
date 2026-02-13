<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance\FinancePayroll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PayrollDisbursementController extends Controller
{
    /**
     * Get payrolls that are specifically "Approved" or "Paid".
     * Joins directly with hr_employees to get bank details.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = FinancePayroll::query()
            ->select(
                'payrolls.*',
                'users.first_name',
                'users.last_name',
                'users.email',
                // Fetch current bank details from hr_employees for display
                'hr_employees.bank_name',
                'hr_employees.bank_account_number as employee_account_number', // Renamed to avoid collision with payrolls.account_number
                'hr_employees.bank_account_name as employee_account_name'      // Renamed to avoid collision with payrolls.account_name
            )
            ->leftJoin('users', 'payrolls.user_id', '=', 'users.id')
            // Join hr_employees using the user_id to match the payroll owner
            ->leftJoin('hr_employees', 'payrolls.user_id', '=', 'hr_employees.user_id')
            ->whereIn('payrolls.status', ['approved', 'paid']);

        // Filter by Status
        if ($status && $status !== 'All') {
            $query->where('payrolls.status', strtolower($status));
        }

        // Search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('payrolls.payroll_code', 'like', "%{$search}%")
                  ->orWhere('users.first_name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('payrolls.updated_at', 'desc')->paginate(15);

        return response()->json($payrolls);
    }

    /**
     * Process Payment: Approved -> Paid
     * Auto-generates Transaction Reference.
     * SAVES Account Name and Account Number to database.
     */
    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payroll = FinancePayroll::findOrFail($id);

            if (strtolower($payroll->status) !== 'approved') {
                return response()->json(['message' => 'Only approved payrolls can be processed for payment.'], 400);
            }

            // Fetch Employee Bank Details for Snapshotting
            $employeeDetails = DB::table('hr_employees')
                ->where('user_id', $payroll->user_id)
                ->select('bank_name', 'bank_account_number', 'bank_account_name')
                ->first();

            $bankName = $employeeDetails->bank_name ?? 'Unknown Bank';
            $accNumber = $employeeDetails->bank_account_number ?? 'N/A';
            $accName = $employeeDetails->bank_account_name ?? 'N/A';

            // Determine Payment Method based on Bank Name
            $paymentMethod = 'Bank Transfer'; // Default
            $checkBank = strtolower($bankName);
            
            if (Str::contains($checkBank, ['gcash', 'maya', 'paymaya', 'grabpay', 'paypal', 'coins'])) {
                $paymentMethod = 'E-Wallet';
            } elseif (Str::contains($checkBank, ['cash', 'onhand', 'on-hand'])) {
                $paymentMethod = 'Cash';
            } elseif (Str::contains($checkBank, ['cheque', 'check'])) {
                $paymentMethod = 'Check';
            }

            // Auto-generate a unique transaction reference
            $autoReference = 'TRX-' . strtoupper(Str::random(10));

            // Optional: Still keep a text note as backup
            $bankSnapshotNote = "Payment Details: {$bankName} | Acct No: {$accNumber} | Acct Name: {$accName}.";
            $finalNotes = $bankSnapshotNote . ($request->admin_notes ? " Remarks: " . $request->admin_notes : "");

            $payroll->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
                'payment_reference' => $autoReference, 
                'payment_method' => $paymentMethod,
                'account_name' => $accName,      // <--- Uploading Account Name to DB
                'account_number' => $accNumber,  // <--- Uploading Account Number to DB
                'admin_notes' => $finalNotes
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully.',
                'data' => $payroll,
                'reference' => $autoReference,
                'method' => $paymentMethod,
                'account_name' => $accName,
                'account_number' => $accNumber
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing payment.', 'error' => $e->getMessage()], 500);
        }
    }
}