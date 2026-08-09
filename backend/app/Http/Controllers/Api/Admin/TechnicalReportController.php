<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Models\TechnicalReport;
use App\Events\TechnicalReportUpdated;

class TechnicalReportController extends Controller
{
    public function index()
    {
        try {
            $reports = TechnicalReport::select(
                    'technical_reports.*', 
                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as user_name'),
                    'users.email'
                )
                ->leftJoin('users', 'technical_reports.user_id', '=', 'users.id')
                ->orderBy('technical_reports.created_at', 'desc')
                ->get()
                ->map(function ($report) {
                    if ($report->attachment && !str_starts_with($report->attachment, 'http')) {
                        $report->attachment = asset('storage/' . $report->attachment);
                    }
                    return $report;
                });

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reports: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statistics()
    {
        try {
            $total = TechnicalReport::count();
            $pending = TechnicalReport::where('status', 'pending')->count();
            $reviewed = TechnicalReport::where('status', 'reviewed')->count();
            // New resolved statistic variable
            $resolved = TechnicalReport::where('status', 'resolved')->count();

            $byCategory = TechnicalReport::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get();

            $byRole = TechnicalReport::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'reviewed' => $reviewed,
                    'resolved' => $resolved,
                    'by_category' => $byCategory,
                    'by_role' => $byRole
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        try {
            $report = TechnicalReport::findOrFail($id);
            $report->status = $request->status; 
            
            $report->reviewed_by = Auth::id(); 
            $report->save();

            // Fire event so the client is notified their issue was marked as reviewed/resolved
            event(new TechnicalReportUpdated($report));

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
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