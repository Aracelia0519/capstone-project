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
                        'termination_url' => isset($req->termination_path) && $req->termination_path ? asset('storage/' . $req->termination_path) : null,
                        // Track who signed the termination to dictate UI flow
                        'sp_termination_signed_at' => $req->sp_termination_signed_at,
                        'distributor_termination_signed_at' => $req->distributor_termination_signed_at,
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
     * Approve partnership request, generate agreement, and dispatch emails
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

            $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');

            $spSignatureBase64Str = '';
            if ($req->sp_signature_path && Storage::disk('public')->exists($req->sp_signature_path)) {
                $spImageRaw = Storage::disk('public')->get($req->sp_signature_path);
                $spSignatureBase64Str = 'data:image/png;base64,' . base64_encode($spImageRaw);
            }

            $distSignatureBase64Str = $request->signature ?? '';

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
                            <p><strong>Date:</strong> " . ($req->sp_signed_at ? \Carbon\Carbon::parse($req->sp_signed_at)->format('F j, Y') : 'Unknown') . "</p>
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
                'distributor_signed_at' => Carbon::now(),
                
                // CRITICAL FIX: Reset old termination data upon reactivation approval
                'termination_path' => null,
                'distributor_termination_signed_at' => null,
                'distributor_termination_signature_path' => null,
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
            ]);

            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $distEmail = $distributor ? $distributor->email : $user->email;
                $attachmentPath = storage_path('app/public/' . $fileName);

                app()->terminating(function () use ($spEmail, $spName, $distName, $distEmail, $attachmentPath) {
                    try {
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
                        Log::error('Mail sending failed: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail fail: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Partnership Approved and Final Agreement Document generated.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Decline partnership request and dispatch email
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
                'rejection_reason' => $request->reason,
                
                // Clear termination fields in case they requested reactivation and it gets rejected.
                'termination_path' => null,
                'distributor_termination_signed_at' => null,
                'distributor_termination_signature_path' => null,
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
            ]);

            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Service Provider';
                
                $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
                $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'The Distributor';
                
                $reason = $request->reason;

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
                        Log::error('Mail sending failed: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail fail: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Partnership request declined.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Raw HTML for Termination Agreement
     */
    public function getTerminationRaw($id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_manage')) {
                return response()->json(['message' => 'Access Denied.'], 403);
            }

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
                return response()->json(['success' => false, 'message' => 'Request not found.'], 404);
            }

            $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');

            $html = "
                <p>This Notice of Termination officially requests the revocation of the Commercial Partnership Agreement between <strong>{$distName}</strong> (Distributor) and <strong>{$spName}</strong> (Service Provider), requested on <strong>{$dateStr}</strong>.</p>
                <p class='mt-3'><strong>Terms of Termination:</strong></p>
                <ul class='list-disc pl-5 mt-2 space-y-1 text-slate-700'>
                    <li>Upon approval by the Service Provider, access to the Distributor's wholesale catalog will be revoked.</li>
                    <li>Any pending transactions or fulfillment obligations initiated prior to this date shall be honored.</li>
                    <li>Both parties agree to continue upholding the confidentiality of any proprietary business intelligence shared during the active partnership period.</li>
                </ul>
            ";

            return response()->json(['success' => true, 'html' => $html], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate termination terms.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate Partnership Termination (Sets to Pending Termination)
     */
    public function terminate(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_manage')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to terminate partnerships.'], 403);
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

            if (!$req || $req->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'Active partnership not found or unauthorized.'], 404);
            }

            // Save Termination Signature
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                if (count($imageParts) == 2) {
                    $imageTypeAux = explode("image/", $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageBase64 = base64_decode($imageParts[1]);
                    
                    $fileName = 'distributor_' . $distributorId . '_sp_' . $req->service_provider_id . '_' . time() . '.' . $imageType;
                    $signaturePath = 'agreements/terminations/signatures/' . $fileName;
                    Storage::disk('public')->put($signaturePath, $imageBase64);
                }
            }

            $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');

            $distSignatureBase64Str = $request->signature ?? '';

            // Generate Termination HTML Document
            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Notice of Partnership Termination Request</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #f97316; padding-bottom: 20px; }
                    h1 { color: #c2410c; margin-bottom: 5px; }
                    .date-ref { text-align: right; margin-bottom: 30px; font-weight: bold; color: #475569; }
                    .parties { background: #fff7ed; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #fed7aa; }
                    .terms { margin-bottom: 40px; }
                    .terms h3 { color: #9a3412; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
                    .signature-box { width: 45%; }
                    .signature-line { border-bottom: 1px solid #94a3b8; margin-bottom: 10px; height: 120px; display: flex; align-items: flex-end; justify-content: center; position: relative; }
                    .signature-line img { max-height: 100px; max-width: 100%; margin-bottom: 5px; }
                    .sig-label { font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #475569; }
                    .sig-details { font-size: 14px; color: #475569; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>Notice of Partnership Termination Request</h1>
                    <p>Official Revocation Request Awaiting Approval</p>
                </div>

                <div class='date-ref'>Request Date: {$dateStr}</div>
                
                <div class='parties'>
                    <p>This Notice formally requests the termination of the Commercial Partnership Agreement between:</p>
                    <p><strong>Distributor (Initiating Party):</strong> {$distName}</p>
                    <p><strong>Service Provider:</strong> {$spName}</p>
                </div>
                
                <div class='terms'>
                    <h3>Terms of Revocation</h3>
                    <p><strong>1. Access Revocation:</strong> Upon final approval by the Service Provider, access to the Distributor's wholesale catalog and partner-tier pricing will be revoked.</p>
                    <p><strong>2. Transaction Fulfillment:</strong> Any pending transactions initiated prior to this date shall be processed normally, subject to standard review.</p>
                    <p><strong>3. Mutual Confidentiality:</strong> Both parties are reminded of their obligation to protect proprietary business data obtained during the partnership period.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Requested By (Distributor)</div>
                        <div class='signature-line'>
                            " . ($distSignatureBase64Str ? "<img src='{$distSignatureBase64Str}' alt='Distributor Signature' />" : "") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$authorizedName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Awaiting Approval (Service Provider)</div>
                        <div class='signature-line'>
                            <span style='color: #cbd5e1; font-style: italic;'>Pending Signature</span>
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> Pending</p>
                            <p><strong>Date:</strong> Pending</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $htmlPath = 'agreements/terminations/documents/termination_sp_' . $req->service_provider_id . '_' . $distributorId . '_' . time() . '.html';
            Storage::disk('public')->put($htmlPath, $htmlContent);

            DB::table('service_provider_distributors')->where('id', $req->id)->update([
                'status' => 'pending_termination',
                'updated_at' => Carbon::now(),
                'termination_path' => $htmlPath,
                'distributor_termination_signed_at' => Carbon::now(),
                'distributor_termination_signature_path' => $signaturePath,
                
                // Clear any lingering SP signatures here just to prevent conflict blocks
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
            ]);

            // Background Emails
            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $attachmentPath = storage_path('app/public/' . $htmlPath);

                app()->terminating(function () use ($spEmail, $spName, $distName, $attachmentPath) {
                    try {
                        if ($spEmail) {
                            $spSubject = "Action Required: Partnership Termination Requested - {$distName}";
                            $spHtml = "
                                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; color: #333;'>
                                    <h2 style='color: #f97316; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Partnership Termination Requested</h2>
                                    <p>Dear <b>{$spName}</b>,</p>
                                    <p>This email is to formally notify you that <b>{$distName}</b> has initiated a request to terminate your commercial partnership.</p>
                                    <p>An official Termination Document has been attached for your review.</p>
                                    <div style='background-color: #fff7ed; padding: 15px; border-left: 4px solid #f97316; margin: 20px 0;'>
                                        <p style='margin: 0;'><b>Action Required:</b> Please log in to your Service Provider portal to review and officially approve this termination request to formally close the partnership.</p>
                                    </div>
                                    <br/>
                                    <p>Regards,<br/><b>The CaviteGoPaint System</b></p>
                                </div>
                            ";
                            Mail::html($spHtml, function($msg) use ($spEmail, $spSubject, $attachmentPath) {
                                $msg->to($spEmail)->subject($spSubject)->attach($attachmentPath);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Mail sending failed: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail fail: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Termination Request Sent to Service Provider.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to initiate termination.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Distributor Approves SP-Initiated Termination Request
     */
    public function approveTermination(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_manage')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to approve terminations.'], 403);
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

            // Refactored to use empty() to catch any instances of empty strings causing false negatives.
            if (!$req || $req->status !== 'pending_termination' || empty($req->sp_termination_signed_at)) {
                return response()->json(['success' => false, 'message' => 'Valid pending SP termination request not found.'], 404);
            }

            // Save Distributor's Termination Signature
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                
                $fileName = 'term_dist_' . $distributorId . '_sp_' . $req->service_provider_id . '_' . time() . '.' . $imageType;
                $signaturePath = 'agreements/terminations/signatures/' . $fileName;
                Storage::disk('public')->put($signaturePath, $imageBase64);
            }

            $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');

            // Get base64 for SP Signature
            $spSignatureStr = '';
            if (!empty($req->sp_termination_signature_path) && Storage::disk('public')->exists($req->sp_termination_signature_path)) {
                $spSignatureStr = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($req->sp_termination_signature_path));
            }

            $distSignatureStr = $request->signature;

            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Notice of Partnership Termination (Final)</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #ef4444; padding-bottom: 20px; }
                    h1 { color: #b91c1c; margin-bottom: 5px; }
                    .date-ref { text-align: right; margin-bottom: 30px; font-weight: bold; color: #475569; }
                    .parties { background: #fef2f2; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #fca5a5; }
                    .terms { margin-bottom: 40px; }
                    .terms h3 { color: #7f1d1d; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
                    .signature-box { width: 45%; }
                    .signature-line { border-bottom: 1px solid #94a3b8; margin-bottom: 10px; height: 120px; display: flex; align-items: flex-end; justify-content: center; position: relative; }
                    .signature-line img { max-height: 100px; max-width: 100%; margin-bottom: 5px; }
                    .sig-label { font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #475569; }
                    .sig-details { font-size: 14px; color: #475569; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>Notice of Partnership Termination</h1>
                    <p>Official Revocation of Access - Fully Executed</p>
                </div>

                <div class='date-ref'>Finalized Date: {$dateStr}</div>
                
                <div class='parties'>
                    <p>This Notice formally dissolves the Commercial Partnership Agreement between:</p>
                    <p><strong>Distributor (Approving Party):</strong> {$distName}</p>
                    <p><strong>Service Provider (Initiating Party):</strong> {$spName}</p>
                </div>
                
                <div class='terms'>
                    <h3>Terms of Revocation</h3>
                    <p><strong>1. Access Revocation:</strong> The Service Provider's access to the Distributor's wholesale catalog and partner-tier pricing is officially revoked.</p>
                    <p><strong>2. Transaction Fulfillment:</strong> Any pending transactions initiated prior to this date shall be processed normally, subject to standard review.</p>
                    <p><strong>3. Mutual Confidentiality:</strong> Both parties are reminded of their obligation to protect proprietary business data obtained during the partnership period.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Requested By (Service Provider)</div>
                        <div class='signature-line'>
                            " . ($spSignatureStr ? "<img src='{$spSignatureStr}' alt='SP Signature' />" : "<span style='color: #cbd5e1; font-style: italic;'>No Signature Uploaded</span>") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> " . Carbon::parse($req->sp_termination_signed_at)->format('F j, Y') . "</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Approved By (Distributor)</div>
                        <div class='signature-line'>
                            <img src='{$distSignatureStr}' alt='Distributor Signature' />
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

            $htmlPath = 'agreements/terminations/documents/termination_final_dist_' . $distributorId . '_sp_' . $req->service_provider_id . '_' . time() . '.html';
            Storage::disk('public')->put($htmlPath, $htmlContent);

            DB::table('service_provider_distributors')->where('id', $req->id)->update([
                'status' => 'disconnected',
                'updated_at' => Carbon::now(),
                'termination_path' => $htmlPath,
                'distributor_termination_signed_at' => Carbon::now(),
                'distributor_termination_signature_path' => $signaturePath
            ]);

            // Dispatch Emails
            try {
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $distEmail = $distributor ? $distributor->email : $user->email;
                $attachmentPath = storage_path('app/public/' . $htmlPath);

                app()->terminating(function () use ($spEmail, $distEmail, $spName, $distName, $attachmentPath) {
                    try {
                        if ($distEmail) {
                            $distSubject = "Partnership Terminated Successfully - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #ef4444;'>Termination Approved</h2>
                                    <p>You have formally approved the termination request from <b>{$spName}</b>. The partnership is now disconnected.</p>
                                    <p>A copy of the fully signed termination document is attached.</p>
                                </div>";
                            Mail::html($distHtml, function($msg) use ($distEmail, $distSubject, $attachmentPath) {
                                $msg->to($distEmail)->subject($distSubject)->attach($attachmentPath);
                            });
                        }
                        if ($spEmail) {
                            $spSubject = "Partnership Formally Terminated - {$distName}";
                            $spHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #ef4444;'>Partnership Terminated</h2>
                                    <p><b>{$distName}</b> has officially approved your termination request. Your wholesale access has been revoked.</p>
                                    <p>A copy of the finalized document is attached for your records.</p>
                                </div>";
                            Mail::html($spHtml, function($msg) use ($spEmail, $spSubject, $attachmentPath) {
                                $msg->to($spEmail)->subject($spSubject)->attach($attachmentPath);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Termination Mail Error: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail hook failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Termination successfully approved.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve termination.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Distributor Declines SP-Initiated Termination Request
     */
    public function declineTermination(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_manage')) {
                return response()->json(['message' => 'Access Denied.'], 403);
            }

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

            if (!$req || $req->status !== 'pending_termination') {
                return response()->json(['success' => false, 'message' => 'Valid pending termination not found.'], 404);
            }

            DB::table('service_provider_distributors')->where('id', $req->id)->update([
                'status' => 'active',
                'updated_at' => Carbon::now(),
                'termination_path' => null,
                'distributor_termination_signed_at' => null,
                'distributor_termination_signature_path' => null,
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
            ]);

            // Dispatch Emails
            try {
                $distributor = \App\Models\User::with('distributorRequirement')->find($distributorId);
                $distEmail = $distributor ? $distributor->email : $user->email;
                $spEmail = $req->serviceProvider ? $req->serviceProvider->email : null;
                $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Service Provider';
                $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';

                app()->terminating(function () use ($spEmail, $distEmail, $spName, $distName) {
                    try {
                        if ($spEmail) {
                            $spSubject = "Notice: Termination Request Declined - {$distName}";
                            $spHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #f59e0b;'>Termination Declined</h2>
                                    <p>Please be advised that <b>{$distName}</b> has <b>declined</b> your request to terminate the partnership.</p>
                                    <p>The status of the commercial relationship remains <b>Active</b>.</p>
                                </div>";
                            Mail::html($spHtml, function($msg) use ($spEmail, $spSubject) {
                                $msg->to($spEmail)->subject($spSubject);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Termination Decline Mail Error: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail hook failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Termination successfully declined. Partnership remains active.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline termination.', 'error' => $e->getMessage()], 500);
        }
    }
}