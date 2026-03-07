<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceProvider\SPPaymentSetting;

class SPPaymentSettingController extends Controller
{
    /**
     * Retrieve the current payment settings for the logged-in service provider
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        // Fetch or Initialize default settings if none exist
        $settings = SPPaymentSetting::firstOrCreate(
            ['service_provider_id' => $user->id],
            [
                'is_on_hand_enabled' => true,
                'is_gcash_enabled' => false,
                'gcash_number' => null
            ]
        );

        return response()->json([
            'success' => true, 
            'data' => $settings
        ]);
    }

    /**
     * Update the service provider's payment settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'is_on_hand_enabled' => 'required|boolean',
            'is_gcash_enabled' => 'required|boolean',
            // GCash number is strictly required only if GCash is enabled
            'gcash_number' => 'required_if:is_gcash_enabled,true|nullable|string|max:20'
        ]);

        $user = Auth::user();

        $settings = SPPaymentSetting::updateOrCreate(
            ['service_provider_id' => $user->id],
            [
                'is_on_hand_enabled' => $request->is_on_hand_enabled,
                'is_gcash_enabled' => $request->is_gcash_enabled,
                'gcash_number' => $request->is_gcash_enabled ? $request->gcash_number : null,
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Payment methods updated successfully!', 
            'data' => $settings
        ]);
    }
}