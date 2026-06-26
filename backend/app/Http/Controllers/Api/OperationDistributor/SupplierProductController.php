<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierProductController extends Controller
{
    /**
     * Fetch RBAC permissions for the logged-in user (Level-Based).
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        // Uses the exact same RBAC permission key
        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'ec_partner_supplier')
            ->first();

        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_manage' => (bool)$access->can_manage,
            'can_approve' => (bool)$access->can_approve,
        ];
    }

    /**
     * Fetch all available products (raw materials) of a specific supplier
     */
    public function index($supplierId)
    {
        try {
            $user = Auth::user();

            // Get permissions and check Level-Based RBAC Read Access
            $permissions = $this->getPermissions($user);
            
            if (!$permissions['can_view']) {
                return response()->json(['message' => 'Access Denied: You do not have permission to view partner suppliers.'], 403);
            }

            // Fetch the Supplier details
            $supplier = DB::table('users')->where('id', $supplierId)->where('role', 'supplier')->first();
            
            if (!$supplier) {
                return response()->json(['success' => false, 'message' => 'Supplier not found.'], 404);
            }

            $supplierName = $supplier->first_name . ' ' . $supplier->last_name;
            $companyName = DB::table('supplier_requirements')->where('user_id', $supplierId)->value('company_name');

            // Fetch the products (supplier_raw_materials)
            $products = DB::table('supplier_raw_materials')
                ->where('user_id', $supplierId)
                ->get();

            return response()->json([
                'success' => true,
                'supplier_name' => $companyName ? $companyName : $supplierName,
                'data' => $products,
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch supplier products: ' . $e->getMessage()
            ], 500);
        }
    }
}