<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderSavedColor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceProviderColorController extends Controller
{
    public function saveColor(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'hex' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
                'rgb' => 'required|string|max:255',
                'hsl' => 'required|string|max:255',
                'hue' => 'required|integer|min:0|max:360',
                'saturation' => 'required|integer|min:0|max:100',
                'lightness' => 'required|integer|min:0|max:100',
                'contrast' => 'required|numeric|min:0|max:100',
                'luminance' => 'required|integer|min:0|max:100',
                'temperature' => 'required|string|max:50',
                'accessibility' => 'required|string|max:50',
                'family' => 'required|string|max:50',
                'stability' => 'required|string|max:50',
                'frequency' => 'required|string|max:50',
                'quantumState' => 'required|string|max:50',
                'sourceColors' => 'nullable|array',
                'palettes' => 'nullable|array',
                'isFavorite' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Check if color with same hex already exists for this user
            $existingColor = ServiceProviderSavedColor::where('user_id', $user->id)
                ->where('hex_code', strtoupper($request->hex))
                ->first();

            if ($existingColor) {
                return response()->json([
                    'success' => false,
                    'message' => 'This color has already been saved'
                ], 409);
            }

            // Save the color
            $color = ServiceProviderSavedColor::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'hex_code' => strtoupper($request->hex),
                'rgb_values' => $request->rgb,
                'hsl_values' => $request->hsl,
                'hue' => $request->hue,
                'saturation' => $request->saturation,
                'lightness' => $request->lightness,
                'contrast_ratio' => $request->contrast,
                'luminance' => $request->luminance,
                'temperature' => $request->temperature,
                'accessibility' => $request->accessibility,
                'color_family' => $request->family,
                'stability' => $request->stability,
                'frequency' => $request->frequency,
                'quantum_state' => $request->quantumState,
                'source_colors' => $request->sourceColors ? json_encode($request->sourceColors) : null,
                'palettes' => $request->palettes ? json_encode($request->palettes) : null,
                'is_favorite' => $request->isFavorite ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Color saved successfully!',
                'data' => [
                    'id' => $color->id,
                    'name' => $color->name,
                    'hex' => $color->hex_code,
                    'saved_at' => $color->created_at_formatted,
                    'is_favorite' => $color->is_favorite,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save color',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSavedColors(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = ServiceProviderSavedColor::forUser($user->id)
                ->orderBy('created_at', 'desc');

            // Apply filters if provided
            if ($request->has('family')) {
                $query->byColorFamily($request->family);
            }

            if ($request->boolean('favorites_only')) {
                $query->favorites();
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('hex_code', 'like', "%{$search}%")
                      ->orWhere('color_family', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $colors = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'colors' => $colors->items(),
                    'pagination' => [
                        'total' => $colors->total(),
                        'per_page' => $colors->perPage(),
                        'current_page' => $colors->currentPage(),
                        'last_page' => $colors->lastPage(),
                        'from' => $colors->firstItem(),
                        'to' => $colors->lastItem(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch saved colors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getColor($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $color = ServiceProviderSavedColor::forUser($user->id)->find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $color
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch color',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteColor($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $color = ServiceProviderSavedColor::forUser($user->id)->find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color not found'
                ], 404);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Color deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete color',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleFavorite($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $color = ServiceProviderSavedColor::forUser($user->id)->find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color not found'
                ], 404);
            }

            $color->update(['is_favorite' => !$color->is_favorite]);

            return response()->json([
                'success' => true,
                'message' => 'Color favorite status updated',
                'data' => [
                    'is_favorite' => $color->is_favorite
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update favorite status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getColorStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $stats = ServiceProviderSavedColor::forUser($user->id)
                ->select(
                    DB::raw('COUNT(*) as total_colors'),
                    DB::raw('SUM(CASE WHEN is_favorite = 1 THEN 1 ELSE 0 END) as total_favorites'),
                    DB::raw('COUNT(DISTINCT color_family) as color_families'),
                    DB::raw('MAX(created_at) as last_saved')
                )
                ->first();

            $familyDistribution = ServiceProviderSavedColor::forUser($user->id)
                ->select('color_family', DB::raw('COUNT(*) as count'))
                ->groupBy('color_family')
                ->orderBy('count', 'desc')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->color_family => $item->count];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'family_distribution' => $familyDistribution,
                    'recent_colors' => ServiceProviderSavedColor::forUser($user->id)->recent(5)->get()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch color statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}