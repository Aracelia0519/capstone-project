<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Jobs\SendRegistrationEmail;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['client', 'distributor', 'service_provider'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => 'pending',
            ]);

            // Dispatch the registration email job
            SendRegistrationEmail::dispatch([
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at
            ]);

            // Generate token
            $token = $user->createPersonalToken($request->remember);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. Please wait for admin approval.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user - Modified to include employee department data
     */
    public function login(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Only block inactive users, allow pending users
            if ($user->status === 'inactive') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is inactive. Please contact support.'
                ], 403);
            }

            // Initialize employee data
            $employeeData = null;
            
            // If user is an employee, get their department from hr_employees table
            if ($user->isEmployee()) {
                $employee = Employee::where('user_id', $user->id)->first();
                
                if ($employee) {
                    $employeeData = [
                        'department' => $employee->department,
                        'position' => $employee->position,
                        'employee_code' => $employee->employee_code,
                        'employment_status' => $employee->employment_status
                    ];
                }
            }

            // Generate token and store in remember_token
            $token = $user->createPersonalToken($request->remember);
            
            // Prepare user data
            $userData = [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
            ];
            
            // Add employee data if exists
            if ($employeeData) {
                $userData['employee_data'] = $employeeData;
            }
            
            // Prepare response based on status
            $response = [
                'status' => 'success',
                'message' => $user->status === 'pending' 
                    ? 'Login successful. Your account is pending approval.' 
                    : 'Login successful',
                'user' => $userData,
                'token' => $token
            ];
            
            // Add warning message for pending users
            if ($user->status === 'pending') {
                $response['warning'] = 'Your account is pending approval. Some features may be limited.';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if email exists
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email format'
            ], 422);
        }

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists
        ]);
    }

    /**
     * Get authenticated user - Updated to include employee data
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not authenticated'
            ], 401);
        }

        // Initialize employee data
        $employeeData = null;
        
        // If user is an employee, get their department from hr_employees table
        if ($user->isEmployee()) {
            $employee = Employee::where('user_id', $user->id)->first();
            
            if ($employee) {
                // Find position to get accessibility
                $position = \App\Models\HR\Position::where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->where('status', 'active')
                    ->first();
                
                $accessibilityKeys = [];
                
                if ($position) {
                    // Get from position_accessibilities table
                    $accessibilitySettings = \App\Models\HR\PositionAccessibility::where('position_id', $position->id)
                        ->where('is_granted', true)
                        ->get();
                    
                    if ($accessibilitySettings->count() > 0) {
                        $accessibilityKeys = $accessibilitySettings->pluck('permission_key')->toArray();
                    } 
                    // Fallback to requirements
                    elseif ($position->requirements && isset($position->requirements['accessibility'])) {
                        $accessibilityKeys = $position->requirements['accessibility'];
                    }
                }
                
                $employeeData = [
                    'department' => $employee->department,
                    'position' => $employee->position,
                    'employee_code' => $employee->employee_code,
                    'employment_status' => $employee->employment_status,
                    'accessibility_keys' => $accessibilityKeys
                ];
            }
        }

        // Prepare user data
        $userData = [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];
        
        // Add employee data if exists
        if ($employeeData) {
            $userData['employee_data'] = $employeeData;
        }

        return response()->json([
            'status' => 'success',
            'user' => $userData
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user) {
                $user->revokeTokens();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}