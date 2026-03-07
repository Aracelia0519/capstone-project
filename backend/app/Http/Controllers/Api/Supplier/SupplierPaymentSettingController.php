<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier\SupplierPaymentSetting;

class SupplierPaymentSettingController extends Controller
{
    /**
     * Resolve the main supplier ID based on the logged-in user's role
     */
    private function getSupplierId($user) 
    {
        $supplierId = $user->id; // Default if the user is the primary supplier

        if ($user->role === 'supplier_employee') {
            $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
            if ($personnel) {
                $supplierId = $personnel->supplier_id;
            }
        } elseif ($user->role === 'personnel_officer') {
            $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
            if ($officer) {
                $supplierId = $officer->supplier_id;
            }
        }

        return $supplierId;
    }

    /**
     * Retrieve the current payment settings for the supplier
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $supplierId = $this->getSupplierId($user);

        // Fetch or Initialize default settings if none exist
        $settings = SupplierPaymentSetting::firstOrCreate(
            ['supplier_id' => $supplierId],
            [
                'is_cod_enabled' => true,
                'is_gcash_enabled' => false,
                'gcash_number' => null
            ]
        );

        return response()->json([
            'success' => true, 
            'data' => $settings
        ]);
    }

    /**
     * Update the supplier's payment settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'is_cod_enabled' => 'required|boolean',
            'is_gcash_enabled' => 'required|boolean',
            // GCash number is strictly required only if GCash is enabled
            'gcash_number' => 'required_if:is_gcash_enabled,true|nullable|string|max:20'
        ]);

        $user = Auth::user();
        $supplierId = $this->getSupplierId($user);

        $settings = SupplierPaymentSetting::updateOrCreate(
            ['supplier_id' => $supplierId],
            [
                'is_cod_enabled' => $request->is_cod_enabled,
                'is_gcash_enabled' => $request->is_gcash_enabled,
                'gcash_number' => $request->is_gcash_enabled ? $request->gcash_number : null,
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => 'Payment methods updated successfully!', 
            'data' => $settings
        ]);
    }
}