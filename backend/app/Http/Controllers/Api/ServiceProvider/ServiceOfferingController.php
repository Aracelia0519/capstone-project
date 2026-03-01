<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider\ServiceOffering;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceOfferingController extends Controller
{
    public function index()
    {
        // Fetch only the services belonging to the logged-in Service Provider
        $services = ServiceOffering::where('provider_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services
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
                $path = $image->store('service_offerings', 'public');
                $imagePaths[] = asset('storage/' . $path);
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
            'is_active' => $validated['is_active'],
            'image_paths' => !empty($imagePaths) ? $imagePaths : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service offering posted successfully',
            'data' => $service
        ]);
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
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        $imagePaths = $service->image_paths ?? [];

        // If new images are uploaded, overwrite the old ones (or you could append based on your needs)
        if ($request->hasFile('images')) {
            $imagePaths = []; 
            foreach ($request->file('images') as $image) {
                $path = $image->store('service_offerings', 'public');
                $imagePaths[] = asset('storage/' . $path);
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

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service
        ]);
    }

    public function destroy($id)
    {
        $service = ServiceOffering::where('provider_id', Auth::id())->findOrFail($id);
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service removed successfully']);
    }

    public function toggleStatus($id)
    {
        $service = ServiceOffering::where('provider_id', Auth::id())->findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }
}