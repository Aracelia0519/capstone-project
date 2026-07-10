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
            'model' => 'required|string',
            'type' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight', // Max paint load <= Total max load validation
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.'
        ]);

        try {
            DB::table('ecommerce_vehicles')->insert([
                'plate_number' => $request->plate_number,
                'model' => $request->model,
                'type' => $request->type,
                'max_weight' => $request->max_weight,
                'paint_capacity' => $request->paint_capacity,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
            'type' => 'required|string',
            'status' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight',
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.'
        ]);

        DB::table('ecommerce_vehicles')->where('id', $id)->update([
            'model' => $request->model,
            'type' => $request->type,
            'status' => $request->status,
            'max_weight' => $request->max_weight,
            'paint_capacity' => $request->paint_capacity,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Fleet asset details modified.']);
    }
}