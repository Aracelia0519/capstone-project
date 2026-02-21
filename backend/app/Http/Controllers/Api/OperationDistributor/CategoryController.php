<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Distributor\Product;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Determine the distributor ID based on the user's role
        $distributorId = null;
        if ($user->role === 'operational_distributor') {
            $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            $distributorId = $opDist ? $opDist->parent_distributor_id : null;
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        }

        if (!$distributorId) {
            return response()->json(['message' => 'Unauthorized or distributor not found.'], 403);
        }

        // Fetch distinct categories and aggregate their data
        $categoriesData = Product::where('distributor_id', $distributorId)
            ->whereNotNull('category')
            ->select('category')
            ->selectRaw('COUNT(*) as product_count')
            ->selectRaw('MIN(created_at) as created_date')
            ->groupBy('category')
            ->get();

        // Assign a rotating color theme for the UI
        $colors = [
            'bg-gradient-to-br from-blue-500 to-cyan-500',
            'bg-gradient-to-br from-emerald-500 to-teal-500',
            'bg-gradient-to-br from-amber-500 to-yellow-500',
            'bg-gradient-to-br from-purple-500 to-pink-500',
            'bg-gradient-to-br from-red-500 to-orange-500',
            'bg-gradient-to-br from-indigo-500 to-violet-500'
        ];

        $formattedCategories = [];
        $idCounter = 1;

        foreach ($categoriesData as $index => $cat) {
            if (empty($cat->category)) continue;

            // Fetch up to 3 sample products for the UI badges
            $sampleProducts = Product::where('distributor_id', $distributorId)
                ->where('category', $cat->category)
                ->limit(3)
                ->pluck('name');

            $formattedCategories[] = [
                'id' => $idCounter++,
                'name' => $cat->category,
                'description' => 'Dynamically grouped products under the ' . $cat->category . ' category.',
                'status' => 'Active',
                'productCount' => $cat->product_count,
                'createdDate' => $cat->created_date ? date('Y-m-d', strtotime($cat->created_date)) : date('Y-m-d'),
                'colorClass' => $colors[$index % count($colors)],
                'sampleProducts' => $sampleProducts
            ];
        }

        return response()->json([
            'categories' => $formattedCategories,
            'total_products' => $categoriesData->sum('product_count')
        ]);
    }

    // NEW METHOD: Fetch products properly using parent_distributor_id
    public function products(Request $request)
    {
        $user = $request->user();
        
        // Determine the distributor ID based on the user's role
        $distributorId = null;
        if ($user->role === 'operational_distributor') {
            $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            $distributorId = $opDist ? $opDist->parent_distributor_id : null;
        } elseif ($user->role === 'distributor') {
            $distributorId = $user->id;
        }

        if (!$distributorId) {
            return response()->json(['message' => 'Unauthorized or distributor not found.'], 403);
        }

        $query = Product::where('distributor_id', $distributorId);

        // Filter by category if provided in the URL query string
        if ($request->has('category')) {
            $query->where('category', $request->query('category'));
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count()
        ]);
    }
}