<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: Check if account exists and return masked email options
     */
    public function checkAccount(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // We return a generic success-like response for security to prevent email enumeration,
            // or return an error if you strictly want to let them know. Let's return error for UX.
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with this email address.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'emails' => [
                'primary' => $this->maskEmail($user->email),
                'recovery' => $user->recovery_email ? $this->maskEmail($user->recovery_email) : null,
            ]
        ]);
    }

    /**
     * Step 2: Send OTP to chosen email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'target_type' => 'required|in:primary,recovery'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Account not found.'], 404);
        }

        $targetEmail = $request->target_type === 'recovery' ? $user->recovery_email : $user->email;

        if (!$targetEmail) {
            return response()->json(['status' => 'error', 'message' => 'Selected email destination is unavailable.'], 400);
        }

        $otp = rand(100000, 999999);
        
        // Cache OTP for 10 minutes
        Cache::put('pwd_reset_otp_' . $user->id, $otp, now()->addMinutes(10));

        Log::info("FORGOT PASSWORD OTP for User {$user->id} ({$targetEmail}): {$otp}");

        try {
            Mail::raw("Your CaviteGo Paint password reset verification code is: {$otp}. It will expire in 10 minutes.", function ($message) use ($targetEmail) {
                $message->to($targetEmail)->subject('Password Reset Code - CaviteGo Paint');
            });
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to send OTP email. Please check your SMTP configuration.'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP sent successfully. Please check your inbox.']);
    }

    /**
     * Step 3: Verify OTP and generate a secure reset token
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Account not found.'], 404);
        }

        $cachedOtp = Cache::get('pwd_reset_otp_' . $user->id);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired verification code.'], 401);
        }

        // OTP is valid. Clear it and generate a temporary reset token.
        Cache::forget('pwd_reset_otp_' . $user->id);
        
        $resetToken = Str::random(60);
        Cache::put('pwd_reset_token_' . $user->id, $resetToken, now()->addMinutes(15)); // 15 mins to reset password

        return response()->json([
            'status' => 'success', 
            'message' => 'OTP verified.',
            'reset_token' => $resetToken
        ]);
    }

    /**
     * Step 4: Reset the Password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Account not found.'], 404);
        }

        $cachedToken = Cache::get('pwd_reset_token_' . $user->id);

        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired reset session. Please try again.'], 401);
        }

        // Update password (Model handles hashing, so we just pass the raw value)
        $user->password = $request->password;
        $user->save();

        // Clear the token and invalidate all active sessions for security
        Cache::forget('pwd_reset_token_' . $user->id);
        $user->tokens()->delete();

        return response()->json(['status' => 'success', 'message' => 'Password reset successfully. You can now log in.']);
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
}