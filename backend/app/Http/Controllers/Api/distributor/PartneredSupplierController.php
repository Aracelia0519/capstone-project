<?php

namespace App\Http\Controllers\Api\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
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
                    
                // Termination Documents and Signatures
                $terminationUrl = $distSupplier && $distSupplier->termination_path 
                    ? url('storage/' . $distSupplier->termination_path) 
                    : null;

                $distributorTermSigUrl = $distSupplier && $distSupplier->distributor_termination_signature_path 
                    ? url('storage/' . $distSupplier->distributor_termination_signature_path) 
                    : null;
                
                $supplierTermSigPath = $distSupplier->supplier_termination_signature_path ?? null;
                $supplierTermSigUrl = $supplierTermSigPath ? url('storage/' . $supplierTermSigPath) : null;

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
                    
                    // Appended Termination Payload
                    'termination_url' => $terminationUrl,
                    'distributor_termination_signature_url' => $distributorTermSigUrl,
                    'supplier_termination_signature_url' => $supplierTermSigUrl,
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
     * Fetch raw HTML for Termination Agreement and Verify Pending Requests
     */
    public function getTerminationRaw($id)
    {
        try {
            $user = Auth::user();
            
            $partner = SupplierPartner::where('id', $id)
                ->where('distributor_id', $user->id)
                ->firstOrFail();
                
            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            // Check if the physical document is already generated. If yes, return it.
            if ($distSupplier && $distSupplier->termination_path) {
                $htmlContent = Storage::disk('public')->get($distSupplier->termination_path);
                return response()->json([
                    'success' => true,
                    'html' => $htmlContent
                ]);
            }
            
            // SECURITY CHECK: Verify if there are active procurement requests
            $hasPending = DB::table('procurement_requests')
                ->where('supplier_id', $partner->supplier_id)
                ->where('distributor_id', $user->id)
                ->whereNotIn('status', ['delivered', 'completed', 'rejected', 'cancelled'])
                ->exists();

            if ($hasPending) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please complete all transaction first, before terminating the partnership'
                ], 400);
            }

            // Generate HTML for the termination notice (Fresh Template)
            $supplierName = $partner->supplier->full_name ?? 'Supplier'; 
            $distributorName = $user->full_name ?? 'Distributor';
            $date = now()->format('F d, Y');
            
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; padding: 40px; color: #333;'>
                    <h2 style='text-align: center; color: #dc2626; text-transform: uppercase;'>Official Notice of Partnership Termination</h2>
                    <hr style='border: 1px solid #e2e8f0; margin: 20px 0;' />
                    <p><strong>Date:</strong> {$date}</p>
                    <p><strong>To:</strong> {$supplierName}</p>
                    <p><strong>From:</strong> {$distributorName}</p>
                    <br/>
                    <p>This document serves as an official declaration to terminate the supply chain partnership between the aforementioned parties.</p>
                    <p>By applying a signature below, the distributor formally initiates the termination procedure. This termination will be placed in a pending state until reviewed and countersigned by the supplier.</p>
                    <p>Both parties agree that all outstanding obligations, procurement transactions, and financial balances have been fully settled prior to generating this document.</p>
                </div>
            ";

            return response()->json([
                'success' => true,
                'html' => $htmlContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate termination: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terminate Partnership, apply signature, and email supplier.
     */
    public function terminatePartnership(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            
            $partner = SupplierPartner::with('supplier')->where('id', $id)
                ->where('distributor_id', $user->id)
                ->firstOrFail();

            // Re-validate pending requests just in case
            $hasPending = DB::table('procurement_requests')
                ->where('supplier_id', $partner->supplier_id)
                ->where('distributor_id', $user->id)
                ->whereNotIn('status', ['delivered', 'completed', 'rejected', 'cancelled'])
                ->exists();

            if ($hasPending) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please complete all transaction first, before terminating the partnership'
                ], 400);
            }

            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            // Process Base64 Signature Image
            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            
            if (count($imageParts) !== 2) {
                return response()->json(['success' => false, 'message' => 'Invalid signature format.'], 400);
            }

            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            // Save the signature
            $fileName = 'agreements/terminations/signatures/distributor_' . $user->id . '_supplier_' . $partner->supplier_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            // Generate physical HTML document to save in storage
            $supplierName = $partner->supplier->full_name ?? 'Supplier'; 
            $distributorName = $user->full_name ?? 'Distributor';
            $date = now()->format('F d, Y');
            
            $docHtml = "
                <div style='font-family: Arial, sans-serif; padding: 40px; color: #333;'>
                    <h2 style='text-align: center; color: #dc2626; text-transform: uppercase;'>Official Notice of Partnership Termination</h2>
                    <hr style='border: 1px solid #e2e8f0; margin: 20px 0;' />
                    <p><strong>Date:</strong> {$date}</p>
                    <p><strong>To:</strong> {$supplierName}</p>
                    <p><strong>From:</strong> {$distributorName}</p>
                    <br/>
                    <p>This document serves as an official declaration to terminate the supply chain partnership between the aforementioned parties.</p>
                    <p>By applying a signature below, the distributor formally initiates the termination procedure. This termination will be placed in a pending state until reviewed and countersigned by the supplier.</p>
                    <p>Both parties agree that all outstanding obligations, procurement transactions, and financial balances have been fully settled prior to generating this document.</p>
                </div>
            ";
            
            $htmlFileName = 'agreements/terminations/documents/termination_' . $user->id . '_' . $partner->supplier_id . '_' . time() . '.html';
            Storage::disk('public')->put($htmlFileName, $docHtml);

            // Ensure our ENUM allows the pending_termination state dynamically
            try {
                DB::statement("ALTER TABLE distributor_suppliers MODIFY COLUMN status ENUM('pending_internal','pending_supplier','active','rejected','disconnected','pending_termination','terminated','pending_reactivation') DEFAULT 'pending_internal'");
            } catch (\Exception $e) {
                // If it fails due to permissions, it will fallback to safely continuing with standard updating
            }

            // Add required tracking columns if they don't exist yet
            if (!Schema::hasColumn('distributor_suppliers', 'termination_path')) {
                Schema::table('distributor_suppliers', function ($table) {
                    $table->string('termination_path')->nullable();
                    $table->timestamp('distributor_termination_signed_at')->nullable();
                    $table->string('distributor_termination_signature_path')->nullable();
                });
            }

            if ($distSupplier) {
                $distSupplier->termination_path = $htmlFileName;
                $distSupplier->distributor_termination_signed_at = now();
                $distSupplier->distributor_termination_signature_path = $fileName;
                $distSupplier->status = 'pending_termination';
                $distSupplier->save();
            }

            // Update SupplierPartner Table Status
            $partner->status = 'pending_termination';
            $partner->save();

            // ==========================================
            // EMAIL NOTIFICATION LOGIC TO SUPPLIER
            // ==========================================
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => 'IspyMILK@gmail.com',
                'mail.mailers.smtp.password' => 'tdgtfzwlnrntrkmi',
                'mail.from.address' => 'IspyMILK@gmail.com',
                'mail.from.name' => 'CaviteGoPaint',
            ]);

            $supplierEmail = $partner->supplier->email ?? null;

            if ($supplierEmail) {
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h2 style='color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px;'>Partnership Termination Request</h2>
                        <p>Hello,</p>
                        <p>The distributor <strong>{$distributorName}</strong> has submitted an official request to terminate your supply partnership.</p>
                        <p>A termination document has been generated and pre-signed by the distributor. Please log in to the CaviteGoPaint system to review the document and provide your final approval signature to complete the termination process.</p>
                        <br>
                        <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                    </div>
                ";

                Mail::html($emailHtml, function ($message) use ($supplierEmail, $distributorName) {
                    $message->to($supplierEmail);
                    $message->subject("Partnership Termination Notice from {$distributorName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Termination successfully requested and email sent.',
                'status' => 'pending_termination'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Termination failed: ' . $e->getMessage()
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

    /**
     * Reactivate a terminated partnership by submitting a new signature to the original agreement.
     */
    public function reactivatePartnership(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            
            // Fetch partnership checking it is in a valid state to be reactivated
            $partner = SupplierPartner::with('supplier')->where('id', $id)
                ->where('distributor_id', $user->id)
                ->whereIn('status', ['terminated', 'disconnected'])
                ->firstOrFail();

            $distSupplier = \App\Models\OperationDistributor\DistributorSupplier::where('distributor_id', $user->id)
                ->where('supplier_id', $partner->supplier_id)
                ->first();

            if (!$distSupplier) {
                return response()->json(['success' => false, 'message' => 'Original agreement document not found.'], 404);
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

            // Save the new signature
            $fileName = 'agreements/signatures/distributor_reactivation_' . $user->id . '_supplier_' . $partner->supplier_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            // Dynamically ensure DB Enum support
            try {
                DB::statement("ALTER TABLE distributor_suppliers MODIFY COLUMN status ENUM('pending_internal','pending_supplier','active','rejected','disconnected','pending_termination','terminated','pending_reactivation') DEFAULT 'pending_internal'");
                DB::statement("ALTER TABLE supplier_partners MODIFY COLUMN status ENUM('active','suspended','terminated','rejected','pending_termination','pending_supplier','pending_reactivation') DEFAULT 'active'");
            } catch (\Exception $e) {
                // Ignore if it fails due to permissions
            }

            // Reset document states to represent a fresh agreement process for reactivation
            $distSupplier->distributor_signature_path = $fileName;
            $distSupplier->distributor_signed_at = now();
            
            // Clear supplier signature so they have to agree again
            $distSupplier->supplier_signature_path = null;
            $distSupplier->supplier_signed_at = null;
            
            // Wipe termination history trace for this active flow
            $distSupplier->termination_path = null;
            $distSupplier->distributor_termination_signature_path = null;
            $distSupplier->distributor_termination_signed_at = null;
            $distSupplier->supplier_termination_signature_path = null;
            $distSupplier->supplier_termination_signed_at = null;
            
            $distSupplier->status = 'pending_reactivation';
            $distSupplier->save();

            // Update main record
            $partner->status = 'pending_reactivation';
            $partner->save();

            // ==========================================
            // EMAIL NOTIFICATION LOGIC TO SUPPLIER
            // ==========================================
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => 'IspyMILK@gmail.com',
                'mail.mailers.smtp.password' => 'tdgtfzwlnrntrkmi',
                'mail.from.address' => 'IspyMILK@gmail.com',
                'mail.from.name' => 'CaviteGoPaint',
            ]);

            $supplierEmail = $partner->supplier->email ?? null;
            $distributorName = $user->full_name ?? 'Distributor';

            if ($supplierEmail) {
                $emailHtml = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h2 style='color: #4f46e5; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;'>Partnership Reactivation Request</h2>
                        <p>Hello,</p>
                        <p>The distributor <strong>{$distributorName}</strong> has submitted a request to <strong>reactivate</strong> your previously terminated supply partnership under the original terms and conditions.</p>
                        <p>They have already signed the agreement. Please log in to the CaviteGoPaint system to review and countersign the document to officially restore the partnership.</p>
                        <br>
                        <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                    </div>
                ";

                Mail::html($emailHtml, function ($message) use ($supplierEmail, $distributorName) {
                    $message->to($supplierEmail);
                    $message->subject("Partnership Reactivation Request from {$distributorName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Reactivation requested successfully and email sent.',
                'status' => 'pending_reactivation'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate partnership: ' . $e->getMessage()
            ], 500);
        }
    }
}