<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Distributor\DistributorPayrollSetting;
use App\Models\Distributor\HRManager; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PayrollSettingController extends Controller
{
    /**
     * Get the current distributor's payroll settings.
     */
    public function show()
    {
        $user = Auth::user();
        $targetUserId = $user->id;

        // 1. Check if the current user is an HR Manager
        // If so, we need to get the settings of their Parent Distributor (Business Owner)
        $hrManager = HRManager::where('user_id', $user->id)->first();
        if ($hrManager) {
            $targetUserId = $hrManager->parent_distributor_id;
        }
        
        // 2. Fetch settings using the correct Target ID (Distributor's ID)
        $settings = DistributorPayrollSetting::where('distributor_id', $targetUserId)->first();

        if (!$settings) {
            // Return defaults if not set yet
            return response()->json([
                'frequency' => 'weekly', 
                'day_of_week' => 'friday',
                'bi_monthly_schedule' => '1-15',
                'day_of_month' => '1',
                'late_deduction_policy' => 'none',
                'late_deduction_amount' => null,
                'late_deduction_minutes' => null,
                'is_new' => true
            ]);
        }

        return response()->json($settings);
    }

    /**
     * Update or Create the payroll settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Existing Frequency Validations
            'frequency' => 'required|in:daily,weekly,bi-monthly,monthly',
            'day_of_week' => 'nullable|required_if:frequency,weekly|string',
            'bi_monthly_schedule' => 'nullable|required_if:frequency,bi-monthly|string',
            'day_of_month' => 'nullable|required_if:frequency,monthly|string',
            
            // New Late Deduction Validations
            'late_deduction_policy' => 'required|in:none,prorated,fixed_block',
            'late_deduction_amount' => 'nullable|required_if:late_deduction_policy,prorated,fixed_block|numeric|min:0',
            'late_deduction_minutes' => 'nullable|required_if:late_deduction_policy,fixed_block|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        
        $settings = DistributorPayrollSetting::updateOrCreate(
            ['distributor_id' => $user->id],
            [
                // Frequency
                'frequency' => $request->frequency,
                'day_of_week' => $request->frequency === 'weekly' ? $request->day_of_week : null,
                'bi_monthly_schedule' => $request->frequency === 'bi-monthly' ? $request->bi_monthly_schedule : null,
                'day_of_month' => $request->frequency === 'monthly' ? $request->day_of_month : null,
                
                // Late Deduction
                'late_deduction_policy' => $request->late_deduction_policy,
                'late_deduction_amount' => $request->late_deduction_policy !== 'none' ? $request->late_deduction_amount : null,
                'late_deduction_minutes' => $request->late_deduction_policy === 'fixed_block' ? $request->late_deduction_minutes : null,
            ]
        );

        return response()->json($settings);
    }
}