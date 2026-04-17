<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SPDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $providerId = $user->id;
            
            $startOfMonth = Carbon::now()->startOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // 1. Total Clients & Growth
            $totalClients = DB::table('client_service_requests')->where('provider_id', $providerId)->distinct('client_id')->count('client_id');
            $lastMonthClients = DB::table('client_service_requests')->where('provider_id', $providerId)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->distinct('client_id')->count('client_id');
            $currentMonthClients = DB::table('client_service_requests')->where('provider_id', $providerId)->where('created_at', '>=', $startOfMonth)->distinct('client_id')->count('client_id');
            $clientGrowth = $lastMonthClients > 0 ? round((($currentMonthClients - $lastMonthClients) / $lastMonthClients) * 100) : ($currentMonthClients > 0 ? 100 : 0);

            // 2. Active & Completed Jobs
            $activeJobs = DB::table('official_deals')->where('provider_id', $providerId)->whereIn('status', ['pending', 'in_progress', 'approved'])->count();
            $completedJobs = DB::table('official_deals')->where('provider_id', $providerId)->where('status', 'completed')->count();
            $totalDeals = DB::table('official_deals')->where('provider_id', $providerId)->count();
            $jobCompletionRate = $totalDeals > 0 ? round(($completedJobs / $totalDeals) * 100) : 0;

            // 3. Satisfaction
            $satisfactionQuery = DB::table('service_reviews')->where('provider_id', $providerId);
            $satisfactionRaw = $satisfactionQuery->avg('rating');
            $satisfaction = $satisfactionRaw ? round(($satisfactionRaw / 5) * 100) : 0;

            // 4. Monthly Revenue
            $monthlyRevenue = DB::table('service_payment_transactions')->where('provider_id', $providerId)->where('status', 'completed')->where('created_at', '>=', $startOfMonth)->sum('amount');
            $lastMonthRevenue = DB::table('service_payment_transactions')->where('provider_id', $providerId)->where('status', 'completed')->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('amount');
            $revenueGrowth = $lastMonthRevenue > 0 ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) : ($monthlyRevenue > 0 ? 100 : 0);

            // 5. Recent Colors
            $recentColors = DB::table('service_provider_saved_colors')
                ->where('user_id', $providerId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($color) {
                    return [
                        'id' => $color->id,
                        'name' => $color->name,
                        'hex' => $color->hex_code,
                        'client' => 'General Library', // Fallbacks since colors aren't strictly bound to specific clients in the schema
                        'clientLocation' => 'N/A',
                        'project' => $color->color_family ?? 'Custom Mix',
                        'roomType' => 'Interior/Exterior',
                        'status' => 'Saved',
                        'date' => Carbon::parse($color->created_at)->format('M d, Y'),
                        'time' => Carbon::parse($color->created_at)->format('g:i A'),
                    ];
                });

            // 6. Recent Activities Synthesized Timeline
            $activities = [];
            
            // Recent Deals/Jobs
            $jobs = DB::table('official_deals')->where('provider_id', $providerId)->orderBy('updated_at', 'desc')->take(3)->get();
            foreach($jobs as $job) {
                $activities[] = [
                    'id' => 'job_'.$job->id,
                    'type' => 'job',
                    'title' => 'Service Job ' . ucfirst($job->status),
                    'description' => 'Service deal ID: ' . $job->id . ' has been updated.',
                    'time_raw' => $job->updated_at,
                    'time' => Carbon::parse($job->updated_at)->diffForHumans()
                ];
            }
            
            // Recent Payments
            $payments = DB::table('service_payment_transactions')->where('provider_id', $providerId)->where('status', 'completed')->orderBy('updated_at', 'desc')->take(3)->get();
            foreach($payments as $pay) {
                $activities[] = [
                    'id' => 'pay_'.$pay->id,
                    'type' => 'payment',
                    'title' => 'Payment Received',
                    'description' => '₱' . number_format($pay->amount, 2) . ' received via ' . $pay->payment_method,
                    'time_raw' => $pay->updated_at,
                    'time' => Carbon::parse($pay->updated_at)->diffForHumans()
                ];
            }

            // Recent Colors Mixed
            $colorsAct = DB::table('service_provider_saved_colors')->where('user_id', $providerId)->orderBy('created_at', 'desc')->take(3)->get();
            foreach($colorsAct as $color) {
                $activities[] = [
                    'id' => 'col_'.$color->id,
                    'type' => 'color',
                    'title' => 'New Color Created',
                    'description' => 'Saved "' . $color->name . '" to your palette library.',
                    'time_raw' => $color->created_at,
                    'time' => Carbon::parse($color->created_at)->diffForHumans()
                ];
            }
            
            // Sort by most recent
            usort($activities, function($a, $b) {
                return strtotime($b['time_raw']) - strtotime($a['time_raw']);
            });
            $recentActivities = array_slice($activities, 0, 5);

            // 7. Color Mixer Stats
            $totalColorsCreated = DB::table('service_provider_saved_colors')->where('user_id', $providerId)->count();

            return response()->json([
                'success' => true,
                'user' => [
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'initials' => strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1))
                ],
                'stats' => [
                    'totalClients' => $totalClients,
                    'clientGrowth' => $clientGrowth,
                    'activeJobs' => $activeJobs,
                    'completedJobs' => $completedJobs,
                    'satisfaction' => $satisfaction,
                    'monthlyRevenue' => $monthlyRevenue,
                    'revenueGrowth' => $revenueGrowth,
                    'jobCompletionRate' => $jobCompletionRate,
                ],
                'colorStats' => [
                    'totalCreated' => $totalColorsCreated,
                    'avgTime' => '5m' // Average mixer session estimate
                ],
                'recentColors' => $recentColors,
                'recentActivities' => $recentActivities
            ]);

        } catch (\Exception $e) {
            Log::error('SP Dashboard Error: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Failed to load dashboard.', 'error' => $e->getMessage()], 500);
        }
    }
}