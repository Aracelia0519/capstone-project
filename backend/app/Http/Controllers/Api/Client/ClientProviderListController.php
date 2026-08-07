<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserReport; // NEW IMPORT

class ClientProviderListController extends Controller
{
    public function index(Request $request)
    {
        $clientId = Auth::id();

        // 1. Get Provider IDs from Service Requests
        $requestProviders = DB::table('client_service_requests')
            ->where('client_id', $clientId)
            ->pluck('provider_id')
            ->toArray();

        // 2. Get Provider IDs from Messages (Chats)
        $messageSenders = DB::table('sp_messages')
            ->where('receiver_id', $clientId)
            ->pluck('sender_id')
            ->toArray();

        $messageReceivers = DB::table('sp_messages')
            ->where('sender_id', $clientId)
            ->pluck('receiver_id')
            ->toArray();

        // Merge and filter out the client's own ID
        $allIds = array_unique(array_merge($requestProviders, $messageSenders, $messageReceivers));
        $allIds = array_filter($allIds, function($id) use ($clientId) {
            return $id != $clientId;
        });

        // 3. Get the client's saved/favorite providers
        $favoriteProviderIds = DB::table('client_favorite_providers')
            ->where('client_id', $clientId)
            ->pluck('provider_id')
            ->toArray();

        // 4. Fetch Provider Details
        $providers = User::whereIn('id', $allIds)
            ->where('role', 'service_provider')
            ->get();

        $data = $providers->map(function($provider) use ($clientId, $favoriteProviderIds) {
            // Fetch Portfolio & Address
            $portfolio = DB::table('service_provider_portfolios')->where('provider_id', $provider->id)->first();
            $req = DB::table('service_provider_requirements')->where('user_id', $provider->id)->first();
            $address = $req ? DB::table('service_provider_addresses')->where('service_provider_requirements_id', $req->id)->first() : null;

            // Fetch Ratings & Reviews
            $reviews = DB::table('service_reviews')->where('provider_id', $provider->id);
            $avgRating = (float) $reviews->avg('rating') ?: 0;
            $reviewCount = $reviews->count();

            // Fetch Services transacted between this client and provider
            $transactedServices = DB::table('client_service_requests')
                ->join('service_offerings', 'client_service_requests.service_offering_id', '=', 'service_offerings.id')
                ->where('client_service_requests.client_id', $clientId)
                ->where('client_service_requests.provider_id', $provider->id)
                ->select('service_offerings.id', 'service_offerings.title as name')
                ->distinct()
                ->get();

            // Format Specialties
            $specialties = $portfolio && $portfolio->specialties 
                ? array_map('trim', explode(',', $portfolio->specialties)) 
                : ['Painting Professional'];

            return [
                'id' => $provider->id,
                'name' => trim($provider->first_name . ' ' . $provider->last_name) ?: 'Unknown Provider',
                'title' => 'Service Provider',
                'experience' => ($portfolio->experience_years ?? 0) . ' years',
                'rating' => round($avgRating, 1),
                'reviews' => $reviewCount,
                'specialties' => $specialties,
                'phone' => $provider->phone ?? 'Not provided',
                'email' => $provider->email,
                'location' => $address ? ($address->city . ', ' . $address->province) : 'Location unavailable',
                'status' => 'Available',
                'online' => true,
                'favorite' => in_array($provider->id, $favoriteProviderIds),
                'recentProjects' => $transactedServices
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function toggleFavorite(Request $request, $providerId)
    {
        $clientId = Auth::id();

        // Check if already favorited
        $exists = DB::table('client_favorite_providers')
            ->where('client_id', $clientId)
            ->where('provider_id', $providerId)
            ->exists();

        if ($exists) {
            // Un-favorite
            DB::table('client_favorite_providers')
                ->where('client_id', $clientId)
                ->where('provider_id', $providerId)
                ->delete();
            
            $isFavorite = false;
        } else {
            // Favorite
            DB::table('client_favorite_providers')->insert([
                'client_id' => $clientId,
                'provider_id' => $providerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $isFavorite = true;
        }

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Added to favorites.' : 'Removed from favorites.'
        ]);
    }

    // NEW: Function to submit a report against a user
    public function submitReport(Request $request, $reportedUserId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string',
            'incident_date' => 'required|date',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf,mp4|max:5120' // 5MB max
        ]);

        $reporter = Auth::user();

        // --- Limit reports to 3 per day ---
        $reportsToday = UserReport::where('reported_by_id', $reporter->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($reportsToday >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum limit of 3 reports per day. Please try again tomorrow.'
            ], 429); // 429 Too Many Requests
        }
        // ----------------------------------

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