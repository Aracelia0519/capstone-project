<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier\SupplierRawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SupplierRawMaterialController extends Controller
{
    public function index()
    {
        // Fetch only the authenticated supplier's materials
        $materials = SupplierRawMaterial::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'type' => 'required|string',
            'name' => 'required|string',
            'sku_code' => 'nullable|string',
            'size' => 'required|string',
            'color_code' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048' // Max 2MB
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('supplier/raw_materials', 'public');
            $validated['image_url'] = $path;
        }

        $material = SupplierRawMaterial::create($validated);

        return response()->json([
            'message' => 'Material added successfully',
            'data' => $material
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $material = SupplierRawMaterial::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string',
            'type' => 'required|string',
            'name' => 'required|string',
            'sku_code' => 'nullable|string',
            'size' => 'required|string',
            'color_code' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($material->image_url && Storage::disk('public')->exists($material->image_url)) {
                Storage::disk('public')->delete($material->image_url);
            }
            $path = $request->file('image')->store('supplier/raw_materials', 'public');
            $validated['image_url'] = $path;
        }

        $material->update($validated);

        return response()->json([
            'message' => 'Material updated successfully',
            'data' => $material
        ]);
    }

    public function destroy($id)
    {
        $material = SupplierRawMaterial::where('user_id', Auth::id())->findOrFail($id);
        
        if ($material->image_url && Storage::disk('public')->exists($material->image_url)) {
            Storage::disk('public')->delete($material->image_url);
        }

        $material->delete();

        return response()->json(['message' => 'Material deleted successfully']);
    }
}