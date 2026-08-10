<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminAnalyticalDashboardController extends Controller
{
    /**
     * Get purely analytical data for the Admin Dashboard.
     */
    public function getAnalytics(Request $request)
    {
        // Calculate dynamic trend for users
        $usersThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // 1. Top Level KPIs
        $kpis = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'users_this_month' => $usersThisMonth, // Added for frontend trend badge
            'pending_tech_reports' => DB::table('technical_reports')->where('status', 'pending')->count(),
            'pending_user_reports' => DB::table('user_reports')->where('status', 'pending')->count(),
        ];

        // 2. User Growth Trend (Last 6 Months)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $userGrowthRaw = User::select(
                DB::raw('COUNT(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%b') as month"),
                DB::raw("MAX(created_at) as sort_date")
            )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('sort_date')
            ->get();

        $userGrowth = [
            'labels' => $userGrowthRaw->pluck('month'),
            'data' => $userGrowthRaw->pluck('count'),
        ];

        // 3. User Distribution by Role
        $usersByRoleRaw = User::select('role', DB::raw('COUNT(id) as count'))
            ->groupBy('role')
            ->orderByDesc('count')
            ->get();

        $usersByRole = [
            'labels' => $usersByRoleRaw->pluck('role')->map(fn($role) => ucwords(str_replace('_', ' ', $role))),
            'data' => $usersByRoleRaw->pluck('count'),
        ];

        // 4. Technical Reports Breakdown (by Category)
        $techReportsRaw = DB::table('technical_reports')
            ->select('category', DB::raw('COUNT(id) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        $techReports = [
            'labels' => $techReportsRaw->pluck('category')->map(fn($cat) => ucwords(str_replace('_', ' ', $cat))),
            'data' => $techReportsRaw->pluck('count'),
        ];

        // 5. User Reports Breakdown (by Reason)
        $userReportsRaw = DB::table('user_reports')
            ->select('reason', DB::raw('COUNT(id) as count'))
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        $userReports = [
            'labels' => $userReportsRaw->pluck('reason'),
            'data' => $userReportsRaw->pluck('count'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'kpis' => $kpis,
                'charts' => [
                    'user_growth' => $userGrowth,
                    'users_by_role' => $usersByRole,
                    'tech_reports' => $techReports,
                    'user_reports' => $userReports,
                ]
            ]
        ]);
    }
}