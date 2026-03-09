<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client\ClientPaymentSetting;

class ClientPaymentSettingController extends Controller
{
    /**
     * Retrieve the current payment settings for the logged-in client
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        // Fetch or Initialize default settings if none exist
        $settings = ClientPaymentSetting::firstOrCreate(
            ['client_id' => $user->id],
            [
                'gcash_number' => null
            ]
        );

        return response()->json([
            'success' => true, 
            'data' => $settings
        ]);
    }

    /**
     * Update the client's payment settings (GCash)
     */
    public function update(Request $request)
    {
        $request->validate([
            'gcash_number' => 'nullable|string|max:20'
        ]);

        $user = Auth::user();

        $settings = ClientPaymentSetting::updateOrCreate(
            ['client_id' => $user->id],
            [
                'gcash_number' => $request->gcash_number,
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'GCash number saved successfully!', 
            'data' => $settings
        ]);
    }
}