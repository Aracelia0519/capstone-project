<?php

namespace App\Http\Controllers\Api\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module.
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if (in_array($user->role, ['distributor', 'operational_distributor'])) {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
            ];
        }

        // Check RBAC for standard employees
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_create' => (bool) $access->can_create,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
                ];
            }
        }

        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action)
    {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    // Helper method to resolve the distributor ID based on user roles
    private function getDistributorId($user)
    {
        $distributorId = $user->id; // Default to their own ID

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if ($employee && $employee->parent_distributor_id) {
                $distributorId = $employee->parent_distributor_id;
            }
        } elseif (in_array($user->role, ['operational_distributor', 'hr_manager', 'finance_manager'])) {
            $tableName = $user->role . 's'; 
            $staff = DB::table($tableName)->where('user_id', $user->id)->first(); 
            if ($staff && $staff->parent_distributor_id) {
                $distributorId = $staff->parent_distributor_id;
            }
        }

        return $distributorId;
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $permissions = $this->getPermissions($user, 'ec_promotions');
        
        if (!$permissions['can_view']) {
            return response()->json(['status' => 'error', 'message' => 'Access Denied: You do not have permission to view promotions.'], 403);
        }

        $distributorId = $this->getDistributorId($user);

        // Filter promotions where distributor matches and load the relationships
        $promotions = Promotion::with(['creator', 'product'])
            ->where('distributor_id', $distributorId)
            ->latest()
            ->get();
            
        return response()->json([
            'status' => 'success', 
            'data' => $promotions,
            'permissions' => $permissions
        ]);
    }

    public function getProducts()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
            }

            if (!$this->checkRbacAccess($user, 'ec_promotions', 'can_view') && !$this->checkRbacAccess($user, 'ec_promotions', 'can_create')) {
                return response()->json(['status' => 'error', 'message' => 'Access Denied'], 403);
            }

            $distributorId = $this->getDistributorId($user);

            // Fetch products and their inventory data using the resolved distributor ID
            $products = DB::table('distributor_products')
                ->leftJoin('distributor_inventories', 'distributor_products.id', '=', 'distributor_inventories.product_id')
                ->where('distributor_products.distributor_id', $distributorId)
                ->whereNull('distributor_products.deleted_at') // Ignore soft-deleted products
                ->select(
                    'distributor_products.id', 
                    'distributor_products.name', 
                    'distributor_inventories.quantity as stock'
                )
                ->get();
                
            return response()->json([
                'status' => 'success', 
                'data' => $products,
                'debug' => [
                    'auth_user_id' => $user->id,
                    'auth_user_role' => $user->role,
                    'resolved_distributor_id' => $distributorId,
                    'total_products_found' => $products->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching promotion products: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // HARD BACKEND CHECK: If an employee circumvents the UI, the backend will stop it here.
        if (!$this->checkRbacAccess($user, 'ec_promotions', 'can_create')) {
            return response()->json(['status' => 'error', 'message' => 'Access Denied: You do not have permission to create promotions.'], 403);
        }

        $distributorId = $this->getDistributorId($user);

        $maxEndDate = $request->start_date 
            ? Carbon::parse($request->start_date)->addDays(30)->toDateString() 
            : Carbon::today()->addDays(30)->toDateString();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:percentage,fixed,free_shipping,bogo',
            'discount_value' => 'required_if:type,percentage,fixed|nullable|numeric|min:0',
            'code' => 'required|string|unique:crm_promotions,code',
            'usage_limit' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date|before_or_equal:' . $maxEndDate,
            'product_id' => 'nullable' 
        ], [
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'end_date.before_or_equal' => 'The end date cannot be more than 30 days after the start date.',
            'discount_value.required_if' => 'The discount value field is required for fixed and percentage discounts.',
        ]);

        $promotion = Promotion::create([
            'user_id' => $user->id,
            'distributor_id' => $distributorId,
            'product_id' => $validated['product_id'] ?? null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'discount_value' => $validated['discount_value'],
            'code' => $validated['code'],
            'usage_limit' => $validated['usage_limit'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Promotion created successfully and is pending approval.', 
            'data' => $promotion
        ]);
    }
}