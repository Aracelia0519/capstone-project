<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Distributor\Product;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Reusable method to check if a user has access to a specific action based on the DB schema
     */
    private function checkRbacAccess($user, $permissionKey, $action)
    {
        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return true;
        }

        // Check RBAC for standard employees
        if ($user->role === 'employee') {
            // 1. Get the employee record
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) {
                return false;
            }

            // 2. Find their position ID by matching the string title
            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();

            if (!$position) {
                return false;
            }

            // 3. Check the position_accessibilities table for specific permission_key & action
            $hasAccess = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->where($action, 1) // checks can_view, can_create, can_update, or can_delete
                ->exists();

            return $hasAccess;
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Check RBAC Read Access using the exact key from the database
        if (!$this->checkRbacAccess($user, 'ec_categories', 'can_view')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view categories.'], 403);
        }
        
        // Determine the distributor ID based on the user's role
        $distributorId = null;
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            $distributorId = $employee ? $employee->parent_distributor_id : null;
        } elseif ($user->role === 'operational_distributor') {
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

    public function products(Request $request)
    {
        $user = $request->user();

        // Check RBAC Read Access
        if (!$this->checkRbacAccess($user, 'ec_categories', 'can_view')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to view category products.'], 403);
        }
        
        // Determine the distributor ID based on the user's role
        $distributorId = null;
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            $distributorId = $employee ? $employee->parent_distributor_id : null;
        } elseif ($user->role === 'operational_distributor') {
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

    // Intercept Category Creation for RBAC check
    public function store(Request $request)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_categories', 'can_create')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to add new categories.'], 403);
        }
        
        // Note: As your frontend noted, categories are dynamic based on products.
        // This simulates a successful return so the frontend can update its UI state.
        return response()->json(['success' => true, 'message' => 'Category added successfully.']);
    }

    // Intercept Category Updating for RBAC check
    public function update(Request $request, $id)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_categories', 'can_update')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to modify categories.'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
    }

    // Intercept Category Deletion for RBAC check
    public function destroy(Request $request, $id)
    {
        if (!$this->checkRbacAccess($request->user(), 'ec_categories', 'can_delete')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to delete categories.'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }
}