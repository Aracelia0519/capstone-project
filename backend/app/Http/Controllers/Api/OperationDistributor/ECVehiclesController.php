<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ECVehiclesController extends Controller
{
    private function checkAccess()
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Match the structural full access check found in your system
        if (isset($user->has_full_access) && $user->has_full_access) {
            return true;
        }

        // Standard operational distributor role authorization lookup path
        if ($user->role === 'operational_distributor') {
            return true;
        }

        // RBAC validation matching ECInventoryController execution flows
        $hasPermission = DB::table('position_accessibilities')
            ->where('position_id', $user->position_id)
            ->where('permission_key', 'ec_vehicles')
            ->exists();

        return $hasPermission;
    }

    public function index()
    {
        if (!$this->checkAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $vehicles = DB::table('ecommerce_vehicles')->orderBy('id', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $vehicles]);
    }

    public function store(Request $request)
    {
        if (!$this->checkAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'plate_number' => 'required|string|unique:ecommerce_vehicles,plate_number',
            'make' => 'required|string',
            'vin' => 'nullable|string',
            'mv_file_number' => 'required|string|size:15',
            'chassis_number' => 'required|string|size:17',
            'color' => 'required|string',
            'fuel_type' => 'required|in:Gasoline,Diesel,Electric,Hybrid',
            'model' => 'required|string',
            'type' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight',
            'cr_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'or_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'proof_of_ownership' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.',
            'mv_file_number.size' => 'The MV File Number must be exactly 15 characters.',
            'chassis_number.size' => 'The Chassis Number must be exactly 17 characters.'
        ]);

        try {
            $data = [
                'plate_number' => $request->plate_number,
                'make' => $request->make,
                'vin' => $request->vin,
                'mv_file_number' => $request->mv_file_number,
                'chassis_number' => $request->chassis_number,
                'color' => $request->color,
                'fuel_type' => $request->fuel_type,
                'model' => $request->model,
                'type' => $request->type,
                'max_weight' => $request->max_weight,
                'paint_capacity' => $request->paint_capacity,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($request->hasFile('cr_file')) {
                $data['cr_file_path'] = $request->file('cr_file')->store('ecommerce_vehicles/cr', 'public');
            }
            if ($request->hasFile('or_file')) {
                $data['or_file_path'] = $request->file('or_file')->store('ecommerce_vehicles/or', 'public');
            }
            if ($request->hasFile('proof_of_ownership')) {
                $data['proof_of_ownership_path'] = $request->file('proof_of_ownership')->store('ecommerce_vehicles/proofs', 'public');
            }

            DB::table('ecommerce_vehicles')->insert($data);

            return response()->json(['status' => 'success', 'message' => 'Vehicle unit successfully registered.']);
        } catch (\Exception $e) {
            Log::error('ECommerce Fleet Unit registration breakdown: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save vehicle asset.'], 500);
        }
    }

    public function destroy($id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        DB::table('ecommerce_vehicles')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Fleet asset unit removed.']);
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'model' => 'required|string',
            'make' => 'required|string',
            'vin' => 'nullable|string',
            'mv_file_number' => 'required|string|size:15',
            'chassis_number' => 'required|string|size:17',
            'color' => 'required|string',
            'fuel_type' => 'required|in:Gasoline,Diesel,Electric,Hybrid',
            'type' => 'required|string',
            'status' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight',
            'cr_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'or_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'proof_of_ownership' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.',
            'mv_file_number.size' => 'The MV File Number must be exactly 15 characters.',
            'chassis_number.size' => 'The Chassis Number must be exactly 17 characters.'
        ]);

        try {
            $data = [
                'model' => $request->model,
                'make' => $request->make,
                'vin' => $request->vin,
                'mv_file_number' => $request->mv_file_number,
                'chassis_number' => $request->chassis_number,
                'color' => $request->color,
                'fuel_type' => $request->fuel_type,
                'type' => $request->type,
                'status' => $request->status,
                'max_weight' => $request->max_weight,
                'paint_capacity' => $request->paint_capacity,
                'updated_at' => now(),
            ];

            if ($request->hasFile('cr_file')) {
                $data['cr_file_path'] = $request->file('cr_file')->store('ecommerce_vehicles/cr', 'public');
            }
            if ($request->hasFile('or_file')) {
                $data['or_file_path'] = $request->file('or_file')->store('ecommerce_vehicles/or', 'public');
            }
            if ($request->hasFile('proof_of_ownership')) {
                $data['proof_of_ownership_path'] = $request->file('proof_of_ownership')->store('ecommerce_vehicles/proofs', 'public');
            }

            DB::table('ecommerce_vehicles')->where('id', $id)->update($data);

            return response()->json(['status' => 'success', 'message' => 'Fleet asset details modified.']);
        } catch (\Exception $e) {
            Log::error('ECommerce Fleet Unit update breakdown: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to update vehicle asset.'], 500);
        }
    }
}