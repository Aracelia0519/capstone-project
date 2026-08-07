<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ServiceProvider\ServiceProviderPortfolio;

class ServiceProviderPortfolioController extends Controller
{
    /**
     * Fetch the authenticated provider's portfolio
     */
    public function show(Request $request)
    {
        $portfolio = ServiceProviderPortfolio::where('provider_id', Auth::id())->first();

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio not found.'
            ], 404);
        }

        // Map relative storage paths to full accessible URLs for the frontend
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $galleryUrls = [];
        
        if (!empty($portfolio->gallery_images)) {
            foreach ($portfolio->gallery_images as $path) {
                $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                $galleryUrls[] = $baseUrl . '/storage/' . ltrim($cleanPath, '/');
            }
        }

        $portfolioData = $portfolio->toArray();
        $portfolioData['gallery_urls'] = $galleryUrls;

        return response()->json([
            'success' => true,
            'data' => $portfolioData
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
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB per image
        ]);

        $providerId = Auth::id();

        // Find existing portfolio or initialize a new one
        $portfolio = ServiceProviderPortfolio::firstOrNew(['provider_id' => $providerId]);

        // Process File Uploads
        $imagePaths = $portfolio->gallery_images ?? [];
        
        if ($request->hasFile('gallery_images')) {
            // For simplicity, overwriting existing images. If you wish to append, comment out the line below.
            $imagePaths = []; 

            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('service_providers/portfolios', 'public');
                $imagePaths[] = $path;
            }
        }

        // Update Fields
        $portfolio->motto = $validated['motto'];
        $portfolio->bio = $validated['bio'];
        $portfolio->experience_years = $validated['experience_years'];
        $portfolio->specialties = $validated['specialties'] ?? '';
        
        if ($request->hasFile('gallery_images')) {
            $portfolio->gallery_images = $imagePaths;
        }

        $portfolio->save();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio successfully updated.',
            'data' => $portfolio
        ]);
    }
}