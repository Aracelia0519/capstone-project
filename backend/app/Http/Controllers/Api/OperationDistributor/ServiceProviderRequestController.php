<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceProviderRequestController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module.
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_manage' => true,
                'can_approve' => true
            ];
        }

        // Check RBAC for standard employees
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_manage' => (bool) $access->can_manage,
                    'can_approve' => (bool) $access->can_approve,
                ];
            }
        }

        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action)
    {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    /**
     * Fetch all service provider requests for the current distributor
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get permissions and check RBAC Read Access using 'ec_service_provider'
            $permissions = $this->getPermissions($user, 'ec_service_provider');
            
            if (!$permissions['can_view']) {
                return response()->json(['message' => 'Access Denied: You do not have permission to view service provider requests.'], 403);
            }

            // Logic to get Distributor ID based on the user role
            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            if (!$distributorId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access. Distributor context not found.'], 403);
            }

            $requests = ServiceProviderDistributor::with(['serviceProvider.serviceProviderRequirement'])
                ->where('distributor_id', $distributorId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $sp = $req->serviceProvider;
                    $spReq = $sp ? $sp->serviceProviderRequirement : null;
                    
                    $addressStr = 'Address not provided';
                    $location = 'Location not provided';
                    
                    if ($spReq) {
                        $addr = DB::table('service_provider_addresses')->where('service_provider_requirements_id', $spReq->id)->first();
                        if ($addr) {
                            $location = "{$addr->city}, {$addr->province}";
                            $addressStr = "{$addr->block_address}, {$addr->barangay}, {$addr->city}, {$addr->province}";
                        }
                    }

                    return [
                        'id' => $req->id,
                        'provider_name' => $sp ? $sp->full_name : 'Unknown',
                        'email' => $sp ? $sp->email : 'Unknown',
                        'phone' => $sp ? $sp->phone : 'Unknown',
                        'location' => $location,
                        'full_address' => $addressStr,
                        'date_requested' => $req->created_at->format('Y-m-d'),
                        'message' => $req->request_message,
                        'status' => $req->status,
                        'agreement_url' => $req->agreement_path ? asset('storage/' . $req->agreement_path) : null,
                        'sp_signature_url' => $req->sp_signature_path ? asset('storage/' . $req->sp_signature_path) : null,
                    ];
                });

            return response()->json([
                'success' => true, 
                'data' => $requests,
                'permissions' => $permissions
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requests', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve partnership request, generate agreement, and dispatch emails (Background Lazy Load)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_approve')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to approve partnership requests.'], 403);
            }

            $request->validate([
                'signature' => 'required|string'
            ]);

            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)
                    ->where('distributor_id', $distributorId)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found or unauthorized.'], 404);
            }

            // Save Signature to Disk
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                if (count($imageParts) == 2) {
                    $imageTypeAux = explode("image/", $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageBase64 = base64_decode($imageParts[1]);
                    
                    $fileName = 'dist_' . $distributorId . '_sp_' . $req->service_provider_id . '_' . time() . '.' . $imageType;
                    $signaturePath = 'agreements/signatures/' . $fileName;
                    Storage::disk('public')->put($signaturePath, $imageBase64);
                }
            }

            // Data for Agreement & Emails
            $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');

            // --- EMBED SIGNATURES AS BASE64 IN HTML ---
            // Fetch SP Signature and convert to base64
            $spSignatureBase64Str = '';
            if ($req->sp_signature_path && Storage::disk('public')->exists($req->sp_signature_path)) {
                $spImageRaw = Storage::disk('public')->get($req->sp_signature_path);
                $spSignatureBase64Str = 'data:image/png;base64,' . base64_encode($spImageRaw);
            }

            // Distributor signature is already provided natively via request as base64!
            $distSignatureBase64Str = $request->signature ?? '';

            // --- Generate Formal Agreement HTML ---
            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Partnership Agreement</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; }
                    h1 { color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 5px; }
                    .date-ref { text-align: right; margin-bottom: 30px; font-weight: bold; color: #475569; }
                    .parties { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
                    .terms { margin-bottom: 40px; }
                    .terms h3 { color: #334155; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 50px; gap: 40px; }
                    .signature-box { width: 45%; }
                    .signature-line { border-bottom: 1px solid #94a3b8; margin-bottom: 10px; height: 120px; display: flex; align-items: flex-end; justify-content: center; position: relative; }
                    .signature-line img { max-height: 100px; max-width: 100%; margin-bottom: 5px; }
                    .sig-label { font-size: 14px; font-weight: bold; margin-bottom: 5px; }
                    .sig-details { font-size: 14px; color: #475569; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>Official Partnership Agreement</h1>
                    <p>Commercial Supply Relationship</p>
                </div>

                <div class='date-ref'>Effective Date: {$dateStr}</div>
                
                <div class='parties'>
                    <p>This Partnership Agreement (the \"Agreement\") is formally entered into by and between:</p>
                    <p><strong>Distributor:</strong> {$distName}</p>
                    <p><strong>Service Provider:</strong> {$spName}</p>
                </div>
                
                <div class='terms'>
                    <h3>Terms and Conditions</h3>
                    <p><strong>1. Authorization of Access:</strong> By executing this document, the Distributor authorizes the Service Provider to access the wholesale catalog, view pricing tiers, and submit procurement requests through the platform.</p>
                    <p><strong>2. Procurement and Pricing:</strong> The Service Provider shall be eligible to procure materials under established partner-tier pricing. The Distributor reserves the exclusive right to modify pricing tiers and stock availability.</p>
                    <p><strong>3. Compliance and Representation:</strong> The Service Provider agrees to maintain all necessary local business licenses and industry certifications.</p>
                    <p><strong>4. Confidentiality:</strong> Both parties mutually agree to hold in strict confidence any sensitive business intelligence shared during this partnership.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Service Provider</div>
                        <div class='signature-line'>
                            " . ($spSignatureBase64Str ? "<img src='{$spSignatureBase64Str}' alt='SP Signature' />" : "<span style='color: #cbd5e1; font-style: italic;'>No Signature Provided</span>") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> " . ($req->sp_signed_at ? $req->sp_signed_at->format('F j, Y') : 'Unknown') . "</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Distributor (Authorized Rep)</div>
                        <div class='signature-line'>
                            " . ($distSignatureBase64Str ? "<img src='{$distSignatureBase64Str}' alt='Distributor Signature' />" : "") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$authorizedName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $fileName = 'agreements/sp_partnerships/sp_partnership_final_' . $req->service_provider_id . '_' . $distributorId . '_' . time() . '.html';
            Storage::disk('public')->put($fileName, $htmlContent);

            $req->update([
                'status' => 'active',
                'approved_at' => Carbon::now(),
                'agreement_path' => $fileName,
                'distributor_signature_path' => $signaturePath,
                'distributor_signed_at' => Carbon::now()
            ]);

            // ==========================================
            // BACKGROUND / LAZY-LOAD EMAIL DISPATCHER
            // ==========================================
            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $distEmail = $distributor ? $distributor->email : $user->email;
                $attachmentPath = storage_path('app/public/' . $fileName);

                // app()->terminating executes immediately AFTER the HTTP response is sent to Vue.
                // This makes the frontend super fast and creates a "lazy load" background effect.
                app()->terminating(function () use ($spEmail, $spName, $distName, $distEmail, $attachmentPath) {
                    try {
                        // 1. Email to Service Provider
                        if ($spEmail) {
                            $spSubject = "Partnership Request Approved - {$distName}";
                            $spHtml = "
                                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; color: #333;'>
                                    <h2 style='color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Partnership Approved</h2>
                                    <p>Dear <b>{$spName}</b>,</p>
                                    <p>Congratulations! Your commercial partnership request with <b>{$distName}</b> has been officially approved via the CaviteGoPaint network.</p>
                                    <p>We have attached the finalized, dually-signed Official Partnership Agreement to this email for your records.</p>
                                    <p>You can now log into your Service Provider portal to access their wholesale catalog and submit procurement orders.</p>
                                    <br/>
                                    <p>Best regards,<br/><b>The CaviteGoPaint System on behalf of {$distName}</b></p>
                                </div>
                            ";
                            Mail::html($spHtml, function($msg) use ($spEmail, $spSubject, $attachmentPath) {
                                $msg->to($spEmail)->subject($spSubject)->attach($attachmentPath);
                            });
                        }

                        // 2. Email to Distributor
                        if ($distEmail) {
                            $distSubject = "New Partnership Established - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; color: #333;'>
                                    <h2 style='color: #10b981; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>New Partnership Established</h2>
                                    <p>Dear <b>{$distName}</b> Team,</p>
                                    <p>You have successfully approved the commercial partnership request from <b>{$spName}</b>.</p>
                                    <p>We have attached the finalized, dually-signed Official Partnership Agreement to this email for your internal records.</p>
                                    <br/>
                                    <p>Best regards,<br/><b>CaviteGoPaint System Administrator</b></p>
                                </div>
                            ";
                            Mail::html($distHtml, function($msg) use ($distEmail, $distSubject, $attachmentPath) {
                                $msg->to($distEmail)->subject($distSubject)->attach($attachmentPath);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Mail sending failed during background processing: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Failed to register terminating mail process: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Partnership Approved and Final Agreement Document generated.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Decline partnership request and dispatch email (Background Lazy Load)
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_approve')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to reject partnership requests.'], 403);
            }

            $request->validate(['reason' => 'required|string|max:1000']);

            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)
                    ->where('distributor_id', $distributorId)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found or unauthorized.'], 404);
            }

            $req->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);

            // ==========================================
            // BACKGROUND / LAZY-LOAD EMAIL DISPATCHER
            // ==========================================
            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Service Provider';
                
                $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
                $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'The Distributor';
                
                $reason = $request->reason;

                // Push email to background
                app()->terminating(function () use ($spEmail, $spName, $distName, $reason) {
                    try {
                        if ($spEmail) {
                            $spSubject = "Partnership Request Update - {$distName}";
                            $spHtml = "
                                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; color: #333;'>
                                    <h2 style='color: #ef4444; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Partnership Proposal Update</h2>
                                    <p>Dear <b>{$spName}</b>,</p>
                                    <p>We regret to inform you that your recent commercial partnership request with <b>{$distName}</b> via CaviteGoPaint has been declined at this time.</p>
                                    
                                    <div style='background-color: #fef2f2; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0; border-radius: 4px;'>
                                        <p style='margin-top: 0; margin-bottom: 5px;'><b>Reason provided:</b></p>
                                        <p style='margin: 0; font-style: italic; color: #7f1d1d;'>\"{$reason}\"</p>
                                    </div>
                                    
                                    <p>Thank you for your interest in our distribution network.</p>
                                    <br/>
                                    <p>Best regards,<br/><b>The CaviteGoPaint System on behalf of {$distName}</b></p>
                                </div>
                            ";
                            Mail::html($spHtml, function($msg) use ($spEmail, $spSubject) {
                                $msg->to($spEmail)->subject($spSubject);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Mail sending failed during partnership rejection: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Failed to register terminating mail process: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Partnership request declined.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline request.', 'error' => $e->getMessage()], 500);
        }
    }
}