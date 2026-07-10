<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierVehiclesController extends Controller
{
    public function index()
    {
        $vehicles = DB::table('supplier_vehicles')->orderBy('id', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $vehicles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|unique:supplier_vehicles,plate_number',
            'model' => 'required|string',
            'type' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight', // Max paint load <= Total max load validation
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.'
        ]);

        try {
            DB::table('supplier_vehicles')->insert([
                'plate_number' => $request->plate_number,
                'model' => $request->model,
                'type' => $request->type,
                'max_weight' => $request->max_weight,
                'paint_capacity' => $request->paint_capacity,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Supplier vehicle successfully registered.']);
        } catch (\Exception $e) {
            Log::error('Supplier Vehicle registration breakdown: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server failed to save supplier vehicle component.'], 500);
        }
    }

    public function destroy($id)
    {
        DB::table('supplier_vehicles')->where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Supplier vehicle removed.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'model' => 'required|string',
            'type' => 'required|string',
            'status' => 'required|string',
            'max_weight' => 'required|numeric|min:1',
            'paint_capacity' => 'required|numeric|min:0|lte:max_weight',
        ], [
            'paint_capacity.lte' => 'The max paint load cannot be greater than the total max load.'
        ]);

        DB::table('supplier_vehicles')->where('id', $id)->update([
            'model' => $request->model,
            'type' => $request->type,
            'status' => $request->status,
            'max_weight' => $request->max_weight,
            'paint_capacity' => $request->paint_capacity,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Supplier vehicle configuration changed successfully.']);
    }
}