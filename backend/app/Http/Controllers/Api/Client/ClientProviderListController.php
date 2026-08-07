<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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

        // 3. Fetch Provider Details
        $providers = User::whereIn('id', $allIds)
            ->where('role', 'service_provider')
            ->get();

        $data = $providers->map(function($provider) use ($clientId) {
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
                'favorite' => false,
                'recentProjects' => $transactedServices
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}