<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the distributor's products.
     */
    public function index(Request $request)
    {
        try {
            $distributorId = Auth::id();
            
            $query = Product::forDistributor($distributorId)
                ->active()
                ->orderBy('created_at', 'desc');
            
            // Apply filters
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }
            
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $products = $query->get();
            
            // Convert image URLs to full URLs
            $products->transform(function ($product) {
                if ($product->image_url) {
                    // If it's already a full URL, return as is
                    if (filter_var($product->image_url, FILTER_VALIDATE_URL)) {
                        return $product;
                    }
                    
                    // If it's a data URL (base64), return as is
                    if (str_starts_with($product->image_url, 'data:')) {
                        return $product;
                    }
                    
                    // For relative paths, create full URL using asset()
                    $product->image_url = asset('storage/' . ltrim($product->image_url, '/'));
                }
                return $product;
            });
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading products:', [
                'distributor_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'sku_code' => 'nullable|string|max:100|unique:distributor_products,sku_code',
            'size' => 'required|string|max:100',
            'color_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' // 2MB max
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Only JPG, PNG, GIF, and WebP images are allowed.',
            'image.max' => 'The image size must be less than 2MB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = null;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = 'product_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $folderPath = 'products';
                
                // Store the file using Storage facade
                $path = $image->storeAs($folderPath, $fileName, 'public');
                $imagePath = $path; // Store relative path without 'storage/' prefix
                
                Log::info('Product image uploaded:', [
                    'distributor_id' => Auth::id(),
                    'file_name' => $fileName,
                    'relative_path' => $path,
                    'storage_path' => 'storage/' . $path,
                    'full_url' => asset('storage/' . $path)
                ]);
            }

            $product = Product::create([
                'distributor_id' => Auth::id(),
                'category' => $request->category,
                'type' => $request->type,
                'name' => $request->name,
                'sku_code' => $request->sku_code,
                'size' => $request->size,
                'color_code' => $request->color_code,
                'price' => $request->price,
                'cost' => $request->cost,
                'min_stock_level' => $request->min_stock_level ?? 10,
                'max_stock_level' => $request->max_stock_level ?? 100,
                'description' => $request->description,
                'image_url' => $imagePath // Store relative path only
            ]);

            // Return full URL for the response
            if ($product->image_url) {
                $product->image_url = asset('storage/' . $product->image_url);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating product:', [
                'distributor_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            $distributorId = Auth::id();
            
            $product = Product::forDistributor($distributorId)->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            // Ensure image URL is full URL
            if ($product->image_url) {
                if (!filter_var($product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($product->image_url, 'data:')) {
                    $product->image_url = asset('storage/' . ltrim($product->image_url, '/'));
                }
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading product:', [
                'distributor_id' => Auth::id(),
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        try {
            $distributorId = Auth::id();
            
            $product = Product::forDistributor($distributorId)->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|max:255',
                'name' => 'sometimes|required|string|max:255',
                'sku_code' => 'nullable|string|max:100|unique:distributor_products,sku_code,' . $id,
                'size' => 'sometimes|required|string|max:100',
                'color_code' => 'nullable|string|max:50',
                'price' => 'sometimes|required|numeric|min:0',
                'cost' => 'nullable|numeric|min:0',
                'min_stock_level' => 'nullable|integer|min:0',
                'max_stock_level' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'is_active' => 'sometimes|boolean'
            ], [
                'image.image' => 'The file must be an image.',
                'image.mimes' => 'Only JPG, PNG, GIF, and WebP images are allowed.',
                'image.max' => 'The image size must be less than 2MB.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image_url) {
                    $oldPath = ltrim($product->image_url, '/');
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                
                $image = $request->file('image');
                $fileName = 'product_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $folderPath = 'products';
                
                // Store the new file
                $path = $image->storeAs($folderPath, $fileName, 'public');
                
                Log::info('Product image updated:', [
                    'product_id' => $id,
                    'file_name' => $fileName,
                    'relative_path' => $path,
                    'full_url' => asset('storage/' . $path)
                ]);
                
                // Store relative path without 'storage/' prefix
                $product->image_url = $path;
            }

            // Update other fields
            $updateData = $request->except('image');
            if (isset($updateData['image_url']) && $updateData['image_url'] === null) {
                // Handle image removal if image_url is explicitly set to null
                if ($product->image_url) {
                    $oldPath = ltrim($product->image_url, '/');
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $product->image_url = null;
                unset($updateData['image_url']);
            }
            
            $product->update($updateData);

            // Return full URL for the response
            if ($product->image_url && !filter_var($product->image_url, FILTER_VALIDATE_URL) && !str_starts_with($product->image_url, 'data:')) {
                $product->image_url = asset('storage/' . $product->image_url);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating product:', [
                'distributor_id' => Auth::id(),
                'product_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        try {
            $distributorId = Auth::id();
            
            $product = Product::forDistributor($distributorId)->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Delete associated image if exists
            if ($product->image_url) {
                $imagePath = ltrim($product->image_url, '/');
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $product->delete();

            Log::info('Product deleted:', [
                'distributor_id' => $distributorId,
                'product_id' => $id,
                'product_name' => $product->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting product:', [
                'distributor_id' => Auth::id(),
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product statistics.
     */
    public function statistics()
    {
        try {
            $distributorId = Auth::id();
            
            $stats = Product::forDistributor($distributorId)
                ->selectRaw('
                    COUNT(*) as total_products,
                    COUNT(DISTINCT category) as categories_count,
                    COUNT(DISTINCT type) as types_count,
                    AVG(price) as avg_price
                ')
                ->first();

            $categoryStats = Product::forDistributor($distributorId)
                ->select('category')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('category')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'overall' => $stats,
                    'by_category' => $categoryStats
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading product statistics:', [
                'distributor_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}