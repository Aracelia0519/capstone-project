<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Models\SystemNotification;
use App\Events\Notification\NotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginLogController extends Controller
{
    /**
     * Fetch all login logs.
     * Orders by the most recent first.
     */
    public function index()
    {
        try {
            $logs = LoginLog::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch audit logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Security Notification to user or their handling distributor/supplier
     */
    public function sendSecurityNotification(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Verify the user actually exists in the database
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'User not found. The attacker was likely guessing an invalid email address.'
                ], 404);
            }

            $receiverId = $user->id;
            $receiverRole = $user->role;

            // Check if max 2 notifications have already been sent today for this receiver
            $todayCount = SystemNotification::where('receiver_id', $receiverId)
                ->where('title', 'Security Alert: Possible Brute Force Attack')
                ->whereDate('created_at', today())
                ->count();

            if ($todayCount >= 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Daily notification limit reached (maximum 2 security alerts per day for this account).'
                ], 422);
            }

            // Route notifications to handling Distributors for their sub-accounts
            if (in_array($user->role, ['employee', 'operational_distributor', 'hr_manager', 'finance_manager'])) {
                $profile = null;
                if ($user->role === 'employee') {
                    $profile = DB::table('hr_employees')->where('user_id', $user->id)->first();
                } elseif ($user->role === 'operational_distributor') {
                    $profile = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                } elseif ($user->role === 'hr_manager') {
                    $profile = DB::table('hr_managers')->where('user_id', $user->id)->first();
                } elseif ($user->role === 'finance_manager') {
                    $profile = DB::table('finance_managers')->where('user_id', $user->id)->first();
                }

                if ($profile && $profile->parent_distributor_id) {
                    $receiverId = $profile->parent_distributor_id;
                    $receiverRole = 'distributor';
                }
            } 
            // Route notifications to handling Suppliers for their sub-accounts
            elseif (in_array($user->role, ['supplier_employee', 'personnel_officer'])) {
                $profile = null;
                if ($user->role === 'supplier_employee') {
                    $profile = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
                } elseif ($user->role === 'personnel_officer') {
                    $profile = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
                }

                if ($profile && $profile->supplier_id) {
                    $receiverId = $profile->supplier_id;
                    $receiverRole = 'supplier';
                }
            }

            $message = "Security Notice: We have detected unusual login activity or a potential unauthorized access attempt targeting the account associated with {$user->email}. If you did not initiate this activity, please secure your account by updating your password immediately and enabling necessary security settings. If you recognize this activity, no further action is required.";

            // Save to database
            $notification = SystemNotification::create([
                'type' => 'Warning',
                'title' => 'Security Alert: Possible Brute Force Attack',
                'message' => $message,
                'is_read' => 0,
                'sender_id' => \Illuminate\Support\Facades\Auth::id(),
                'receiver_id' => $receiverId,
                'sender_role' => 'admin',
                'receiver_role' => $receiverRole,
            ]);

            // Broadcast real-time event
            event(new NotificationEvent($notification));

            return response()->json([
                'status' => 'success', 
                'message' => 'Security notification successfully sent.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}