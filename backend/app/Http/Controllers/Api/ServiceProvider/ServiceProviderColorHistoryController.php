<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderSavedColor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ServiceProviderColorHistoryController extends Controller
{
    /**
     * Fetch all saved colors for the color history list.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Fetch all saved colors for this user
            // We fetch all to allow your Vue frontend's computed properties to handle the filtering/sorting
            $colors = ServiceProviderSavedColor::forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($color) {
                    return [
                        'id' => $color->id,
                        'name' => $color->name,
                        'hex_code' => $color->hex_code,
                        'rgb_values' => $color->rgb_values,
                        // Using color_family as a stand-in for Client Name since there's no client field yet
                        // You can change this later once jobs/clients are linked to colors
                        'client_name' => $color->color_family . ' Project', 
                        'created_at' => $color->created_at->toIso8601String(),
                        'time_ago' => $color->created_at->diffForHumans(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $colors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch color history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}