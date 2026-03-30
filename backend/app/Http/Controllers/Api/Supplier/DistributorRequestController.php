<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\OperationDistributor\DistributorSupplier;
use App\Models\Supplier\SupplierPartner;
use App\Models\User; 

class DistributorRequestController extends Controller
{
    /**
     * Resolve the supplier ID for the current logged-in user.
     * This allows both direct Suppliers and their Personnel Officers to access data.
     */
    private function resolveSupplierId($user)
    {
        $supplierId = $user->id;
        
        // Check if the user is a personnel officer and get their parent supplier ID
        $personnelOfficer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
        if ($personnelOfficer) {
            $supplierId = $personnelOfficer->supplier_id;
        }

        return $supplierId;
    }

    /**
     * Fetch all pending and active requests for the logged-in Supplier or their staff.
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Validation check
            if (!$user) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $supplierId = $this->resolveSupplierId($user);

            // Fetch requests with status 'pending_supplier' AND 'active' for this supplier
            $requests = DistributorSupplier::where('supplier_id', $supplierId)
                ->whereIn('status', ['pending_supplier', 'active'])
                ->with([
                    'distributor' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'email', 'phone');
                    }
                ])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($req) {
                    // Send the agreement document URL and signatures to the frontend
                    $req->agreement_url = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
                    $req->distributor_signature_url = $req->distributor_signature_path ? url('storage/' . $req->distributor_signature_path) : null;
                    $req->supplier_signature_url = $req->supplier_signature_path ? url('storage/' . $req->supplier_signature_path) : null;
                    return $req;
                });

            return response()->json([
                'success' => true,
                'data' => $requests
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch requests: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve the partnership request, apply supplier signature, and activate.
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Request not found or already processed.'], 404);
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

            // Save the supplier's signature to storage
            $fileName = 'agreements/signatures/supplier_' . $supplierId . '_distributor_' . $distRequest->distributor_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            // 1. Update the document reference to ACTIVE and apply signature
            $distRequest->update([
                'status' => 'active',
                'supplier_approved_at' => now(),
                'supplier_signed_at' => now(),
                'supplier_signature_path' => $fileName,
            ]);

            // 2. Ensure the main supplier_partners table is updated to ACTIVE
            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'active']);
            } else {
                // Failsafe in case it didn't create during the internal approval phase
                SupplierPartner::create([
                    'distributor_id' => $distRequest->distributor_id,
                    'supplier_id' => $supplierId,
                    'status' => 'active',
                    'partnership_start_date' => now(),
                    'total_orders' => 0,
                    'total_sales' => 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request approved, signed, and activated.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a partnership request and send an email notification to the distributor.
     */
    public function reject(Request $request, $id)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $supplierId = $this->resolveSupplierId($user);

            // We eager load 'distributor' and 'creator' so we can access their email addresses
            $distRequest = DistributorSupplier::with(['distributor', 'creator'])
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Request not found.'], 404);
            }

            $reason = $request->input('reason', 'No specific reason provided by the supplier.');

            // Update status to rejected
            $distRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            // Also reject in the supplier_partners table to ensure sync
            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();
                
            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'rejected']);
            }

            // ==========================================
            // EMAIL NOTIFICATION LOGIC
            // ==========================================
            
            // Dynamically inject SMTP settings into the config
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'mail.mailers.smtp.port' => env('MAIL_PORT', 587),
                'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mail.mailers.smtp.username' => env('MAIL_USERNAME', 'IspyMILK@gmail.com'),
                'mail.mailers.smtp.password' => env('MAIL_PASSWORD', 'tdgtfzwlnrntrkmi'),
                'mail.from.address' => env('MAIL_FROM_ADDRESS', 'IspyMILK@gmail.com'),
                'mail.from.name' => env('MAIL_FROM_NAME', 'CaviteGoPaint'),
            ]);

            $distributorEmail = $distRequest->distributor->email ?? null;
            $creatorEmail = $distRequest->creator->email ?? null;
            
            // Get the name of the supplier to show in the email
            $supplierReqs = DB::table('supplier_requirements')->where('user_id', $supplierId)->first();
            $supplierName = $supplierReqs ? $supplierReqs->company_name : ($user->full_name ?? 'The Supplier');

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px;'>Partnership Request Declined</h2>
                    <p>Hello,</p>
                    <p>We regret to inform you that your partnership proposal to <strong>{$supplierName}</strong> has been officially declined.</p>
                    <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Reason provided by the supplier:</strong><br><br> <em>{$reason}</em></p>
                    </div>
                    <p>You can review this status update in your internal Partnership Approvals dashboard on the CaviteGoPaint system.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $creatorEmail, $supplierName) {
                    // Send main email to the Distributor Owner
                    $message->to($distributorEmail);
                    
                    // CC the specific employee/creator who made the request (only if it's a different email)
                    if ($creatorEmail && $creatorEmail !== $distributorEmail) {
                        $message->cc($creatorEmail);
                    }
                    
                    $message->subject("Partnership Request Declined - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request declined and email notification sent.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rejection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}