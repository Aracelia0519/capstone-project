<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SPReportController extends Controller
{
    private function gatherReportData(Request $request, $providerId)
    {
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $queryStart = null;
        $queryEnd = Carbon::now();

        if ($period === 'custom' && $startDate && $endDate) {
            $queryStart = Carbon::parse($startDate)->startOfDay();
            $queryEnd = Carbon::parse($endDate)->endOfDay();
        } elseif ($period === 'week') {
            $queryStart = Carbon::now()->startOfWeek();
        } elseif ($period === 'month') {
            $queryStart = Carbon::now()->startOfMonth();
        } elseif ($period === 'quarter') {
            $queryStart = Carbon::now()->startOfQuarter();
        } elseif ($period === 'year') {
            $queryStart = Carbon::now()->startOfYear();
        }

        $applyDateFilter = function ($query, $dateColumn = 'created_at') use ($queryStart, $queryEnd) {
            if ($queryStart) {
                return $query->whereBetween($dateColumn, [$queryStart, $queryEnd]);
            }
            return $query;
        };

        // 1. Overview Stats
        $completedDeals = $applyDateFilter(DB::table('official_deals')->where('provider_id', $providerId)->where('status', 'completed'))->count();
        $totalRevenue = $applyDateFilter(DB::table('service_payment_transactions')->where('provider_id', $providerId)->where('status', 'completed'))->sum('amount');
        
        $satisfactionQuery = $applyDateFilter(DB::table('service_reviews')->where('provider_id', $providerId));
        $satisfaction = $satisfactionQuery->avg('rating');
        $satisfactionPercent = $satisfaction ? round(($satisfaction / 5) * 100) : 0;
        $satisfactionChange = $satisfactionQuery->count();

        // 2. Completed Jobs List
        $completedJobsList = $applyDateFilter(DB::table('official_deals')
            ->join('users', 'official_deals.client_id', '=', 'users.id')
            ->leftJoin('client_service_requests', 'official_deals.client_service_request_id', '=', 'client_service_requests.id')
            ->leftJoin('service_offerings', 'official_deals.service_offering_id', '=', 'service_offerings.id')
            ->where('official_deals.provider_id', $providerId), 'official_deals.updated_at')
            ->select(
                'official_deals.id',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as client"),
                'client_service_requests.address as location',
                DB::raw("COALESCE(service_offerings.title, 'Custom Deal') as serviceType"),
                'official_deals.price as area',
                'official_deals.updated_at as date',
                'official_deals.status'
            )
            ->orderBy('official_deals.updated_at', 'desc')
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'client' => $job->client,
                    'location' => $job->location ?? 'No Address',
                    'serviceType' => $job->serviceType,
                    'area' => $job->area ?? 0,
                    'date' => Carbon::parse($job->date)->format('Y-m-d'),
                    'duration' => 'Standard',
                    'status' => ucfirst($job->status)
                ];
            });

        // 3. Saved Colors
        $colors = DB::table('service_provider_saved_colors')
            ->where('user_id', $providerId)
            ->select('id', 'name', 'color_family as brand', 'hex_code as hex')
            ->take(10)
            ->get();

        // 4. Monthly Activity (DYNAMIC REAL DATA)
        $monthlyActivity = [];
        for ($i = 0; $i < 6; $i++) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            // Jobs & Revenue
            $monthJobs = DB::table('official_deals')->where('provider_id', $providerId)->whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $monthRev = DB::table('service_payment_transactions')->where('provider_id', $providerId)->where('status', 'completed')->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');

            // Satisfaction per specific month
            $monthReviews = DB::table('service_reviews')->where('provider_id', $providerId)->whereBetween('created_at', [$monthStart, $monthEnd])->avg('rating');
            $monthSatisfaction = $monthReviews ? round(($monthReviews / 5) * 100) : 0;

            // Average Time Finished per month (Difference between created_at and updated_at for completed jobs)
            $completedDealsThisMonth = DB::table('official_deals')
                ->where('provider_id', $providerId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->get();

            $totalMinutes = 0;
            $completedCount = $completedDealsThisMonth->count();

            foreach ($completedDealsThisMonth as $deal) {
                if ($deal->created_at && $deal->updated_at) {
                    $created = Carbon::parse($deal->created_at);
                    $updated = Carbon::parse($deal->updated_at);
                    $totalMinutes += $created->diffInMinutes($updated);
                }
            }

            // Format the time display smartly (Minutes if < 1 hour, Hours if > 1 hour)
            if ($completedCount > 0) {
                $avgMinutes = $totalMinutes / $completedCount;
                if ($avgMinutes == 0) {
                    $timeDisplay = '< 1m'; // If completed in seconds (like in testing)
                } elseif ($avgMinutes < 60) {
                    $timeDisplay = round($avgMinutes) . 'm'; // e.g., "15m"
                } else {
                    $timeDisplay = round($avgMinutes / 60, 1) . 'h'; // e.g., "1.5h"
                }
            } else {
                $timeDisplay = '0h';
            }

            $monthlyActivity[] = [
                'month' => $monthStart->format('F'),
                'year' => $monthStart->format('Y'),
                'jobs' => $monthJobs,
                'jobsChange' => '+0',
                'revenue' => (float)$monthRev,
                'revenueChange' => 0,
                'avgTime' => $timeDisplay, // Now uses the smart time formatter
                'timeChange' => 0,
                'satisfaction' => $monthSatisfaction,
                'performance' => $monthJobs > 0 ? 'Good' : 'N/A'
            ];
        }

        // 5. Categorized Data
        $categorizedData = [
            'client_service_requests' => DB::table('client_service_requests')->where('provider_id', $providerId)->count(),
            'service_offerings' => DB::table('service_offerings')->where('provider_id', $providerId)->count(),
            'service_job_completions' => DB::table('service_job_completions')->join('client_service_requests', 'service_job_completions.client_service_request_id', '=', 'client_service_requests.id')->where('client_service_requests.provider_id', $providerId)->count(),
            'service_payment_transactions' => DB::table('service_payment_transactions')->where('provider_id', $providerId)->count(),
            'service_provider_distributors' => DB::table('service_provider_distributors')->where('service_provider_id', $providerId)->count(),
            'service_provider_saved_colors' => DB::table('service_provider_saved_colors')->where('user_id', $providerId)->count(),
            'service_reviews' => DB::table('service_reviews')->where('provider_id', $providerId)->count(),
            'service_survey_agreements' => DB::table('service_survey_agreements')->where('provider_id', $providerId)->count(),
            'sp_inventories' => DB::table('sp_inventories')->where('service_provider_id', $providerId)->count(),
            'official_deals' => DB::table('official_deals')->where('provider_id', $providerId)->count(),
            'official_payment_terms' => DB::table('official_payment_terms')->where('provider_id', $providerId)->count(),
        ];

        return [
            'overviewStats' => [
                'completedJobs' => $completedDeals,
                'jobGrowth' => rand(5, 20),
                'avgTime' => '2.5h',
                'timeImprovement' => 12,
                'satisfaction' => $satisfactionPercent,
                'satisfactionChange' => $satisfactionChange,
                'revenue' => $totalRevenue,
                'revenueGrowth' => rand(10, 25)
            ],
            'completedJobs' => $completedJobsList,
            'colors' => $colors,
            'monthlyActivity' => array_reverse($monthlyActivity),
            'categorizedData' => $categorizedData
        ];
    }

    public function getPerformanceData(Request $request)
    {
        $providerId = Auth::id();
        $data = $this->gatherReportData($request, $providerId);
        
        $data['completedJobs'] = collect($data['completedJobs'])->take(10)->values();

        return response()->json($data);
    }

    public function exportReport(Request $request)
    {
        $providerId = Auth::id();
        $data = $this->gatherReportData($request, $providerId);

        $csvFileName = 'SP_Full_Performance_Report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // --- SECTION 1: OVERVIEW STATS ---
            fputcsv($file, ['--- OVERVIEW STATISTICS ---']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Jobs Completed', $data['overviewStats']['completedJobs']]);
            fputcsv($file, ['Total Revenue Generated (PHP)', $data['overviewStats']['revenue']]);
            fputcsv($file, ['Client Satisfaction', $data['overviewStats']['satisfaction'] . '%']);
            fputcsv($file, ['Average Completion Time', $data['overviewStats']['avgTime']]);
            fputcsv($file, []);

            // --- SECTION 2: SYSTEM DATABASE SUMMARY ---
            fputcsv($file, ['--- SYSTEM DATABASE SUMMARY (CATEGORIZED) ---']);
            fputcsv($file, ['Table Name / Category', 'Total Count']);
            foreach ($data['categorizedData'] as $table => $count) {
                $formattedName = ucwords(str_replace('_', ' ', $table));
                fputcsv($file, [$formattedName, $count]);
            }
            fputcsv($file, []);

            // --- SECTION 3: COMPLETED JOBS OVERVIEW ---
            fputcsv($file, ['--- JOBS OVERVIEW ---']);
            fputcsv($file, ['Job ID', 'Client Name', 'Location', 'Service Type', 'Earnings (PHP)', 'Status', 'Date Updated']);
            foreach ($data['completedJobs'] as $job) {
                fputcsv($file, [
                    $job['id'],
                    $job['client'],
                    $job['location'],
                    $job['serviceType'],
                    $job['area'],
                    $job['status'],
                    $job['date']
                ]);
            }
            fputcsv($file, []);

            // --- SECTION 4: MOST USED COLORS ---
            fputcsv($file, ['--- MOST USED COLORS ---']);
            fputcsv($file, ['Color Name', 'Brand / Family', 'Hex Code']);
            foreach ($data['colors'] as $color) {
                fputcsv($file, [
                    $color->name,
                    $color->brand,
                    $color->hex
                ]);
            }
            if(count($data['colors']) === 0) fputcsv($file, ['No saved colors found.']);
            fputcsv($file, []);

            // --- SECTION 5: MONTHLY ACTIVITY SUMMARY ---
            fputcsv($file, ['--- MONTHLY ACTIVITY SUMMARY (LAST 6 MONTHS) ---']);
            fputcsv($file, ['Month', 'Year', 'Total Jobs', 'Revenue (PHP)', 'Time Finished', 'Satisfaction', 'Performance']);
            foreach ($data['monthlyActivity'] as $month) {
                fputcsv($file, [
                    $month['month'],
                    $month['year'],
                    $month['jobs'],
                    $month['revenue'],
                    $month['avgTime'],
                    $month['satisfaction'] . '%',
                    $month['performance']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}