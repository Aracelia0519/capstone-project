<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard data
     */
    public function adminDashboard(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            // Get statistics
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'clients' => User::where('role', 'client')->where('status', 'active')->count(),
                'distributors' => User::where('role', 'distributor')->where('status', 'active')->count(),
                'service_providers' => User::where('role', 'service_provider')->where('status', 'active')->count(),
                'admins' => User::where('role', 'admin')->where('status', 'active')->count(),
            ];

            // Get recent users
            $recentUsers = User::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ];
                });

            // Get user growth data (last 30 days)
            $userGrowth = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'recent_users' => $recentUsers,
                    'user_growth' => $userGrowth,
                    'user' => [
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at->format('F j, Y')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get distributor dashboard data
     */
    public function distributorDashboard(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isDistributor()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            // Mock data for distributor dashboard
            $stats = [
                'total_orders' => 156,
                'pending_orders' => 12,
                'completed_orders' => 144,
                'total_sales' => 125000.50,
                'monthly_sales' => 25000.75,
                'inventory_items' => 45,
                'low_stock_items' => 8,
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'user' => [
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service provider dashboard data
     */
    public function serviceProviderDashboard(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isServiceProvider()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            // Mock data for service provider dashboard
            $stats = [
                'total_jobs' => 89,
                'active_jobs' => 15,
                'completed_jobs' => 74,
                'total_clients' => 42,
                'monthly_income' => 35000.25,
                'pending_requests' => 7,
                'rating' => 4.8,
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'user' => [
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client dashboard data
     */
    public function clientDashboard(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isClient()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        try {
            // Mock data for client dashboard
            $stats = [
                'total_requests' => 23,
                'active_requests' => 3,
                'completed_requests' => 20,
                'favorite_colors' => 8,
                'total_spent' => 15000.75,
                'service_providers' => 5,
                'upcoming_appointments' => 2,
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stats' => $stats,
                    'user' => [
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'address' => $user->address
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}