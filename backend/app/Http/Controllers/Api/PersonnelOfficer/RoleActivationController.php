<?php

namespace App\Http\Controllers\Api\PersonnelOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonnelOfficer\Personnel;
use App\Models\PersonnelOfficer\PersonnelAccessibility;
use Illuminate\Support\Facades\DB;

class RoleActivationController extends Controller
{
    /**
     * Display a listing of personnel for the current supplier
     */
    public function index(Request $request)
    {
        // Get the supplier ID based on the authenticated user (either a PO or direct supplier)
        $po = $request->user()->personnelOfficer;
        $supplierId = $po ? $po->supplier_id : $request->user()->id;

        $personnels = Personnel::with(['user', 'accessibilities'])
            ->where('supplier_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $personnels
        ]);
    }

    /**
     * Activate personnel and assign modules
     */
    public function activate(Request $request, $id)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.name' => 'required|string',
            'modules.*.path' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $personnel = Personnel::findOrFail($id);
            
            // Verify permission (ensure this personnel belongs to the user's supplier)
            $po = $request->user()->personnelOfficer;
            $supplierId = $po ? $po->supplier_id : $request->user()->id;
            
            if ($personnel->supplier_id !== $supplierId) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            // 1. Update Personnel status
            $personnel->update(['status' => 'active']);

            // 2. Update linked User account status
            if ($personnel->user_id) {
                $personnel->user()->update(['status' => 'active']);
            }

            // 3. Clear existing accessibilities (if any) and insert new ones
            $personnel->accessibilities()->delete();
            
            $accessRecords = array_map(function($module) use ($personnel) {
                return [
                    'personnel_id' => $personnel->id,
                    'module_path' => $module['path'],
                    'module_name' => $module['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $request->modules);

            PersonnelAccessibility::insert($accessRecords);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Personnel activated and access granted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate personnel: ' . $e->getMessage()
            ], 500);
        }
    }
}