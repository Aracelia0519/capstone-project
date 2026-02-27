<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use App\Models\HR\PositionAccessibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OperationDistributorSidebarController extends Controller
{
    /**
     * Get sidebar accessibility for the authenticated user
     */
    public function getSidebarAccess(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // If user is a distributor, admin, OR operational_distributor, grant full access
            if (in_array($user->role, ['distributor', 'admin', 'operational_distributor'])) {
                return response()->json([
                    'status' => 'success',
                    'has_full_access' => true,
                    'access_keys' => []
                ]);
            }

            // If user is an employee, determine their specific access
            if ($user->isEmployee()) {
                $employee = Employee::where('user_id', $user->id)->first();

                if (!$employee) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Employee record not found.'
                    ], 404);
                }

                // Find position based on title and their distributor
                $position = Position::where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->where('status', 'active')
                    ->first();

                $accessibilityKeys = [];

                if ($position) {
                    // Try getting accessibilities from the database table first
                    $accessibilitySettings = PositionAccessibility::where('position_id', $position->id)
                        ->where('is_granted', true)
                        ->get();

                    if ($accessibilitySettings->count() > 0) {
                        $accessibilityKeys = $accessibilitySettings->pluck('permission_key')->toArray();
                    } 
                    // Fallback to the JSON requirements field for backward compatibility
                    elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                        $accessibilityKeys = $position->requirements['accessibility'];
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'has_full_access' => false,
                    'access_keys' => $accessibilityKeys
                ]);
            }

            // Default fallback response
            return response()->json([
                'status' => 'success',
                'has_full_access' => false,
                'access_keys' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Operation Distributor sidebar access: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch sidebar access',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}