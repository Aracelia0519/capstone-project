<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Models\User;
use App\Models\SystemNotification;
use App\Events\Notification\NotificationEvent; // <-- CORRECTED IMPORT
use Illuminate\Support\Facades\DB;

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
                ->orderBy('pending_reports', 'desc') // Prioritize users with pending reports
                ->orderBy('last_report_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reports: ' . $e->getMessage()
            ], 500);
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

            // Fetch detailed reports with reporter info
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

            // Analytics Data for Charts
            $reasonStats = [];
            $statusStats = ['pending' => 0, 'reviewed' => 0];

            foreach ($reports as $report) {
                // Tally Reasons
                if (!isset($reasonStats[$report->reason])) {
                    $reasonStats[$report->reason] = 0;
                }
                $reasonStats[$report->reason]++;

                // Tally Statuses
                if (isset($statusStats[$report->status])) {
                    $statusStats[$report->status]++;
                }
            }

            // Sort reasons by highest count
            arsort($reasonStats);

            return response()->json([
                'success' => true,
                'user' => $user,
                'reports' => $reports,
                'analytics' => [
                    'reasons' => $reasonStats,
                    'statuses' => $statusStats,
                    'total' => count($reports)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user reports: ' . $e->getMessage()
            ], 500);
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

            // --- REAL-TIME NOTIFICATION DISPATCH ---
            // If the status is newly updated to "reviewed", send a system notification to the reporter
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
                        'message' => "Your incident report regarding {$reportedName} (from {$incidentDate}) has been officially reviewed. The administrative team is now processing the appropriate actions. Thank you for your vigilance and for keeping our community safe.",
                        'is_read' => false,
                        'sender_role' => 'admin',
                        'receiver_role' => $report->reporter_role,
                    ]);

                    // Broadcast directly to the specific user's private notification channel using the correct Event Class
                    broadcast(new NotificationEvent($notification)); // <-- CORRECTED DISPATCH
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Report status updated to ' . ucfirst($request->status),
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}