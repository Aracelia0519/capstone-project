<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client\ClientRequirement;
use App\Models\Client\ClientAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientAddressController extends Controller
{
    /**
     * Get the address for the authenticated client
     */
    public function index()
    {
        $user = Auth::user();
        $requirement = ClientRequirement::where('user_id', $user->id)->first();

        if (!$requirement || !$requirement->address) {
            return response()->json([
                'status' => 'error',
                'message' => 'Address not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $requirement->address
        ]);
    }

    /**
     * Update address coordinates manually if needed
     */
    public function updateCoordinates(Request $request)
    {
        // Added stricter validation to ensure coordinates are within Cavite bounding box
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|min:14.0000|max:14.6000',
            'longitude' => 'required|numeric|min:120.5000|max:121.1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $requirement = ClientRequirement::where('user_id', $user->id)->first();

        if (!$requirement) {
            return response()->json(['message' => 'Client requirements not found'], 404);
        }

        $address = $requirement->address()->updateOrCreate(
            ['client_requirements_id' => $requirement->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Location coordinates updated',
            'data' => $address
        ]);
    }
}