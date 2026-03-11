<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceProvider\ServiceOffering;
use App\Models\EcommerceClient\ClientServiceRequest;

class ClientServiceController extends Controller
{
    public function getServices(Request $request)
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        // Fetch visible reviews grouped by service_offering_id including client_id and client_reply
        $reviews = DB::table('service_reviews')
            ->join('client_service_requests', 'service_reviews.client_service_request_id', '=', 'client_service_requests.id')
            ->join('users', 'service_reviews.client_id', '=', 'users.id')
            ->where('service_reviews.is_hidden', false)
            ->select(
                'service_reviews.id',
                'service_reviews.client_id',
                'service_reviews.rating',
                'service_reviews.comment',
                'service_reviews.reply',
                'service_reviews.client_reply',
                'service_reviews.created_at',
                'client_service_requests.service_offering_id',
                'users.first_name',
                'users.last_name'
            )
            ->get()
            ->groupBy('service_offering_id');

        $services = ServiceOffering::with('provider')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedServices = $services->map(function ($service) use ($baseUrl, $reviews) {
            $providerName = 'Independent Provider';
            if ($service->provider) {
                $providerName = trim(($service->provider->first_name ?? '') . ' ' . ($service->provider->last_name ?? ''));
                if (empty($providerName)) {
                    $providerName = $service->provider->name ?? 'Independent Provider';
                }
            }

            $formattedPaths = [];
            if (!empty($service->image_paths)) {
                $formattedPaths = array_map(function ($path) use ($baseUrl) {
                    if (str_starts_with($path, 'http')) {
                        $parsedUrl = parse_url($path);
                        $path = $parsedUrl['path'] ?? $path; 
                    }
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    return $baseUrl . '/storage/' . ltrim($cleanPath, '/');
                }, $service->image_paths);
            }

            $serviceReviews = $reviews->get($service->id, collect());
            $avgRating = $serviceReviews->count() > 0 ? round($serviceReviews->avg('rating'), 1) : 0;
            
            $mappedReviews = $serviceReviews->map(function($r) {
                return [
                    'id' => $r->id,
                    'client_id' => $r->client_id,
                    'client_name' => trim($r->first_name . ' ' . $r->last_name),
                    'rating' => (int) $r->rating,
                    'comment' => $r->comment,
                    'reply' => $r->reply,
                    'client_reply' => $r->client_reply,
                    'created_at' => $r->created_at
                ];
            })->sortByDesc('created_at')->values()->toArray();

            return [
                'id' => $service->id,
                'provider_id' => $service->provider_id,
                'provider_name' => $providerName,
                'title' => $service->title,
                'category' => $service->category,
                'price' => (float) $service->price,
                'price_type' => $service->price_type,
                'duration' => $service->duration,
                'description' => $service->description,
                'image_paths' => $formattedPaths,
                'reviews' => $mappedReviews,
                'average_rating' => $avgRating,
                'total_reviews' => count($mappedReviews)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedServices
        ]);
    }

    public function requestService(Request $request)
    {
        $validated = $request->validate([
            'service_offering_id' => 'required|exists:service_offerings,id',
            'provider_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'preferred_date' => 'required|date',
            'time_preference' => 'required|string',
            'contact_number' => ['required', 'string', 'regex:/^0[0-9]{10}$/'],
            'address' => 'required|string',
        ], [
            'contact_number.regex' => 'The contact number must be exactly 11 digits and start with 0.'
        ]);

        $userId = Auth::id();

        $existingPendingRequest = ClientServiceRequest::where('client_id', $userId)
            ->where('service_offering_id', $validated['service_offering_id'])
            ->where('provider_id', $validated['provider_id'])
            ->where('status', 'pending')
            ->exists();

        if ($existingPendingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending request for this service. Please wait for the provider to respond before sending another one.'
            ], 400);
        }

        if ($validated['address'] === 'default') {
            $clientReq = DB::table('client_requirements')->where('user_id', $userId)->first();
            $addressRecord = null;

            if ($clientReq) {
                $addressRecord = DB::table('client_addresses')
                    ->where('client_requirements_id', $clientReq->id)
                    ->first();
            }

            if ($addressRecord) {
                $addressParts = array_filter([
                    $addressRecord->block_address ?? null,
                    $addressRecord->barangay ?? null,
                    $addressRecord->city ?? null,
                    $addressRecord->province ?? null
                ]);
                
                $validated['address'] = implode(', ', $addressParts);
                
                if (empty(trim($validated['address']))) {
                    $validated['address'] = 'Registered Address (Details missing in profile)';
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No saved address found in your profile. Please use the "Custom Address" option.'
                ], 400);
            }
        }

        $serviceRequest = ClientServiceRequest::create([
            'client_id' => $userId,
            'service_offering_id' => $validated['service_offering_id'],
            'provider_id' => $validated['provider_id'],
            'description' => $validated['description'],
            'preferred_date' => $validated['preferred_date'],
            'time_preference' => $validated['time_preference'],
            'contact_number' => $validated['contact_number'],
            'address' => $validated['address'],
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service request submitted successfully! The provider will contact you soon.',
            'data' => $serviceRequest
        ]);
    }
}