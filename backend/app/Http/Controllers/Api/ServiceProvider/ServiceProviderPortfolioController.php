<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceProvider\ServiceProviderPortfolio;

class ServiceProviderPortfolioController extends Controller
{
    /**
     * Fetch the authenticated provider's portfolio along with available images from completed jobs.
     */
    public function show(Request $request)
    {
        $providerId = Auth::id();
        $portfolio = ServiceProviderPortfolio::where('provider_id', $providerId)->first();
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        
        // Map currently saved portfolio gallery paths to full URLs
        $galleryData = [];
        if ($portfolio && !empty($portfolio->gallery_images)) {
            foreach ($portfolio->gallery_images as $path) {
                $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                $galleryData[] = [
                    'path' => $path,
                    'url' => $baseUrl . '/storage/' . ltrim($cleanPath, '/')
                ];
            }
        }

        // Fetch all approved/completed service job images associated with this provider
        $completedJobs = DB::table('service_job_completions')
            ->join('client_service_requests', 'service_job_completions.client_service_request_id', '=', 'client_service_requests.id')
            ->where('client_service_requests.provider_id', $providerId)
            ->where('service_job_completions.status', 'approved')
            ->pluck('service_job_completions.proof_images');

        $availableImages = [];
        foreach ($completedJobs as $jsonImages) {
            $images = json_decode($jsonImages, true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    $cleanImgPath = preg_replace('/^\/?storage\//', '', $img);
                    $availableImages[] = [
                        'path' => $img,
                        'url' => $baseUrl . '/storage/' . ltrim($cleanImgPath, '/')
                    ];
                }
            }
        }

        // Ensure we only return unique images just in case
        $uniqueAvailableImages = collect($availableImages)->unique('path')->values()->toArray();

        $portfolioData = $portfolio ? $portfolio->toArray() : [];
        $portfolioData['gallery_data'] = $galleryData;

        return response()->json([
            'success' => true,
            'data' => $portfolioData,
            'available_images' => $uniqueAvailableImages
        ]);
    }

    /**
     * Create or update the provider's portfolio
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'motto' => 'required|string|max:255',
            'bio' => 'required|string',
            'experience_years' => 'required|integer|min:0',
            'specialties' => 'nullable|string|max:255',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'string', // Accepts paths of the selected existing images
        ]);

        $providerId = Auth::id();

        // Find existing portfolio or initialize a new one
        $portfolio = ServiceProviderPortfolio::firstOrNew(['provider_id' => $providerId]);

        // Update Fields
        $portfolio->motto = $validated['motto'];
        $portfolio->bio = $validated['bio'];
        $portfolio->experience_years = $validated['experience_years'];
        $portfolio->specialties = $validated['specialties'] ?? '';
        
        // Directly store the array of paths
        $portfolio->gallery_images = $validated['gallery_images'] ?? [];

        $portfolio->save();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio successfully updated.',
            'data' => $portfolio
        ]);
    }
}