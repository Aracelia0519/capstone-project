<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderPortfolio;
use Vinkla\Hashids\Facades\Hashids; // Import Hashids 

class ProviderProfileController extends Controller
{
    public function show(Request $request, $id)
    {
        // Decode the incoming hashid, falling back to the raw $id if decoding is empty
        $decoded = Hashids::decode($id);
        $providerId = !empty($decoded) ? $decoded[0] : $id;

        $provider = User::where('id', $providerId)->where('role', 'service_provider')->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Service Provider not found.'
            ], 404);
        }

        $portfolio = ServiceProviderPortfolio::where('provider_id', $providerId)->first();
        
        $providerName = trim($provider->first_name . ' ' . $provider->last_name);

        $data = [
            'provider_id' => $provider->id,
            'provider_name' => $providerName ?: 'Independent Provider',
            'motto' => $portfolio->motto ?? null,
            'bio' => $portfolio->bio ?? null,
            'experience_years' => $portfolio->experience_years ?? null,
            'specialties' => $portfolio->specialties ?? null,
            'gallery_urls' => []
        ];

        if ($portfolio && !empty($portfolio->gallery_images)) {
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
            foreach ($portfolio->gallery_images as $path) {
                $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                $data['gallery_urls'][] = $baseUrl . '/storage/' . ltrim($cleanPath, '/');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}