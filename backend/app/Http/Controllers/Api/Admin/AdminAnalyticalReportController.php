<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticalReportController extends Controller
{
    /**
     * Get detailed tabular data for the Reports Data Hub
     */
    public function getReports(Request $request)
    {
        // 1. Users Data (Extended for View Modal)
        $users = User::select('id', 'first_name', 'last_name', 'email', 'phone', 'address', 'role', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'Not provided',
                    'address' => $user->address ?? 'Not provided',
                    'role' => ucwords(str_replace('_', ' ', $user->role)),
                    'status' => ucfirst($user->status),
                    'date' => Carbon::parse($user->created_at)->format('M d, Y h:i A'),
                    'raw_date' => $user->created_at,
                ];
            });

        // 2. Technical Reports Data (Extended for View Modal)
        $techReports = DB::table('technical_reports')
            ->join('users', 'technical_reports.user_id', '=', 'users.id')
            ->select(
                'technical_reports.id',
                'users.first_name',
                'users.last_name',
                'technical_reports.role',
                'technical_reports.category',
                'technical_reports.page',
                'technical_reports.device',
                'technical_reports.browser',
                'technical_reports.error_message',
                'technical_reports.status',
                'technical_reports.created_at'
            )
            ->orderByDesc('technical_reports.created_at')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'reporter' => trim($report->first_name . ' ' . $report->last_name),
                    'role' => ucwords(str_replace('_', ' ', $report->role)),
                    'category' => ucwords(str_replace('_', ' ', $report->category)),
                    'page' => $report->page,
                    'device' => $report->device,
                    'browser' => $report->browser,
                    'error_message' => $report->error_message,
                    'status' => ucfirst($report->status),
                    'date' => Carbon::parse($report->created_at)->format('M d, Y h:i A'),
                ];
            });

        // 3. User Reports Data (Extended for View Modal)
        $userReports = DB::table('user_reports')
            ->join('users as reporter', 'user_reports.reported_by_id', '=', 'reporter.id')
            ->join('users as reported', 'user_reports.reported_user_id', '=', 'reported.id')
            ->select(
                'user_reports.id',
                'reporter.first_name as reporter_first',
                'reporter.last_name as reporter_last',
                'user_reports.reporter_role',
                'reported.first_name as reported_first',
                'reported.last_name as reported_last',
                'user_reports.reported_user_role',
                'user_reports.reason',
                'user_reports.description',
                'user_reports.incident_date',
                'user_reports.status',
                'user_reports.created_at'
            )
            ->orderByDesc('user_reports.created_at')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'reporter' => trim($report->reporter_first . ' ' . $report->reporter_last) . ' (' . ucwords(str_replace('_', ' ', $report->reporter_role)) . ')',
                    'reported_user' => trim($report->reported_first . ' ' . $report->reported_last) . ' (' . ucwords(str_replace('_', ' ', $report->reported_user_role)) . ')',
                    'reason' => $report->reason,
                    'description' => $report->description,
                    'incident_date' => Carbon::parse($report->incident_date)->format('M d, Y'),
                    'status' => ucfirst($report->status),
                    'date' => Carbon::parse($report->created_at)->format('M d, Y h:i A'),
                ];
            });

        // Summary Counts
        $summary = [
            'total_users' => count($users),
            'total_tech_reports' => count($techReports),
            'total_user_reports' => count($userReports),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => $summary,
                'users' => $users,
                'tech_reports' => $techReports,
                'user_reports' => $userReports
            ]
        ]);
    }
}