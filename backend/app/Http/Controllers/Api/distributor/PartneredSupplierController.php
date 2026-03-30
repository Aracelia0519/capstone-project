<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Supplier\SupplierPartner;
use App\Models\User; 

class PartneredSupplierController extends Controller
{
    /**
     * Get all active/partnered suppliers for the current distributor.
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Ensure the user is a distributor
            if (!$user || !$user->isDistributor()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            // Fetch partnerships
            $partnerships = SupplierPartner::where('distributor_id', $user->id)
                ->with([
                    'supplier', 
                    'supplier.supplierRequirements',
                    'supplier.supplierRequirements.address'
                ])
                ->get();

            // Transform data for the frontend
            $data = $partnerships->map(function ($partner) use ($user) {
                $supplier = $partner->supplier;
                $req = $supplier->supplierRequirements;
                $address = $req ? $req->address : null;

                // Format Address
                $fullAddress = 'Address not available';
                $locationShort = 'N/A';
                
                if ($address) {
                    $fullAddress = "{$address->block_address}, {$address->barangay}, {$address->city}, {$address->province}";
                    $locationShort = "{$address->city}, {$address->province}";
                }

                // Fetch the actual documented agreement from distributor_suppliers table
                $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                    ->where('supplier_id', $supplier->id)
                    ->first();
                
                $agreementUrl = $distSupplier && $distSupplier->agreement_path 
                    ? url('storage/' . $distSupplier->agreement_path) 
                    : null;

                // Distributor Signature
                $distributorSignatureUrl = $distSupplier && $distSupplier->distributor_signature_path 
                    ? url('storage/' . $distSupplier->distributor_signature_path) 
                    : null;
                
                // Supplier Signature
                $supplierSignatureUrl = $distSupplier && $distSupplier->supplier_signature_path 
                    ? url('storage/' . $distSupplier->supplier_signature_path) 
                    : null;
                    
                $isSigned = $distSupplier && $distSupplier->distributor_signed_at !== null;
                
                $signedAt = $distSupplier && $distSupplier->distributor_signed_at 
                    ? $distSupplier->distributor_signed_at->format('M d, Y h:i A') 
                    : null;

                $supplierSignedAt = $distSupplier && $distSupplier->supplier_signed_at 
                    ? $distSupplier->supplier_signed_at->format('M d, Y h:i A') 
                    : null;

                return [
                    'id' => $partner->id, 
                    'supplier_user_id' => $supplier->id,
                    'company_name' => $req ? $req->company_name : $supplier->full_name,
                    'contact_person' => $supplier->full_name,
                    'email' => $supplier->email,
                    'phone' => $supplier->phone ?? 'N/A',
                    'category' => 'General Supply', 
                    'rating' => 5.0, 
                    'fulfillment_rate' => 100, 
                    'location' => $locationShort,
                    'address' => $fullAddress,
                    'status' => $partner->status, 
                    'total_orders' => $partner->total_orders,
                    'total_spend' => $partner->total_sales, 
                    'on_time_rate' => 95, 
                    'id_number' => $req ? $req->id_number : 'N/A',
                    'partnership_date' => $partner->partnership_start_date->format('M d, Y'),
                    
                    // Appended Agreement & Signatures Payload
                    'agreement_url' => $agreementUrl,
                    'is_signed' => $isSigned,
                    'signed_at' => $signedAt,
                    'supplier_signed_at' => $supplierSignedAt,
                    'distributor_signature_url' => $distributorSignatureUrl,
                    'supplier_signature_url' => $supplierSignatureUrl,
                    'distributor_supplier_id' => $distSupplier ? $distSupplier->id : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch partners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch the raw HTML content of the agreement for the frontend to merge signatures.
     */
    public function getAgreementRaw($id)
    {
        try {
            $user = Auth::user();
            
            // Find the active partnership reference
            $partner = SupplierPartner::where('id', $id)
                ->where('distributor_id', $user->id)
                ->firstOrFail();
            
            // Find the actual document record
            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            if (!$distSupplier || !$distSupplier->agreement_path) {
                return response()->json(['success' => false, 'message' => 'Agreement document not found.'], 404);
            }

            // Read the file directly from storage to bypass CORS issues on the frontend
            $htmlContent = Storage::disk('public')->get($distSupplier->agreement_path);

            return response()->json([
                'success' => true,
                'html' => $htmlContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch agreement content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Digitally sign the partnership agreement with an image.
     */
    public function signAgreement(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            
            // Find the active partnership reference
            $partner = SupplierPartner::where('id', $id)
                ->where('distributor_id', $user->id)
                ->firstOrFail();
            
            // Find the actual document record in the distributor_suppliers table
            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            if (!$distSupplier) {
                return response()->json(['success' => false, 'message' => 'Agreement document not found.'], 404);
            }

            if ($distSupplier->distributor_signed_at) {
                return response()->json(['success' => false, 'message' => 'Agreement is already signed.'], 400);
            }

            // Process Base64 Signature Image
            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            
            if (count($imageParts) !== 2) {
                return response()->json(['success' => false, 'message' => 'Invalid signature format.'], 400);
            }

            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            // Save the image
            $fileName = 'agreements/signatures/distributor_' . $user->id . '_supplier_' . $partner->supplier_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            // Apply signature, timestamp, and update STATUS to pending_supplier
            $distSupplier->distributor_signed_at = now();
            $distSupplier->distributor_signature_path = $fileName;
            $distSupplier->status = 'pending_supplier';
            $distSupplier->save();

            // Update the main SupplierPartner model's status to match
            $partner->status = 'pending_supplier';
            $partner->save();

            return response()->json([
                'success' => true,
                'message' => 'Agreement successfully signed.',
                'signed_at' => $distSupplier->distributor_signed_at->format('M d, Y h:i A')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign agreement: ' . $e->getMessage()
            ], 500);
        }
    }
}