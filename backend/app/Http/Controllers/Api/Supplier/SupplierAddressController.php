<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier\SupplierRequirements;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierAddressController extends Controller
{
    /**
     * Get the address for the authenticated supplier
     */
    public function index()
    {
        $user = Auth::user();
        $requirement = SupplierRequirements::where('user_id', $user->id)->first();

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
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $requirement = SupplierRequirements::where('user_id', $user->id)->first();

        if (!$requirement) {
            return response()->json(['message' => 'Supplier requirements not found'], 404);
        }

        $address = $requirement->address()->updateOrCreate(
            ['supplier_requirements_id' => $requirement->id],
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