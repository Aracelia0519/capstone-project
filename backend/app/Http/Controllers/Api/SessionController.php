<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoginLog;
use App\Events\NewLoginLog;
use Laravel\Sanctum\PersonalAccessToken;

class SessionController extends Controller
{
    /**
     * Terminate the session (Used for Inactivity and Browser Close)
     */
    public function terminate(Request $request)
    {
        // For navigator.sendBeacon (Browser Close), headers aren't easily sent.
        // We catch the token from the query parameter if Bearer is missing.
        $token = $request->bearerToken() ?: $request->query('token');

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken) {
                $user = $accessToken->tokenable;
                
                if ($user) {
                    // Log the Auto-Logout event
                    $log = LoginLog::create([
                        'email' => $user->email,
                        'status' => 'Logout',
                        'browser' => $request->header('User-Agent'),
                        'failure_reason' => 'Auto-Logout (Inactivity / Browser Closed)',
                        'logged_in_at' => null,
                        'Fullname' => trim($user->first_name . ' ' . $user->last_name),
                        'role' => $user->role,
                    ]);
                    event(new NewLoginLog($log));
                }

                // Destroy the specific token from the database
                $accessToken->delete();
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Session securely terminated.']);
    }
}