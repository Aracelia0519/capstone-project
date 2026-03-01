<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceProvider\ServiceOffering;
use App\Models\EcommerceClient\ClientServiceRequest;

class ClientServiceController extends Controller
{
    /**
     * Fetch all active service offerings from all providers
     */
    public function getServices()
    {
        $services = ServiceOffering::with('provider')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedServices = $services->map(function ($service) {
            $providerName = 'Independent Provider';
            if ($service->provider) {
                $providerName = trim(($service->provider->first_name ?? '') . ' ' . ($service->provider->last_name ?? ''));
                if (empty($providerName)) {
                    $providerName = $service->provider->name ?? 'Independent Provider';
                }
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
                'image_paths' => $service->image_paths, // Array of URLs
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
        $validated = $request->validate([
            'service_offering_id' => 'required|exists:service_offerings,id',
            'provider_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'preferred_date' => 'required|date',
            'time_preference' => 'required|string',
            'contact_number' => 'required|string',
            'address' => 'required|string',
        ]);

        $serviceRequest = ClientServiceRequest::create([
            'client_id' => Auth::id(),
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