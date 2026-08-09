<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Models\User;
use App\Models\AccountTermination;
use App\Models\SystemNotification;
use App\Events\Notification\NotificationEvent; 
use App\Events\Account\AccountStatusUpdated; // NEW EVENT IMPORTED
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminReportController extends Controller
{
    /**
     * Get a summary of all reported users.
     */
    public function index(Request $request)
    {
        try {
            // Group the reports by the reported user to show an aggregated list
            $summary = DB::table('user_reports')
                ->join('users', 'user_reports.reported_user_id', '=', 'users.id')
                ->select(
                    'user_reports.reported_user_id',
                    'user_reports.reported_user_role',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    DB::raw('COUNT(user_reports.id) as total_reports'),
                    DB::raw('SUM(CASE WHEN user_reports.status = "pending" THEN 1 ELSE 0 END) as pending_reports'),
                    DB::raw('SUM(CASE WHEN user_reports.status = "reviewed" THEN 1 ELSE 0 END) as reviewed_reports'),
                    DB::raw('MAX(user_reports.created_at) as last_report_date')
                )
                ->groupBy('user_reports.reported_user_id', 'user_reports.reported_user_role', 'users.first_name', 'users.last_name', 'users.email')
                ->orderBy('pending_reports', 'desc') 
                ->orderBy('last_report_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch reports: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get specific details and all reports for a single reported user.
     */
    public function show(int $userId)
    {
        try {
            $user = User::select('id', 'first_name', 'last_name', 'email', 'role', 'status')->find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            // Fetch the termination status
            $termination = AccountTermination::where('account_id', $userId)
                ->orderBy('id', 'desc')
                ->first();

            // Fetch detailed reports
            $reports = DB::table('user_reports')
                ->join('users as reporters', 'user_reports.reported_by_id', '=', 'reporters.id')
                ->select(
                    'user_reports.*', 
                    'reporters.first_name as reporter_first_name', 
                    'reporters.last_name as reporter_last_name', 
                    'reporters.email as reporter_email'
                )
                ->where('user_reports.reported_user_id', $userId)
                ->orderBy('user_reports.created_at', 'desc')
                ->get()
                ->map(function($report) {
                    $report->evidence_url = $report->evidence_path ? asset('storage/' . $report->evidence_path) : null;
                    return $report;
                });

            $reasonStats = [];
            $statusStats = ['pending' => 0, 'reviewed' => 0];

            foreach ($reports as $report) {
                if (!isset($reasonStats[$report->reason])) $reasonStats[$report->reason] = 0;
                $reasonStats[$report->reason]++;
                if (isset($statusStats[$report->status])) $statusStats[$report->status]++;
            }
            arsort($reasonStats);

            return response()->json([
                'success' => true,
                'user' => $user,
                'reports' => $reports,
                'termination' => $termination,
                'analytics' => [
                    'reasons' => $reasonStats,
                    'statuses' => $statusStats,
                    'total' => count($reports)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch user reports: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the status of an individual report.
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed'
        ]);

        try {
            $report = UserReport::findOrFail($id);
            $oldStatus = $report->status;
            
            $report->status = $request->status;
            $report->save();

            if ($oldStatus === 'pending' && $request->status === 'reviewed') {
                $reporter = User::find($report->reported_by_id);
                $reportedUser = User::find($report->reported_user_id);

                if ($reporter && $reportedUser) {
                    $reportedName = $reportedUser->first_name . ' ' . $reportedUser->last_name;
                    $incidentDate = date('M d, Y', strtotime($report->incident_date));

                    $notification = SystemNotification::create([
                        'receiver_id' => $reporter->id,
                        'type' => 'Success', 
                        'title' => 'Incident Report Reviewed',
                        'message' => "Your incident report regarding {$reportedName} (from {$incidentDate}) has been officially reviewed. The administrative team is now processing the appropriate actions.",
                        'is_read' => false,
                        'sender_role' => 'admin',
                        'receiver_role' => $report->reporter_role,
                    ]);

                    broadcast(new NotificationEvent($notification)); 
                }
            }

            return response()->json(['success' => true, 'message' => 'Report status updated.', 'data' => $report]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }

    /**
     * Send an official warning to the reported user.
     */
    public function sendWarning(Request $request, int $userId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:10240' // Max 10MB
        ]);

        try {
            $user = User::findOrFail($userId);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('notifications/attachments', 'public');
            }

            // Log pending termination state
            $termination = AccountTermination::firstOrCreate([
                'account_id' => $user->id,
                'status' => 'pending',
            ], [
                'role' => $user->role
            ]);

            $notification = SystemNotification::create([
                'receiver_id' => $user->id,
                'type' => 'Warning', 
                'title' => 'Official Admin Warning',
                'message' => $request->message,
                'attachment' => $attachmentPath,
                'is_read' => false,
                'sender_role' => 'admin',
                'receiver_role' => $user->role,
            ]);

            broadcast(new NotificationEvent($notification));

            return response()->json(['success' => true, 'message' => 'Warning sent successfully.', 'termination' => $termination]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send warning: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Terminate the Account
     */
    public function terminateAccount(Request $request, int $userId)
    {
        $request->validate([
            'termination_type' => 'required|string',
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:10240'
        ]);

        try {
            $user = User::findOrFail($userId);
            $termination = AccountTermination::where('account_id', $userId)->where('status', 'pending')->first();

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('notifications/attachments', 'public');
            }

            $terminationLabels = [
                'voluntary' => 'Voluntary Closure',
                'admin_action' => 'Administrative Action',
                'policy_violation' => 'Policy Violation',
                'fraud' => 'Fraud / Deceptive Activity',
                'abuse' => 'Abuse / Harassment',
                'security_violation' => 'Security Violation',
                'multiple_reports' => 'Multiple Valid Reports',
                'inactive' => 'Inactive Account',
                'duplicate_account' => 'Duplicate Account',
                'business_closure' => 'Business Closure',
                'other' => 'Other Reason',
            ];
            
            $readableType = $terminationLabels[$request->termination_type] ?? $request->termination_type;

            if($termination) {
                $termination->update([
                    'status' => 'terminated',
                    'terminated_by' => Auth::id() ?? 1, 
                    'termination_type' => $request->termination_type,
                    'reason' => $request->reason,
                    'terminated_at' => now()
                ]);
            }

            // NOTE: The 'users' table structure and status column are intentionally left UNTOUCHED.
            // Control is purely handled via the AccountTermination log.

            $notification = SystemNotification::create([
                'receiver_id' => $user->id,
                'type' => 'Alert', 
                'title' => 'Account Terminated',
                'message' => "Your account has been officially terminated.\nType: {$readableType}.\nReason: {$request->reason}.",
                'attachment' => $attachmentPath,
                'is_read' => false,
                'sender_role' => 'admin',
                'receiver_role' => $user->role,
            ]);

            broadcast(new NotificationEvent($notification));

            // BROADCAST DYNAMIC STATUS UPDATE
            broadcast(new AccountStatusUpdated($user->id, 'terminated', $termination));

            return response()->json(['success' => true, 'message' => 'Account terminated.', 'termination' => $termination]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to terminate account: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reverse the Termination
     */
    public function reverseTermination(Request $request, int $userId)
    {
        $request->validate([
            'reversal_reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:10240'
        ]);

        try {
            $user = User::findOrFail($userId);
            $termination = AccountTermination::where('account_id', $userId)->where('status', 'terminated')->first();

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('notifications/attachments', 'public');
            }

            if($termination) {
                $termination->update([
                    'status' => 'reversed',
                    'reversed_by' => Auth::id() ?? 1,
                    'reversal_reason' => $request->reversal_reason,
                    'reversed_at' => now()
                ]);
            }

            // NOTE: The 'users' table structure and status column are intentionally left UNTOUCHED.
            
            $notification = SystemNotification::create([
                'receiver_id' => $user->id,
                'type' => 'Success', 
                'title' => 'Account Restored',
                'message' => "Your account termination has been officially reversed. Reason: {$request->reversal_reason}. You may now resume standard activities.",
                'attachment' => $attachmentPath,
                'is_read' => false,
                'sender_role' => 'admin',
                'receiver_role' => $user->role,
            ]);

            broadcast(new NotificationEvent($notification));

            // BROADCAST DYNAMIC STATUS UPDATE
            broadcast(new AccountStatusUpdated($user->id, 'reversed', $termination));

            return response()->json(['success' => true, 'message' => 'Termination reversed.', 'termination' => $termination]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reverse termination: ' . $e->getMessage()], 500);
        }
    }
}