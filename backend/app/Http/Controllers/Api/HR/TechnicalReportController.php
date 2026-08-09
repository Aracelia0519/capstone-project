<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\TechnicalReport;
use App\Events\TechnicalReportSubmitted;

class TechnicalReportController extends Controller
{
    public function index()
    {
        try {
            $reports = TechnicalReport::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'required|in:bug,system_error,login_issue,payment_issue,order_issue,inventory_issue,performance_issue,display_issue,security_issue,other',
                'page' => 'required|string|max:255',
                'device' => 'required|string|max:255',
                'browser' => 'required|string|max:255',
                'error_message' => 'required|string',
                'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // Max 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = 'tech_report_' . Auth::id() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $attachmentPath = $file->storeAs('technical_reports', $filename, 'public');
            }

            $report = TechnicalReport::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role,
                'category' => $request->category,
                'page' => $request->page,
                'device' => $request->device,
                'browser' => $request->browser,
                'error_message' => $request->error_message,
                'attachment' => $attachmentPath,
                'status' => 'pending'
            ]);

            // Broadcast event to admin
            event(new TechnicalReportSubmitted($report));

            return response()->json([
                'success' => true,
                'message' => 'Technical report submitted successfully!',
                'data' => $report
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report: ' . $e->getMessage()
            ], 500);
        }
    }
}