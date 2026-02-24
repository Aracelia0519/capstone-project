<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonnelOfficer\Personnel;

class SupplierSidebarController extends Controller
{
    /**
     * Get the accessible sidebar modules for the authenticated user.
     */
    public function getSidebarAccess(Request $request)
    {
        $user = $request->user();

        // 1. If Supplier or Personnel Officer, grant full access to all modules
        if ($user->isSupplier() || $user->isPersonnelOfficer()) {
            return response()->json([
                'status' => 'success',
                'full_access' => true,
                'accessibilities' => []
            ]);
        }

        // 2. If Supplier Employee, restrict based on assigned accessibilities
        if ($user->isSupplierEmployee()) {
            $personnel = Personnel::where('user_id', $user->id)
                                  ->with('accessibilities')
                                  ->first();

            if (!$personnel) {
                return response()->json([
                    'status' => 'success',
                    'full_access' => false,
                    'accessibilities' => []
                ]);
            }

            // Extract the path and name to make frontend filtering bulletproof
            $accessibilities = $personnel->accessibilities->map(function ($acc) {
                return [
                    'path' => $acc->module_path,
                    'name' => $acc->module_name
                ];
            });

            return response()->json([
                'status' => 'success',
                'full_access' => false,
                'accessibilities' => $accessibilities
            ]);
        }

        // Default fallback for any other unexpected roles
        return response()->json([
            'status' => 'success',
            'full_access' => false,
            'accessibilities' => []
        ]);
    }
}