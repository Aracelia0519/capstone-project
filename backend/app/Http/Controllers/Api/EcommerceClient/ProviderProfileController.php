<?php

namespace App\Http\Controllers\Api\EcommerceClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderPortfolio;
use App\Support\PortfolioGallery;
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
            'gallery_urls' => [],
            'gallery_data' => []
        ];

        if ($portfolio && !empty($portfolio->gallery_images)) {
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
            $completedImages = PortfolioGallery::completedJobImages((int) $providerId, $baseUrl);
            $imageLookup = collect($completedImages)->keyBy('path');
            $seenCompletionIds = [];

            foreach ($portfolio->gallery_images as $path) {
                $match = $imageLookup->get($path);

                // If the path belongs to a completed job, ensure only 1 canvas per completion_id is shown
                if ($match && isset($match['completion_id'])) {
                    if (in_array($match['completion_id'], $seenCompletionIds, true)) {
                        continue;
                    }
                    $seenCompletionIds[] = $match['completion_id'];
                }

                $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                $url = $baseUrl . '/storage/' . ltrim($cleanPath, '/');

                $data['gallery_urls'][] = $url;
                $data['gallery_data'][] = [
                    'path' => $path,
                    'url' => $url,
                    'before_path' => $match['before_path'] ?? null,
                    'before_url' => $match['before_url'] ?? null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}