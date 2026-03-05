<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider\ServiceOffering;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceOfferingController extends Controller
{
    /**
     * Helper to dynamically format image URLs based on the requesting device/domain
     */
    private function formatImagePaths($imagePaths, $baseUrl)
    {
        if (empty($imagePaths)) return [];

        return array_map(function ($path) use ($baseUrl) {
            // Remove old hardcoded localhost URLs if they exist in the DB
            if (str_starts_with($path, 'http')) {
                $parsedUrl = parse_url($path);
                $path = $parsedUrl['path'] ?? $path;
            }
            
            // Strip '/storage/' to get the raw relative path
            $cleanPath = preg_replace('/^\/?storage\//', '', $path);
            $cleanPath = ltrim($cleanPath, '/');
            
            // Attach the perfect dynamic base URL for the current device
            return $baseUrl . '/storage/' . $cleanPath;
        }, $imagePaths);
    }

    public function index(Request $request)
    {
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        // Fetch only the services belonging to the logged-in Service Provider
        $services = ServiceOffering::where('provider_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Format the image paths before sending to the frontend
        $formattedServices = $services->map(function ($service) use ($baseUrl) {
            if (!empty($service->image_paths)) {
                $service->image_paths = $this->formatImagePaths($service->image_paths, $baseUrl);
            }
            return $service;
        });

        return response()->json([
            'success' => true,
            'data' => $formattedServices
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'price_type' => 'required|string',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required|boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120' // 5MB max per image
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Save the relative path
                $path = $image->store('service_offerings', 'public');
                $imagePaths[] = $path; 
            }
        }

        $service = ServiceOffering::create([
            'provider_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'image_paths' => $imagePaths,
            'is_active' => $validated['is_active'],
        ]);

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        if (!empty($service->image_paths)) {
            $service->image_paths = $this->formatImagePaths($service->image_paths, $baseUrl);
        }

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $service = ServiceOffering::where('provider_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric',
            'price_type' => 'required|string',
            'duration' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120' 
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            // Delete old images if new ones are uploaded
            if (!empty($service->image_paths)) {
                foreach ($service->image_paths as $oldImage) {
                    $relativePath = preg_replace('/^.*?\/storage\//', '', $oldImage);
                    Storage::disk('public')->delete($relativePath);
                }
            }

            foreach ($request->file('images') as $image) {
                $path = $image->store('service_offerings', 'public');
                $imagePaths[] = $path;
            }
        }

        $service->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'duration' => $validated['duration'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
            'image_paths' => !empty($imagePaths) ? $imagePaths : $service->image_paths,
        ]);

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        if (!empty($service->image_paths)) {
            $service->image_paths = $this->formatImagePaths($service->image_paths, $baseUrl);
        }

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service
        ]);
    }

    public function destroy($id)
    {
        $service = ServiceOffering::where('provider_id', Auth::id())->findOrFail($id);
        
        if (!empty($service->image_paths)) {
            foreach ($service->image_paths as $oldImage) {
                $relativePath = preg_replace('/^.*?\/storage\//', '', $oldImage);
                Storage::disk('public')->delete($relativePath);
            }
        }
        
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service removed successfully']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $service = ServiceOffering::where('provider_id', Auth::id())->findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        if (!empty($service->image_paths)) {
            $service->image_paths = $this->formatImagePaths($service->image_paths, $baseUrl);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Service status updated',
            'data' => $service
        ]);
    }
}