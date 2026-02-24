<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Supplier\PersonnelOfficer;

class PersonnelOfficerController extends Controller
{
    /**
     * Store a newly created personnel officer in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'employment_type' => 'required|in:full_time,part_time,contract,temporary',
            'hire_date' => 'required|date',
            'position' => 'required|string|max:255',
            'valid_id_type' => 'required|string|max:255',
            'id_number' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'valid_id_photo' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'employment_contract' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // FIX: Used $request->user()->id to clear the Intelephense "Undefined method 'id'" error
            $supplierId = $request->user()->id;

            // Create User Account for Personnel Officer
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'role' => 'personnel_officer',
                'status' => 'active', 
            ]);

            // Handle File Uploads
            $validIdPath = null;
            $resumePath = null;
            $contractPath = null;

            if ($request->hasFile('valid_id_photo')) {
                $validIdPath = $request->file('valid_id_photo')->store('supplier/personnel_officers/ids', 'public');
            }

            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('supplier/personnel_officers/resumes', 'public');
            }

            if ($request->hasFile('employment_contract')) {
                $contractPath = $request->file('employment_contract')->store('supplier/personnel_officers/contracts', 'public');
            }

            // Create Personnel Officer Profile
            $personnelOfficer = PersonnelOfficer::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'position' => $request->position,
                'valid_id_type' => $request->valid_id_type,
                'id_number' => $request->id_number,
                'valid_id_photo' => $validIdPath,
                'resume' => $resumePath,
                'employment_contract' => $contractPath,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Personnel Officer created successfully',
                'data' => [
                    'personnel_officer' => $personnelOfficer,
                    'user' => $user
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create Personnel Officer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}