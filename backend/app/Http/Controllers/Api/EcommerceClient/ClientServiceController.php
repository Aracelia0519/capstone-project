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
    /**
     * Fetch all active service offerings from all providers
     */
    public function getServices(Request $request)
    {
        // Dynamically get the base URL of the device/domain making the request
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        $services = ServiceOffering::with('provider')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedServices = $services->map(function ($service) use ($baseUrl) {
            $providerName = 'Independent Provider';
            if ($service->provider) {
                $providerName = trim(($service->provider->first_name ?? '') . ' ' . ($service->provider->last_name ?? ''));
                if (empty($providerName)) {
                    $providerName = $service->provider->name ?? 'Independent Provider';
                }
            }

            // Dynamically format image paths to use the live domain instead of localhost
            $formattedPaths = [];
            if (!empty($service->image_paths)) {
                $formattedPaths = array_map(function ($path) use ($baseUrl) {
                    if (str_starts_with($path, 'http')) {
                        $parsedUrl = parse_url($path);
                        $path = $parsedUrl['path'] ?? $path; 
                    }
                    
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    $cleanPath = ltrim($cleanPath, '/');
                    
                    return $baseUrl . '/storage/' . $cleanPath;
                }, $service->image_paths);
            }

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
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedServices
        ]);
    }

    /**
     * Submit a new service request
     */
    public function requestService(Request $request)
    {
        // Added regex validation: Must start with 0 and exactly 11 digits
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

        // VALIDATION: Prevent duplicate pending requests for the same service and provider
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

        // Intercept the "default" string and replace it with the user's actual address via client_requirements
        if ($validated['address'] === 'default') {
            
            // First fetch the client requirement record for this user
            $clientReq = DB::table('client_requirements')->where('user_id', $userId)->first();
            $addressRecord = null;

            // If a requirement exists, fetch the linked address
            if ($clientReq) {
                $addressRecord = DB::table('client_addresses')
                    ->where('client_requirements_id', $clientReq->id)
                    ->first();
            }

            if ($addressRecord) {
                // Piece together the address dynamically based on your ClientAddress model
                $addressParts = array_filter([
                    $addressRecord->block_address ?? null,
                    $addressRecord->barangay ?? null,
                    $addressRecord->city ?? null,
                    $addressRecord->province ?? null
                ]);
                
                $validated['address'] = implode(', ', $addressParts);
                
                // Fallback just in case they have a blank record
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