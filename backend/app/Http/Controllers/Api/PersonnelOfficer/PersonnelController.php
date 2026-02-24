<?php

namespace App\Http\Controllers\Api\PersonnelOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PersonnelOfficer\Personnel;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $user = new User();
            $user->first_name = $request->firstName;
            $user->last_name = $request->lastName;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = $request->password; 
            $user->role = 'supplier_employee';
            $user->status = 'pending'; 
            $user->save();

            // 2. Handle File Uploads
            $paths = [];
            $fileFields = ['validIdPhoto', 'resume', 'employmentContract', 'medicalCertificate', 'nbiClearance', 'policeClearance'];
            
            foreach($fileFields as $field) {
                if ($request->hasFile($field)) {
                    // Convert camelCase field to snake_case for DB column names
                    $dbField = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $field));
                    $paths[$dbField] = $request->file($field)->store("supplier/personnel/{$dbField}s", 'public');
                }
            }

            // 3. Determine the actual Personnel Role string
            $personnelType = $request->personnelType === 'other' ? $request->customPersonnelType : $request->personnelType;

            // 4. Resolve Supplier ID (from the authenticated Personnel Officer)
            $po = $request->user()->personnelOfficer;
            $supplierId = $po ? $po->supplier_id : $request->user()->id;

            // 5. Create Personnel Record
            $personnel = Personnel::create(array_merge([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'created_by' => $request->user()->id,
                'first_name' => $request->firstName,
                'middle_name' => $request->middleName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'emergency_contact' => $request->emergencyContact,
                'date_of_birth' => $request->dateOfBirth,
                'gender' => $request->gender,
                'marital_status' => $request->maritalStatus,
                'nationality' => $request->nationality,
                'address' => $request->address,
                'personnel_type' => $personnelType,
                'employment_type' => $request->employmentType,
                'hire_date' => $request->hireDate,
                'bank_name' => $request->bankName,
                'bank_account_number' => $request->bankAccountNumber,
                'bank_account_name' => $request->bankAccountName,
                'sss_number' => $request->sssNumber,
                'philhealth_number' => $request->philhealthNumber,
                'pagibig_number' => $request->pagibigNumber,
                'tin_number' => $request->tinNumber,
                'educational_attainment' => $request->educationalAttainment,
                'school_graduated' => $request->schoolGraduated,
                'year_graduated' => $request->yearGraduated,
                'course' => $request->course,
                'valid_id_type' => $request->validIdType,
                'id_number' => $request->idNumber,
                'notes' => $request->notes,
                'status' => 'pending', 
            ], $paths));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Personnel created successfully.',
                'data' => $personnel
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register personnel. ' . $e->getMessage()
            ], 500);
        }
    }
}