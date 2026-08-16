<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SecuritySetting;
use App\Models\UserSecurityQuestion;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SecuritySettingController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $settings = SecuritySetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => trim($user->first_name . ' ' . $user->last_name),
                'role' => $user->role,
            ]
        );

        return response()->json($settings);
    }

    // Standard Toggle (For the 4 basic settings)
    public function update(Request $request)
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|boolean',
            'device_token' => 'nullable|string' // Needed when toggling "remember_this_device" off
        ]);

        $user = Auth::user();
        $field = $request->field;
        $value = $request->value;

        $allowedFields = ['email_login_alerts', 'one_device_login', 'session_timeout', 'remember_this_device'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['message' => 'Invalid basic setting field.'], 400);
        }

        $settings = SecuritySetting::where('user_id', $user->id)->first();
        
        if ($settings) {
            $settings->update([$field => $value]);
            
            $responseData = ['message' => 'Security setting updated.'];

            // ---------------------------------------------------------
            // REMEMBER THIS DEVICE LOGIC
            // ---------------------------------------------------------
            if ($field === 'remember_this_device') {
                if ($value === true) {
                    // Generate a secure 60-character token
                    $deviceToken = Str::random(60);
                    $agent = $request->userAgent();

                    // Simple parser for platform
                    $platform = 'Unknown';
                    if (preg_match('/windows|win32/i', $agent)) $platform = 'Windows';
                    elseif (preg_match('/macintosh|mac os x/i', $agent)) $platform = 'Mac';
                    elseif (preg_match('/linux/i', $agent)) $platform = 'Linux';
                    elseif (preg_match('/android/i', $agent)) $platform = 'Android';
                    elseif (preg_match('/iphone|ipad|ipod/i', $agent)) $platform = 'iOS';

                    // Simple parser for browser
                    $browser = 'Unknown';
                    if (preg_match('/MSIE|Trident/i', $agent)) $browser = 'Internet Explorer';
                    elseif (preg_match('/Edg/i', $agent)) $browser = 'Edge';
                    elseif (preg_match('/Firefox/i', $agent)) $browser = 'Firefox';
                    elseif (preg_match('/OPR/i', $agent)) $browser = 'Opera';
                    elseif (preg_match('/Chrome/i', $agent)) $browser = 'Chrome';
                    elseif (preg_match('/Safari/i', $agent)) $browser = 'Safari';
                    
                    // Store the trusted device with hashed token for database security
                    TrustedDevice::create([
                        'user_id' => $user->id,
                        'device_identifier' => hash('sha256', $deviceToken),
                        'ip_address' => $request->ip(),
                        'user_agent' => $agent,
                        'browser' => $browser,
                        'platform' => $platform,
                        'expires_at' => now()->addDays(30), // Trust for 30 days
                    ]);

                    // Send the raw token back to the frontend to store in localStorage
                    $responseData['device_token'] = $deviceToken;
                } else {
                    // If disabling, delete the specific device token if provided
                    if ($request->filled('device_token')) {
                        TrustedDevice::where('user_id', $user->id)
                            ->where('device_identifier', hash('sha256', $request->device_token))
                            ->delete();
                    }
                }
            }

            return response()->json($responseData);
        }

        return response()->json(['message' => 'Settings not found.'], 404);
    }

    // Send OTP for Recovery Email
    public function sendRecoveryOtp(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $action = $request->action; 
        
        $targetEmail = $action === 'enable' ? $request->recovery_email : $user->email;
        
        if ($action === 'enable') {
            $request->validate(['recovery_email' => 'required|email|unique:users,recovery_email']);
        }

        $otp = rand(100000, 999999);
        
        // Store OTP in cache for 10 minutes
        Cache::put('recovery_otp_' . $user->id, [
            'otp' => $otp,
            'recovery_email' => $request->recovery_email ?? null,
            'action' => $action
        ], now()->addMinutes(10));

        // Log it for local debugging
        Log::info("SP SECURITY OTP for User {$user->id} ({$targetEmail}): {$otp}");

        // Send the actual email
        try {
            Mail::raw("Your account security OTP is: {$otp}. It will expire in 10 minutes.", function ($message) use ($targetEmail) {
                $message->to($targetEmail)
                        ->subject('Account Recovery OTP - Security Settings');
            });
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send OTP email. Please check your SMTP configuration.'], 500);
        }

        return response()->json(['message' => 'OTP sent successfully. Check your email.']);
    }

    // Verify OTP and Update State
    public function verifyRecoveryOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string']);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $cachedData = Cache::get('recovery_otp_' . $user->id);

        if (!$cachedData || $cachedData['otp'] != $request->otp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        $settings = SecuritySetting::firstOrCreate(['user_id' => $user->id]);

        if ($cachedData['action'] === 'enable') {
            $user->recovery_email = $cachedData['recovery_email'];
            $user->save(); 
            
            $settings->update(['account_recovery_email' => true]);
            $msg = 'Recovery email successfully added and enabled.';
        } else {
            $user->recovery_email = null;
            $user->save(); 
            
            $settings->update(['account_recovery_email' => false]);
            $msg = 'Recovery email disabled and removed.';
        }

        Cache::forget('recovery_otp_' . $user->id);
        return response()->json(['message' => $msg]);
    }

    // Security Questions Handling
    public function updateSecurityQuestions(Request $request)
    {
        $user = Auth::user();
        $settings = SecuritySetting::firstOrCreate(['user_id' => $user->id]);

        if ($request->action === 'enable') {
            $request->validate(['answers' => 'required|array|size:5']);
            
            $questions = [
                "What was the first food you learned to cook?",
                "What was the name of your first pet?",
                "What was your first job?",
                "What was the name of your favorite childhood teacher?",
                "What nickname did you have as a child?"
            ];

            UserSecurityQuestion::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'question_1' => $questions[0], 'answer_1' => $request->answers[0],
                    'question_2' => $questions[1], 'answer_2' => $request->answers[1],
                    'question_3' => $questions[2], 'answer_3' => $request->answers[2],
                    'question_4' => $questions[3], 'answer_4' => $request->answers[3],
                    'question_5' => $questions[4], 'answer_5' => $request->answers[4],
                ]
            );

            $settings->update(['security_questions' => true]);
            return response()->json(['message' => 'Security questions saved successfully.']);
            
        } else {
            UserSecurityQuestion::where('user_id', $user->id)->delete();
            $settings->update(['security_questions' => false]);
            return response()->json(['message' => 'Security questions disabled and wiped.']);
        }
    }
}