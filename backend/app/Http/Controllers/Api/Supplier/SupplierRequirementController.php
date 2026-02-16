<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Supplier\SupplierRequirements;
use App\Models\Supplier\SupplierAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SupplierRequirementController extends Controller
{
    /**
     * Get supplier's business verification status
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $requirements = SupplierRequirements::with('address')->where('user_id', $user->id)->first();
            
            if (!$requirements) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'is_verified' => false,
                        'verification_status' => 'none',
                        'has_submitted' => false
                    ]
                ], 200);
            }
            
            $photoUrls = $requirements->getAllPhotoUrls();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $requirements->id,
                    'company_name' => $requirements->company_name,
                    'valid_id_type' => $requirements->valid_id_type,
                    'id_type_name' => $requirements->id_type_name,
                    'id_number' => $requirements->id_number,
                    'business_registration_number' => $requirements->business_registration_number,
                    'address' => $requirements->address, 
                    'status' => $requirements->status,
                    'is_verified' => $requirements->status === 'approved',
                    'verification_status' => $requirements->status,
                    'status_class' => $requirements->status_class,
                    'rejection_reason' => $requirements->rejection_reason,
                    'is_complete' => $requirements->is_complete,
                    'has_submitted' => true,
                    'photos' => $photoUrls,
                    'submitted_at' => $requirements->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $requirements->updated_at->format('Y-m-d H:i:s')
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error fetching supplier requirements:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch business verification data'
            ], 500);
        }
    }

    /**
     * Submit business verification
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'valid_id_type' => 'required|string|in:passport,driver_license,umid,prc,postal,voter,tin,sss,philhealth,other',
                'id_number' => 'required|string|max:100',
                'valid_id_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'dti_certificate_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'mayor_permit_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'barangay_clearance_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'business_registration_number' => 'required|string|max:100',
                'business_registration_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                
                // Address Validation
                'province' => 'required|string|in:Cavite',
                'city' => 'required|string|max:255',
                'barangay' => 'required|string|max:255',
                'block_address' => 'required|string|max:1000',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $existing = SupplierRequirements::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have a ' . $existing->status . ' business verification submission'
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                $uploadData = [];
                $photoFields = [
                    'valid_id_photo',
                    'dti_certificate_photo',
                    'mayor_permit_photo',
                    'barangay_clearance_photo',
                    'business_registration_photo'
                ];
                
                foreach ($photoFields as $field) {
                    if ($request->hasFile($field)) {
                        $file = $request->file($field);
                        $fileName = $user->id . '_' . $field . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $folderPath = 'supplier_verification';
                        $path = Storage::disk('public')->putFileAs($folderPath, $file, $fileName);
                        $uploadData[$field] = $folderPath . '/' . $fileName;
                    }
                }
                
                $requirements = SupplierRequirements::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                    'valid_id_photo' => $uploadData['valid_id_photo'],
                    'dti_certificate_photo' => $uploadData['dti_certificate_photo'],
                    'mayor_permit_photo' => $uploadData['mayor_permit_photo'],
                    'barangay_clearance_photo' => $uploadData['barangay_clearance_photo'],
                    'business_registration_number' => $request->business_registration_number,
                    'business_registration_photo' => $uploadData['business_registration_photo'],
                    'status' => 'pending',
                    'rejection_reason' => null
                ]);

                SupplierAddress::create([
                    'supplier_requirements_id' => $requirements->id,
                    'province' => 'Cavite', 
                    'city' => $request->city,
                    'barangay' => $request->barangay,
                    'block_address' => $request->block_address,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);

                DB::commit();

                $photoUrls = $requirements->getAllPhotoUrls();
                $requirements->load('address');
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Business verification submitted successfully! Your documents are now pending review.',
                    'data' => [
                        'id' => $requirements->id,
                        'company_name' => $requirements->company_name,
                        'address' => $requirements->address,
                        'status' => 'pending',
                        'has_submitted' => true,
                        'photos' => $photoUrls
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error submitting supplier verification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit business verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update Supplier Info (Company name, ID numbers only)
     */
    public function updateSupplierInfo(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'supplier') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            $existing = SupplierRequirements::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update information while verification is ' . $existing->status
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'business_registration_number' => 'required|string|max:100',
                'valid_id_type' => 'required|string',
                'id_number' => 'required|string|max:100'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $requirements = SupplierRequirements::where('user_id', $user->id)->first();
            
            if (!$requirements) {
                $requirements = SupplierRequirements::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'business_registration_number' => $request->business_registration_number,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                    'status' => 'draft'
                ]);
            } else {
                $requirements->update([
                    'company_name' => $request->company_name,
                    'business_registration_number' => $request->business_registration_number,
                    'valid_id_type' => $request->valid_id_type,
                    'id_number' => $request->id_number,
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier information updated successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update info'
            ], 500);
        }
    }
}