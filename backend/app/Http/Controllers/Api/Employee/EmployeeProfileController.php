<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\HR\Employee;

class EmployeeProfileController extends Controller
{
    /**
     * Fetch the authenticated user's profile based on their role.
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $profileData = $user->toArray();
        $specificData = [];
        
        try {
            if ($user->role === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $specificData = $employee->toArray();
                    // Map emergency contact for the frontend format
                    $specificData['emergency_contacts'] = [];
                    if (!empty($employee->emergency_contact)) {
                        $specificData['emergency_contacts'][] = [
                            'id' => 1,
                            'name' => 'Primary Contact',
                            'relationship' => 'Emergency',
                            'phone' => $employee->emergency_contact,
                            'email' => 'N/A',
                            'address' => 'N/A'
                        ];
                    }
                }
            } elseif ($user->role === 'hr_manager') {
                $hrManager = DB::table('hr_managers')->where('user_id', $user->id)->first();
                if ($hrManager) {
                    $specificData = (array) $hrManager;
                    $specificData['department'] = 'Human Resources';
                    $specificData['position'] = 'HR Manager';
                }
            } elseif ($user->role === 'finance_manager') {
                $financeManager = DB::table('finance_managers')->where('user_id', $user->id)->first();
                if ($financeManager) {
                    $specificData = (array) $financeManager;
                    $specificData['department'] = 'Finance';
                    $specificData['position'] = 'Finance Manager';
                }
            } elseif ($user->role === 'operational_distributor' || $user->role === 'distributor') {
                $distributor = DB::table('distributor_requirements')->where('user_id', $user->id)->first();
                if ($distributor) {
                    $specificData = (array) $distributor;
                    $specificData['department'] = 'Operations';
                    $specificData['position'] = 'Operational Distributor';
                }
            }

            // Merge the User base data with their role-specific data
            $unifiedProfile = array_merge($profileData, $specificData);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'profile' => $unifiedProfile
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Pass raw text so Laravel's user model 'password => hashed' cast handles it natively.
            // This prevents double-hashing.
            $user->update([
                'password' => $request->password 
            ]);

            // If the user is an employee and their explicit password field needs updating too
            if ($user->role === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    // Manually hash here for the employee table if it doesn't have automatic casting
                    $employee->update([
                        'password' => Hash::make($request->password)
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}