<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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
     * Fetch all pending, active, termination, and reactivation requests for the logged-in Supplier.
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $supplierId = $this->resolveSupplierId($user);

            $requests = DistributorSupplier::where('supplier_id', $supplierId)
                ->whereIn('status', ['pending_supplier', 'active', 'pending_termination', 'terminated', 'disconnected', 'pending_reactivation'])
                ->with([
                    'distributor' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'email', 'phone');
                    }
                ])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $req->agreement_url = $req->agreement_path ? url('storage/' . $req->agreement_path) : null;
                    $req->distributor_signature_url = $req->distributor_signature_path ? url('storage/' . $req->distributor_signature_path) : null;
                    $req->supplier_signature_url = $req->supplier_signature_path ? url('storage/' . $req->supplier_signature_path) : null;
                    
                    // Termination mappings
                    $req->termination_url = $req->termination_path ? url('storage/' . $req->termination_path) : null;
                    $req->distributor_termination_signature_url = $req->distributor_termination_signature_path ? url('storage/' . $req->distributor_termination_signature_path) : null;
                    
                    // Add column dynamically if it doesn't exist yet in the mapping
                    $supplierTermSig = $req->supplier_termination_signature_path ?? null;
                    $req->supplier_termination_signature_url = $supplierTermSig ? url('storage/' . $supplierTermSig) : null;

                    // Dynamically fetch and append the distributor's company name
                    $distributorReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
                    if ($distributorReq && $req->distributor) {
                        $req->distributor->company_name = $distributorReq->company_name;
                    }

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
     * Approve the initial partnership request, apply supplier signature, activate, and email distributor.
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

            $distRequest = DistributorSupplier::with(['distributor', 'creator'])
                ->where('id', $id)
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

            $distRequest->update([
                'status' => 'active',
                'supplier_approved_at' => now(),
                'supplier_signed_at' => now(),
                'supplier_signature_path' => $fileName,
            ]);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'active']);
            } else {
                SupplierPartner::create([
                    'distributor_id' => $distRequest->distributor_id,
                    'supplier_id' => $supplierId,
                    'status' => 'active',
                    'partnership_start_date' => now(),
                    'total_orders' => 0,
                    'total_sales' => 0,
                ]);
            }

            $this->setupSMTPConfig();

            $distributorEmail = $distRequest->distributor->email ?? null;
            $creatorEmail = $distRequest->creator->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px;'>Partnership Request Approved</h2>
                    <p>Hello,</p>
                    <p>Great news! Your partnership proposal to <strong>{$supplierName}</strong> has been officially approved and signed.</p>
                    <p>You can now access their full product catalog and initiate procurement requests directly through the CaviteGoPaint system.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $creatorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    if ($creatorEmail && $creatorEmail !== $distributorEmail) {
                        $message->cc($creatorEmail);
                    }
                    $message->subject("Partnership Request Approved - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request approved, signed, and activated. Email sent.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject an initial partnership request and send an email notification.
     */
    public function reject(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with(['distributor', 'creator'])
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_supplier')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Request not found.'], 404);
            }

            $reason = $request->input('reason', 'No specific reason provided by the supplier.');

            $distRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();
                
            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'rejected']);
            }

            $this->setupSMTPConfig();

            $distributorEmail = $distRequest->distributor->email ?? null;
            $creatorEmail = $distRequest->creator->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

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
                    $message->to($distributorEmail);
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
            return response()->json(['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fetch the raw HTML content of the termination agreement for downloading.
     */
    public function getTerminationRaw($id)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);
            
            $distSupplier = DistributorSupplier::where('id', $id)
                ->where('supplier_id', $supplierId)
                ->firstOrFail();

            if (!$distSupplier || !$distSupplier->termination_path) {
                return response()->json(['success' => false, 'message' => 'Termination document not found.'], 404);
            }

            $htmlContent = Storage::disk('public')->get($distSupplier->termination_path);

            return response()->json([
                'success' => true,
                'html' => $htmlContent
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch document: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve termination, save supplier signature, end partnership, send email.
     */
    public function approveTermination(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with('distributor')
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_termination')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Termination request not found.'], 404);
            }

            if (!Schema::hasColumn('distributor_suppliers', 'supplier_termination_signature_path')) {
                Schema::table('distributor_suppliers', function ($table) {
                    $table->string('supplier_termination_signature_path')->nullable();
                    $table->timestamp('supplier_termination_signed_at')->nullable();
                });
            }

            try {
                DB::statement("ALTER TABLE distributor_suppliers MODIFY COLUMN status ENUM('pending_internal','pending_supplier','active','rejected','disconnected','pending_termination','terminated','pending_reactivation') DEFAULT 'pending_internal'");
            } catch (\Exception $e) {}

            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'agreements/terminations/signatures/supplier_countersign_' . $supplierId . '_distributor_' . $distRequest->distributor_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            $distRequest->supplier_termination_signature_path = $fileName;
            $distRequest->supplier_termination_signed_at = now();
            $distRequest->status = 'terminated';
            $distRequest->save();

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                try {
                    DB::statement("ALTER TABLE supplier_partners MODIFY COLUMN status ENUM('active','suspended','terminated','rejected','pending_termination','pending_supplier','pending_reactivation') DEFAULT 'active'");
                } catch (\Exception $e) {}
                
                $supplierPartner->update(['status' => 'terminated']);
            }

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #475569; border-bottom: 2px solid #475569; padding-bottom: 10px;'>Partnership Officially Terminated</h2>
                    <p>Hello,</p>
                    <p>This is to formally notify you that your request to terminate the partnership with <strong>{$supplierName}</strong> has been approved and countersigned.</p>
                    <p>The supply chain connection has been successfully severed and is now inactive. You may download the final signed termination document from your dashboard.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    $message->subject("Partnership Officially Terminated - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Termination successfully approved, signed, and distributor notified.',
                'signature_url' => url('storage/' . $fileName),
                'signed_at' => $distRequest->supplier_termination_signed_at->format('M d, Y h:i A')
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Termination approval failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Decline termination request, restore active status, email distributor.
     */
    public function rejectTermination(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with('distributor')
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_termination')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Termination request not found.'], 404);
            }

            $distRequest->update(['status' => 'active']);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'active']);
            }

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);
            $reason = $request->input('reason', 'No specific reason provided.');

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px;'>Termination Request Declined</h2>
                    <p>Hello,</p>
                    <p>Your request to terminate the partnership with <strong>{$supplierName}</strong> has been declined by the supplier.</p>
                    <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Reason provided:</strong><br><br> <em>{$reason}</em></p>
                    </div>
                    <p>Your partnership status has been restored to <strong>Active</strong>. Please contact the supplier directly for further clarification.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    $message->subject("Termination Request Declined - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Termination declined, partnership restored to active. Email sent.',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Declining termination failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve Reactivation: Process supplier's signature, set status to active, notify distributor.
     */
    public function approveReactivation(Request $request, $id)
    {
        $request->validate([
            'signature_image' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with('distributor')
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_reactivation')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Reactivation request not found.'], 404);
            }

            $base64Image = $request->signature_image;
            $imageParts = explode(";base64,", $base64Image);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'png';
            $imageBase64 = base64_decode($imageParts[1]);

            $fileName = 'agreements/signatures/supplier_reactivation_' . $supplierId . '_distributor_' . $distRequest->distributor_id . '_' . time() . '.' . $imageType;
            Storage::disk('public')->put($fileName, $imageBase64);

            $distRequest->supplier_signature_path = $fileName;
            $distRequest->supplier_signed_at = now();
            $distRequest->status = 'active';
            $distRequest->save();

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'active']);
            }

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px;'>Partnership Reactivation Approved</h2>
                    <p>Hello,</p>
                    <p>Great news! Your request to reactivate the partnership with <strong>{$supplierName}</strong> has been formally approved and countersigned.</p>
                    <p>Your partnership status has been restored to <strong>Active</strong>. You can now resume your supply chain operations and procurements seamlessly.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    $message->subject("Partnership Reactivation Approved - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Reactivation approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Reactivation approval failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Decline Reactivation: Revert to terminated, notify distributor.
     */
    public function rejectReactivation(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $supplierId = $this->resolveSupplierId($user);

            $distRequest = DistributorSupplier::with('distributor')
                ->where('id', $id)
                ->where('supplier_id', $supplierId)
                ->where('status', 'pending_reactivation')
                ->first();

            if (!$distRequest) {
                return response()->json(['message' => 'Reactivation request not found.'], 404);
            }

            // Keep status as terminated
            $distRequest->update(['status' => 'terminated']);

            $supplierPartner = SupplierPartner::where('distributor_id', $distRequest->distributor_id)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($supplierPartner) {
                $supplierPartner->update(['status' => 'terminated']);
            }

            $this->setupSMTPConfig();
            $distributorEmail = $distRequest->distributor->email ?? null;
            $supplierName = $this->getSupplierName($user, $supplierId);
            $reason = $request->input('reason', 'No specific reason provided.');

            $htmlMessage = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h2 style='color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px;'>Reactivation Request Declined</h2>
                    <p>Hello,</p>
                    <p>Your request to reactivate the partnership with <strong>{$supplierName}</strong> has been declined.</p>
                    <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;'>
                        <p style='margin: 0;'><strong>Reason provided:</strong><br><br> <em>{$reason}</em></p>
                    </div>
                    <p>Your partnership status remains <strong>Terminated</strong>.</p>
                    <br>
                    <p style='color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0; padding-top: 15px;'>Best regards,<br><strong>CaviteGoPaint System</strong></p>
                </div>
            ";

            if ($distributorEmail) {
                Mail::html($htmlMessage, function ($message) use ($distributorEmail, $supplierName) {
                    $message->to($distributorEmail);
                    $message->subject("Reactivation Request Declined - {$supplierName}");
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Reactivation declined. Partnership kept as terminated.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Declining reactivation failed: ' . $e->getMessage()], 500);
        }
    }


    // --- Shared Helper Methods --- //

    private function getSupplierName($user, $supplierId)
    {
        $supplierReqs = DB::table('supplier_requirements')->where('user_id', $supplierId)->first();
        return $supplierReqs ? $supplierReqs->company_name : ($user->full_name ?? 'The Supplier');
    }

    private function setupSMTPConfig()
    {
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
    }
}