<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\HR\Employee;
use App\Models\LoginLog;
use App\Models\SecuritySetting;
use App\Models\UserSecurityQuestion;
use App\Models\SystemNotification;
use App\Events\NewLoginLog;
use App\Events\Notification\NotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Jobs\SendRegistrationEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['client', 'distributor', 'service_provider', 'supplier'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => 'pending',
            ]);

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
     * Login user - Main Entry Point
     */
    public function login(Request $request)
    {
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

        $browser = $request->header('User-Agent');

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $this->logFailedAttempt($request->email, $browser, 'User not found / Guessing credentials');
                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }

            $settings = SecuritySetting::firstOrCreate(
                ['user_id' => $user->id],
                ['full_name' => trim($user->first_name . ' ' . $user->last_name), 'role' => $user->role]
            );

            if (!Hash::check($request->password, $user->password)) {
                $this->logFailedAttempt($request->email, $browser, 'Incorrect password / Possible brute force', $user);
                $this->sendLoginAlert($user, $settings, false, $request);
                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }

            if ($user->status === 'inactive') {
                $this->logFailedAttempt($request->email, $browser, 'Account is inactive', $user);
                return response()->json(['status' => 'error', 'message' => 'Your account is inactive. Please contact support.'], 403);
            }

            // ==========================================
            // ONE DEVICE LOGIN (Strict Enforcement)
            // ==========================================
            if ($settings->one_device_login && $user->tokens()->count() > 0) {
                $this->logFailedAttempt($request->email, $browser, 'Blocked: Account already active on another device', $user);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access Denied: This account is currently logged in on another device. Please log out from the other session first.'
                ], 403);
            }

            // ==========================================
            // 2FA CHALLENGES (Strict Enforcement)
            // ==========================================
            $requiresOtp = $settings->email_login_alerts;
            $requiresSq = $settings->security_questions && UserSecurityQuestion::where('user_id', $user->id)->exists();

            // 1. Prioritize Account Recovery Email (OTP)
            if ($requiresOtp) {
                $this->logFailedAttempt($request->email, $browser, 'Requires OTP Authentication', $user, 'Pending Verification');
                return response()->json([
                    'status' => 'requires_otp',
                    'message' => 'Unrecognized device detected. Please choose where to send your verification code.',
                    'emails' => [
                        'primary' => $this->maskEmail($user->email),
                        'recovery' => $user->recovery_email ? $this->maskEmail($user->recovery_email) : null,
                    ]
                ]);
            }

            // 2. Fallback directly to Security Questions if OTP is disabled
            if ($requiresSq) {
                $sq = UserSecurityQuestion::where('user_id', $user->id)->first();
                $this->logFailedAttempt($request->email, $browser, 'Requires Security Question Verification', $user, 'Pending Verification');
                
                $qNum = rand(1, 5);
                $qField = 'question_' . $qNum;

                return response()->json([
                    'status' => 'requires_security_questions',
                    'message' => 'To protect your account, please answer your security question.',
                    'question_key' => $qField,
                    'question_text' => $sq->$qField
                ]);
            }

            // No 2FA required -> Finalize Login
            return $this->finalizeLogin($user, $settings, $request);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Login failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send OTP for 2FA Login
     */
    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'target_type' => 'required|in:primary,recovery'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        $targetEmail = $request->target_type === 'recovery' ? $user->recovery_email : $user->email;

        if (!$targetEmail) {
            return response()->json(['status' => 'error', 'message' => 'Selected email not found.'], 400);
        }

        $otp = rand(100000, 999999);
        Cache::put('login_otp_' . $user->id, $otp, now()->addMinutes(10));

        Log::info("LOGIN OTP for User {$user->id} ({$targetEmail}): {$otp}");

        try {
            Mail::raw("Your CaviteGo Paint login verification code is: {$otp}. It will expire in 10 minutes.", function ($message) use ($targetEmail) {
                $message->to($targetEmail)->subject('Login Verification Code - CaviteGo Paint');
            });
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to send OTP email. Please check your SMTP configuration.'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP sent successfully. Please check your inbox.']);
    }

    /**
     * Verify OTP and potentially chain to Security Questions
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'otp' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        $browser = $request->header('User-Agent');

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        if ($user->status === 'inactive') {
            return response()->json(['status' => 'error', 'message' => 'Your account is inactive.'], 403);
        }

        $settings = SecuritySetting::where('user_id', $user->id)->first();

        // One Device Login Strict Check
        if ($settings && $settings->one_device_login && $user->tokens()->count() > 0) {
            $this->logFailedAttempt($request->email, $browser, 'Blocked: Account already active on another device', $user);
            return response()->json([
                'status' => 'error',
                'message' => 'Access Denied: This account is currently logged in on another device. Please log out from the other session first.'
            ], 403);
        }

        $cachedOtp = Cache::get('login_otp_' . $user->id);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            $this->logFailedAttempt($request->email, $browser, 'Failed OTP Verification', $user);
            $this->sendLoginAlert($user, $settings, false, $request);
            
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired verification code.'], 401);
        }

        Cache::forget('login_otp_' . $user->id);

        // ==========================================
        // CHAINING: If Security Questions are ALSO enabled, trigger them now.
        // ==========================================
        $requiresSq = $settings && $settings->security_questions && UserSecurityQuestion::where('user_id', $user->id)->exists();

        if ($requiresSq) {
            $sq = UserSecurityQuestion::where('user_id', $user->id)->first();
            $qNum = rand(1, 5);
            $qField = 'question_' . $qNum;
            
            return response()->json([
                'status' => 'requires_security_questions',
                'message' => 'OTP Verified. Now, please answer your security question.',
                'question_key' => $qField,
                'question_text' => $sq->$qField
            ]);
        }

        // Successfully answered everything -> Finalize Login
        return $this->finalizeLogin($user, $settings, $request);
    }

    /**
     * Validate Security Questions and finalize login
     */
    public function verifySecurityAnswers(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'question_key' => 'required|string',
            'answer' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        $browser = $request->header('User-Agent');

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        if ($user->status === 'inactive') {
            return response()->json(['status' => 'error', 'message' => 'Your account is inactive.'], 403);
        }

        $settings = SecuritySetting::where('user_id', $user->id)->first();

        // One Device Login Strict Check
        if ($settings && $settings->one_device_login && $user->tokens()->count() > 0) {
            $this->logFailedAttempt($request->email, $browser, 'Blocked: Account already active on another device', $user);
            return response()->json([
                'status' => 'error',
                'message' => 'Access Denied: This account is currently logged in on another device. Please log out from the other session first.'
            ], 403);
        }

        $sq = UserSecurityQuestion::where('user_id', $user->id)->first();
        
        $answerField = str_replace('question', 'answer', $request->question_key);

        if (!$sq || strtolower(trim($sq->$answerField)) !== strtolower(trim($request->answer))) {
            $this->logFailedAttempt($request->email, $browser, 'Failed Security Question Answer', $user);
            $this->sendLoginAlert($user, $settings, false, $request);
            
            return response()->json(['status' => 'error', 'message' => 'Incorrect security answer.'], 401);
        }

        // Successfully answered -> Finalize Login
        return $this->finalizeLogin($user, $settings, $request);
    }

    /**
     * Finalize the login, manage tokens, employee data, and send success alerts.
     */
    private function finalizeLogin(User $user, SecuritySetting $settings, Request $request)
    {
        $browser = $request->header('User-Agent');

        // Initialize employee data
        $employeeData = null;
        if ($user->isEmployee()) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = \App\Models\HR\Position::where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->where('status', 'active')
                    ->first();
                
                $accessibilityKeys = [];
                if ($position) {
                    $accessibilitySettings = \App\Models\HR\PositionAccessibility::where('position_id', $position->id)
                        ->where('is_granted', true)
                        ->get();
                    
                    if ($accessibilitySettings->count() > 0) {
                        $accessibilityKeys = $accessibilitySettings->pluck('permission_key')->toArray();
                    } elseif ($position->requirements && isset($position->requirements['accessibility'])) {
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

        // Generate token and store in remember_token
        $token = $user->createPersonalToken($request->remember);
        
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
        
        if ($employeeData) {
            $userData['employee_data'] = $employeeData;
        }

        // SUCCESSFUL LOGIN - Log the Event
        $log = LoginLog::create([
            'email' => $user->email,
            'status' => 'Success',
            'browser' => $browser,
            'failure_reason' => null,
            'logged_in_at' => now(),
            'Fullname' => $user->full_name,
            'role' => $user->role,
        ]);
        event(new NewLoginLog($log));

        // Send Success Login Alert
        $this->sendLoginAlert($user, $settings, true, $request);

        $response = [
            'status' => 'success',
            'message' => $user->status === 'pending' 
                ? 'Login successful. Your account is pending approval.' 
                : 'Login successful',
            'user' => $userData,
            'token' => $token
        ];
        
        if ($user->status === 'pending') {
            $response['warning'] = 'Your account is pending approval. Some features may be limited.';
        }

        return response()->json($response);
    }

    /**
     * Send System Notification for Login Alert (Websocket & DB)
     */
    private function sendLoginAlert($user, $settings, $isSuccess, Request $request)
    {
        if (!$settings || !$settings->email_login_alerts) {
            return;
        }

        $statusText = $isSuccess ? 'Successful' : 'Failed';
        $title = "Security Alert: {$statusText} Login Attempt";
        $message = "We detected a {$statusText} login attempt to your account on " . now()->format('M d, Y h:i A') . " from IP Address: " . $request->ip() . ". Browser: " . $request->userAgent();

        if (!$isSuccess) {
            $message .= " If this was not you, please reset your password immediately.";
        } else {
            $message .= " If you recognize this activity, you can safely ignore this alert.";
        }

        $notification = SystemNotification::create([
            'type' => $isSuccess ? 'Info' : 'Warning',
            'title' => $title,
            'message' => $message,
            'receiver_id' => $user->id,
            'receiver_role' => $user->role,
            'sender_role' => 'system',
        ]);

        broadcast(new NotificationEvent($notification));
    }

    /**
     * Helper to mask emails (e.g. j****c@gmail.com)
     */
    private function maskEmail($email)
    {
        if (!$email) return null;
        
        $parts = explode('@', $email);
        if (count($parts) != 2) return $email;
        
        $name = $parts[0];
        $domain = $parts[1];
        
        if (strlen($name) <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
        }
        
        return $maskedName . '@' . $domain;
    }

    /**
     * Reusable Logging Helper
     */
    private function logFailedAttempt($email, $browser, $reason, ?User $user = null, $status = 'Failed')
    {
        $log = LoginLog::create([
            'email' => $email,
            'status' => $status,
            'browser' => $browser,
            'failure_reason' => $reason,
            'logged_in_at' => null,
            'Fullname' => $user ? trim($user->first_name . ' ' . $user->last_name) : null,
            'role' => $user ? $user->role : null,
        ]);
        event(new NewLoginLog($log));
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
            return response()->json(['status' => 'error', 'message' => 'Invalid email format'], 422);
        }

        $exists = User::where('email', $request->email)->exists();
        return response()->json(['status' => 'success', 'exists' => $exists]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $employeeData = null;
        if ($user->isEmployee()) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = \App\Models\HR\Position::where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->where('status', 'active')
                    ->first();
                
                $accessibilityKeys = [];
                if ($position) {
                    $accessibilitySettings = \App\Models\HR\PositionAccessibility::where('position_id', $position->id)
                        ->where('is_granted', true)
                        ->get();
                    if ($accessibilitySettings->count() > 0) {
                        $accessibilityKeys = $accessibilitySettings->pluck('permission_key')->toArray();
                    } elseif ($position->requirements && isset($position->requirements['accessibility'])) {
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
        
        if ($employeeData) {
            $userData['employee_data'] = $employeeData;
        }

        return response()->json(['status' => 'success', 'user' => $userData]);
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
            return response()->json(['status' => 'success', 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }
}