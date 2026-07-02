<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use App\Events\Partnership\PartnershipStatusUpdated;
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
    private function getPermissions($user, $permissionKey)
    {
        $defaults = ['can_view' => false, 'can_manage' => false, 'can_approve' => false];
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return ['can_view' => true, 'can_manage' => true, 'can_approve' => true];
        }
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')->where('title', $employee->position)->where('distributor_id', $employee->parent_distributor_id)->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')->where('position_id', $position->id)->where('permission_key', $permissionKey)->first();
            if ($access) return ['can_view' => (bool) $access->can_view, 'can_manage' => (bool) $access->can_manage, 'can_approve' => (bool) $access->can_approve];
        }
        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action) {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    private function getDistributorId($user) {
        if ($user->role === 'operational_distributor') return DB::table('operational_distributors')->where('user_id', $user->id)->value('parent_distributor_id');
        if ($user->role === 'employee') return DB::table('hr_employees')->where('user_id', $user->id)->value('parent_distributor_id');
        return $user->id;
    }

    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $permissions = $this->getPermissions($user, 'ec_service_provider');
            if (!$permissions['can_view']) return response()->json(['message' => 'Access Denied.'], 403);

            $distId = $this->getDistributorId($user);
            if (!$distId) return response()->json(['success' => false, 'message' => 'Distributor context not found.'], 403);

            $requests = ServiceProviderDistributor::with(['serviceProvider.serviceProviderRequirement'])
                ->where('distributor_id', $distId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $sp = $req->serviceProvider;
                    $spReq = $sp ? $sp->serviceProviderRequirement : null;
                    
                    $addressStr = 'Address not provided';
                    $location = 'Location not provided';
                    if ($spReq) {
                        $addr = DB::table('service_provider_addresses')->where('service_provider_requirements_id', $spReq->id)->first();
                        if ($addr) { $location = "{$addr->city}, {$addr->province}"; $addressStr = "{$addr->block_address}, {$addr->barangay}, {$addr->city}, {$addr->province}"; }
                    }

                    $status = $req->status;
                    if ($status === 'active' && $req->contract_end_date && Carbon::parse($req->contract_end_date)->isPast()) {
                        $status = 'expired';
                    }

                    return [
                        'id' => $req->id,
                        'provider_name' => $sp ? $sp->full_name : 'Unknown',
                        'email' => $sp ? $sp->email : 'Unknown',
                        'phone' => $sp ? $sp->phone : 'Unknown',
                        'location' => $location,
                        'full_address' => $addressStr,
                        'message' => $req->request_message,
                        'status' => $status,
                        'proposed_end_date' => $req->proposed_end_date,
                        'contract_end_date' => $req->contract_end_date,
                        'last_proposed_by' => $req->last_proposed_by ?? 'service_provider',
                        'agreement_url' => $req->agreement_path ? asset('storage/' . $req->agreement_path) : null,
                    ];
                });

            return response()->json(['success' => true, 'data' => $requests, 'permissions' => $permissions, 'current_user_id' => $distId], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requests', 'error' => $e->getMessage()], 500);
        }
    }

    private function saveSignature($signatureBase64, $userId, $providerId) {
        $imageParts = explode(";base64,", $signatureBase64);
        $imageType = explode("image/", $imageParts[0])[1] ?? 'png';
        $fileName = 'dist_' . $userId . '_sp_' . $providerId . '_' . time() . '.' . $imageType;
        $path = 'agreements/signatures/' . $fileName;
        Storage::disk('public')->put($path, base64_decode($imageParts[1]));
        return $path;
    }

    /**
     * OP Counters SP's proposed date
     */
    public function counter(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_approve')) return response()->json(['message' => 'Access Denied.'], 403);

            $request->validate([
                'signature' => 'required|string',
                'proposed_end_date' => 'required|date|after_or_equal:' . now()->addDays(28)->format('Y-m-d')
            ]);
            
            $distId = $this->getDistributorId($user);
            $req = ServiceProviderDistributor::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            $signaturePath = $this->saveSignature($request->signature, $distId, $req->service_provider_id);

            $req->update([
                'status' => 'negotiating',
                'proposed_end_date' => $request->proposed_end_date,
                'last_proposed_by' => 'distributor',
                'distributor_signature_path' => $signaturePath,
                'distributor_signed_at' => now(),
            ]);

            event(new PartnershipStatusUpdated($distId, $req->service_provider_id, 'contract_countered'));
            return response()->json(['success' => true, 'message' => 'Counter-proposal sent successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send counter proposal.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * OP Approves SP's proposed date (Finalize Contract)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_approve')) return response()->json(['message' => 'Access Denied.'], 403);

            $request->validate(['signature' => 'required|string']);
            
            $distId = $this->getDistributorId($user);
            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            $signaturePath = $this->saveSignature($request->signature, $distId, $req->service_provider_id);

            $distributor = \App\Models\User::with('distributorRequirement')->find($distId);
            $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'Authorized Distributor';
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';
            $dateStr = Carbon::now()->format('F j, Y');
            $endDateStr = Carbon::parse($req->proposed_end_date)->format('F j, Y');

            $spSigStr = $req->sp_signature_path && Storage::disk('public')->exists($req->sp_signature_path) ? 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($req->sp_signature_path)) : '';
            $distSigStr = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($signaturePath));

            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head><style> body { font-family: sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 40px; } .sig-line { border-bottom: 1px solid #94a3b8; margin-bottom: 10px; height: 120px; display: flex; align-items: flex-end; justify-content: center; } .sig-line img { max-height: 100px; } </style></head>
            <body>
                <h1 style='text-align:center; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;'>Official Partnership Agreement</h1>
                <p style='text-align:right'><strong>Effective Date:</strong> {$dateStr} <br/> <strong>Expiration Date:</strong> {$endDateStr}</p>
                <div style='background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 30px;'>
                    <p>This Partnership Agreement is formally finalized between:</p>
                    <p><strong>Distributor:</strong> {$distName}</p>
                    <p><strong>Service Provider:</strong> {$spName}</p>
                </div>
                <h3>Terms and Conditions</h3>
                <p><strong>1. Authorization of Access:</strong> Distributor authorizes Service Provider to access wholesale catalog and partner-tier pricing.</p>
                <p><strong>2. Procurement and Pricing:</strong> Procurement subject to Distributor's quality assurance. Pricing is exclusive.</p>
                <div style='display: flex; justify-content: space-between; margin-top: 50px;'>
                    <div style='width: 45%;'><p><strong>Service Provider</strong></p><div class='sig-line'><img src='{$spSigStr}'/></div><p>Signed by: {$spName}</p><p>Date: " . ($req->sp_signed_at ? Carbon::parse($req->sp_signed_at)->format('F j, Y') : 'Unknown') . "</p></div>
                    <div style='width: 45%;'><p><strong>Distributor</strong></p><div class='sig-line'><img src='{$distSigStr}'/></div><p>Signed by: {$authorizedName}</p><p>Date: {$dateStr}</p></div>
                </div>
            </body>
            </html>
            ";

            $fileName = 'agreements/sp_partnerships/final_' . $req->service_provider_id . '_' . $distId . '_' . time() . '.html';
            Storage::disk('public')->put($fileName, $htmlContent);

            $req->update([
                'status' => 'active',
                'contract_end_date' => $req->proposed_end_date,
                'approved_at' => now(),
                'agreement_path' => $fileName,
                'distributor_signature_path' => $signaturePath,
                'distributor_signed_at' => now(),
            ]);

            event(new PartnershipStatusUpdated($distId, $req->service_provider_id, 'contract_accepted'));

            try {
                $spEmail = $req->serviceProvider->email ?? null;
                $distEmail = $distributor->email ?? $user->email;
                $attachmentPath = storage_path('app/public/' . $fileName);

                app()->terminating(function () use ($spEmail, $spName, $distName, $distEmail, $attachmentPath) {
                    try {
                        if ($spEmail) {
                            $spHtml = "<h2>Partnership Approved</h2><p>Your contract with {$distName} has been officially approved.</p><p>The finalized, signed Official Partnership Agreement is attached.</p>";
                            Mail::html($spHtml, function($msg) use ($spEmail, $distName, $attachmentPath) { $msg->to($spEmail)->subject("Partnership Request Approved - {$distName}")->attach($attachmentPath); });
                        }
                        if ($distEmail) {
                            $distHtml = "<h2>New Partnership Established</h2><p>You have approved the contract with {$spName}.</p><p>The finalized agreement is attached for your records.</p>";
                            Mail::html($distHtml, function($msg) use ($distEmail, $spName, $attachmentPath) { $msg->to($distEmail)->subject("New Partnership Established - {$spName}")->attach($attachmentPath); });
                        }
                    } catch (\Exception $m) {}
                });
            } catch (\Exception $e) {}

            return response()->json(['success' => true, 'message' => 'Contract Finalized.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve request.', 'error' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_approve')) return response()->json(['message' => 'Access Denied.'], 403);
            $request->validate(['reason' => 'required|string|max:1000']);

            $distId = $this->getDistributorId($user);
            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)->where('distributor_id', $distId)->firstOrFail();

            $req->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
            event(new PartnershipStatusUpdated($distId, $req->service_provider_id, 'partnership_rejected'));

            try {
                $spEmail = $req->serviceProvider->email ?? null;
                $spName = $req->serviceProvider->full_name ?? 'Service Provider';
                $distributor = \App\Models\User::with('distributorRequirement')->find($distId);
                $distName = $distributor ? ($distributor->distributorRequirement->company_name ?? $distributor->full_name) : 'The Distributor';
                $reason = $request->reason;

                app()->terminating(function () use ($spEmail, $spName, $distName, $reason) {
                    try {
                        if ($spEmail) {
                            $spHtml = "<h2>Partnership Update</h2><p>Your recent commercial proposal with {$distName} has been declined.</p><p><b>Reason:</b> \"{$reason}\"</p>";
                            Mail::html($spHtml, function($msg) use ($spEmail, $distName) { $msg->to($spEmail)->subject("Partnership Update - {$distName}"); });
                        }
                    } catch (\Exception $m) {}
                });
            } catch (\Exception $e) {}

            return response()->json(['success' => true, 'message' => 'Partnership request declined.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline request.', 'error' => $e->getMessage()], 500);
        }
    }
}