<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributorSupplierProductController extends Controller
{
    /**
     * Fetch all available products (raw materials) of a specific supplier for the Main Distributor
     */
    public function index($supplierId)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Only allow distributors (or admins) to fetch this data
            if (!$user || !$user->isDistributor()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
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
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch supplier products: ' . $e->getMessage()
            ], 500);
        }
    }
}