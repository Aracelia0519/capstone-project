<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserReport; 

class ClientListController extends Controller
{
    public function index(Request $request)
    {
        $providerId = Auth::id();

        // 1. Get Client IDs from Service Requests
        $requestClients = DB::table('client_service_requests')
            ->where('provider_id', $providerId)
            ->pluck('client_id')
            ->toArray();

        // 2. Get Client IDs from Messages (Chats)
        $messageSenders = DB::table('sp_messages')
            ->where('receiver_id', $providerId)
            ->pluck('sender_id')
            ->toArray();

        $messageReceivers = DB::table('sp_messages')
            ->where('sender_id', $providerId)
            ->pluck('receiver_id')
            ->toArray();

        // Merge and filter out the provider's own ID
        $allIds = array_unique(array_merge($requestClients, $messageSenders, $messageReceivers));
        $allIds = array_filter($allIds, function($id) use ($providerId) {
            return $id != $providerId;
        });

        // 3. Fetch Client Details
        $clients = User::whereIn('id', $allIds)
            ->where('role', 'client')
            ->get();

        $data = $clients->map(function($client) use ($providerId) {
            // Fetch Address (if they have set one up via requirements)
            $req = DB::table('client_requirements')->where('user_id', $client->id)->first();
            $address = $req ? DB::table('client_addresses')->where('client_requirements_id', $req->id)->first() : null;

            // Fetch Services transacted between this client and provider
            $transactedServices = DB::table('client_service_requests')
                ->join('service_offerings', 'client_service_requests.service_offering_id', '=', 'service_offerings.id')
                ->where('client_service_requests.client_id', $client->id)
                ->where('client_service_requests.provider_id', $providerId)
                ->select(
                    'service_offerings.id', 
                    'service_offerings.title as name', 
                    'client_service_requests.status',
                    'client_service_requests.created_at as date'
                )
                ->get();

            return [
                'id' => $client->id,
                'name' => trim($client->first_name . ' ' . $client->last_name) ?: 'Unknown Client',
                'email' => $client->email,
                'phone' => $client->phone ?? 'Not provided',
                'address' => $address ? ($address->city . ', ' . $address->province) : 'Location unavailable',
                'status' => 'active',
                'jobCount' => $transactedServices->count(),
                'recentProjects' => $transactedServices
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->values()
        ]);
    }

    public function submitReport(Request $request, $reportedUserId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120' // 5MB max
        ]);

        $reporter = Auth::user();

        // --- NEW: Limit reports to 3 per day ---
        $reportsToday = UserReport::where('reported_by_id', $reporter->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($reportsToday >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum limit of 3 reports per day. Please try again tomorrow.'
            ], 429); // 429 Too Many Requests
        }
        // ----------------------------------------

        $reportedUser = User::find($reportedUserId);

        if (!$reportedUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('reports/evidence', 'public');
        }

        $report = UserReport::create([
            'reported_user_id' => $reportedUser->id,
            'reported_by_id' => $reporter->id,
            'reporter_role' => $reporter->role,
            'reported_user_role' => $reportedUser->role,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'incident_date' => $validated['incident_date'],
            'evidence_path' => $evidencePath,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully. Administrators will review the incident.'
        ]);
    }
}