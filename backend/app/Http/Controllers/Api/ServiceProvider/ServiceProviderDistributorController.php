<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceProviderDistributorController extends Controller
{
    /**
     * Fetch all verified distributors and their partnership status with the logged-in service provider.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $distributors = User::where('role', 'distributor')
                ->whereHas('distributorRequirement', function ($query) {
                    $query->where('status', 'approved');
                })
                ->with(['distributorRequirement.address'])
                ->get()
                ->map(function ($distributor) use ($user) {
                    
                    $partnership = ServiceProviderDistributor::where('service_provider_id', $user->id)
                        ->where('distributor_id', $distributor->id)
                        ->first();

                    $specialties = DB::table('distributor_products')
                        ->where('distributor_id', $distributor->id)
                        ->where('is_active', 1) 
                        ->select('category')
                        ->distinct()
                        ->pluck('category')
                        ->toArray();

                    $address = $distributor->distributorRequirement->address;
                    $locationString = $address ? "{$address->city}, {$address->province}" : 'Location Unverified';

                    return [
                        'id' => $distributor->id,
                        'partnership_id' => $partnership ? $partnership->id : null,
                        'name' => $distributor->distributorRequirement->company_name ?? 'Unknown Distributor',
                        'location' => $locationString,
                        'specialties' => $specialties,
                        'status' => $partnership ? $partnership->status : null,
                        'agreement_url' => ($partnership && $partnership->agreement_path) ? asset('storage/' . $partnership->agreement_path) : null,
                        'termination_url' => ($partnership && isset($partnership->termination_path) && $partnership->termination_path) ? asset('storage/' . $partnership->termination_path) : null,
                        // Add these so frontend can tell who initiated the termination
                        'sp_termination_signed_at' => $partnership ? $partnership->sp_termination_signed_at : null,
                        'distributor_termination_signed_at' => $partnership ? $partnership->distributor_termination_signed_at : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $distributors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch distributors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Partnership Request (SP -> Distributor)
     */
    public function requestPartnership(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'distributor_id' => 'required|exists:users,id',
                'request_message' => 'nullable|string|max:1000',
                'signature' => 'required|string',
            ]);

            $user = Auth::user();
            $distributor = User::with('distributorRequirement')->find($request->distributor_id);

            $existing = ServiceProviderDistributor::where('service_provider_id', $user->id)
                ->where('distributor_id', $request->distributor_id)
                ->whereIn('status', ['pending', 'active'])
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'A pending or active partnership already exists.'], 400);
            }

            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1];
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'sp_' . $user->id . '_' . time() . '.' . $imageType;
                
                $signaturePath = 'agreements/signatures/' . $fileName;
                Storage::disk('public')->put($signaturePath, $imageBase64);
            }

            $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';
            $dateStr = now()->format('F j, Y');
            $signatureUrl = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($signaturePath));

            $agreementHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Partnership Agreement Proposal</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; }
                    h1 { color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 5px; }
                    .date-ref { text-align: right; margin-bottom: 30px; font-weight: bold; color: #475569; }
                    .parties { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
                    .terms { margin-bottom: 40px; }
                    .terms h3 { color: #334155; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 50px; gap: 40px;}
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
                    <p>Commercial Supply Relationship (Proposal)</p>
                </div>

                <div class='date-ref'>Request Date: {$dateStr}</div>
                
                <div class='parties'>
                    <p>This Partnership Agreement (the \"Agreement\") is formally proposed between:</p>
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
                            <img src='{$signatureUrl}' alt='SP Signature' />
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Distributor (Authorized Rep)</div>
                        <div class='signature-line'>
                            <span style='color: #cbd5e1; font-style: italic;'>Pending Distributor Signature</span>
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$distName}</p>
                            <p><strong>Date:</strong> ____________________</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $agreementFileName = 'sp_partnership_' . $user->id . '_' . $distributor->id . '_' . time() . '.html';
            $agreementPath = 'agreements/sp_partnerships/' . $agreementFileName;
            Storage::disk('public')->put($agreementPath, $agreementHtml);

            $partnership = ServiceProviderDistributor::updateOrCreate(
                ['service_provider_id' => $user->id, 'distributor_id' => $request->distributor_id],
                [
                    'status' => 'pending',
                    'request_message' => $request->request_message,
                    'agreement_path' => $agreementPath,
                    'sp_signature_path' => $signaturePath,
                    'sp_signed_at' => now(),
                ]
            );

            return response()->json(['success' => true, 'message' => 'Partnership request sent successfully!'], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Requests Reactivation of a Terminated Partnership
     */
    public function requestReactivation(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'signature' => 'required|string',
                'message' => 'nullable|string|max:1000'
            ]);

            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->first();

            if (!$partnership || $partnership->status !== 'disconnected') {
                return response()->json(['success' => false, 'message' => 'Valid disconnected partnership not found.'], 404);
            }

            // Save New Reactivation Signature
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1];
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'sp_reactivation_' . $user->id . '_' . time() . '.' . $imageType;
                
                $signaturePath = 'agreements/signatures/' . $fileName;
                Storage::disk('public')->put($signaturePath, $imageBase64);
            }

            $distributor = User::with('distributorRequirement')->find($partnership->distributor_id);
            $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';
            $dateStr = now()->format('F j, Y');
            $signatureUrl = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($signaturePath));

            // Generate fresh Agreement Proposal HTML
            $agreementHtml = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Partnership Agreement Reactivation Proposal</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    .header { text-align: center; margin-bottom: 40px; }
                    h1 { color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 5px; }
                    .date-ref { text-align: right; margin-bottom: 30px; font-weight: bold; color: #475569; }
                    .parties { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
                    .terms { margin-bottom: 40px; }
                    .terms h3 { color: #334155; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 50px; gap: 40px;}
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
                    <p>Commercial Supply Relationship (Reactivation Proposal)</p>
                </div>

                <div class='date-ref'>Request Date: {$dateStr}</div>
                
                <div class='parties'>
                    <p>This Partnership Agreement (the \"Agreement\") is formally proposed to restore the relationship between:</p>
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
                            <img src='{$signatureUrl}' alt='SP Signature' />
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Distributor (Authorized Rep)</div>
                        <div class='signature-line'>
                            <span style='color: #cbd5e1; font-style: italic;'>Pending Distributor Signature</span>
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$distName}</p>
                            <p><strong>Date:</strong> ____________________</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $agreementFileName = 'sp_partnership_reactivation_' . $user->id . '_' . $distributor->id . '_' . time() . '.html';
            $agreementPath = 'agreements/sp_partnerships/' . $agreementFileName;
            Storage::disk('public')->put($agreementPath, $agreementHtml);

            $message = $request->message ?? 'We would like to formally request the reactivation of our commercial partnership.';

            $partnership->update([
                'status' => 'pending',
                'request_message' => $message,
                'agreement_path' => $agreementPath,
                'sp_signature_path' => $signaturePath,
                'sp_signed_at' => now(),
                'distributor_signature_path' => null,
                'distributor_signed_at' => null,
                'termination_path' => null,
                'distributor_termination_signed_at' => null,
                'distributor_termination_signature_path' => null,
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
                'updated_at' => Carbon::now()
            ]);

            // Dispatch Emails
            try {
                $distEmail = $distributor->email;

                app()->terminating(function () use ($distEmail, $spName, $message) {
                    try {
                        if ($distEmail) {
                            $distSubject = "Reactivation Request - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #10b981;'>Partnership Reactivation Requested</h2>
                                    <p><b>{$spName}</b> has requested to reactivate their previously terminated commercial partnership with you and has signed a new agreement proposal.</p>
                                    
                                    <div style='background-color: #f3f4f6; padding: 15px; border-left: 4px solid #10b981; margin: 20px 0;'>
                                        <p style='margin: 0;'><b>Message provided:</b><br/> <i>\"{$message}\"</i></p>
                                    </div>
                                    
                                    <p>Please log in to your distributor portal and review this request under your pending service provider proposals to formally approve and re-sign.</p>
                                </div>";
                            Mail::html($distHtml, function($msg) use ($distEmail, $distSubject) {
                                $msg->to($distEmail)->subject($distSubject);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Reactivation Mail Error: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail hook failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Reactivation requested successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to request reactivation.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Initiates the Termination Request (Sets to pending_termination)
     */
    public function requestTermination(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $request->validate([
                'signature' => 'required|string',
                'message' => 'nullable|string|max:1000'
            ]);

            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->first();

            if (!$partnership || $partnership->status !== 'active') {
                return response()->json(['success' => false, 'message' => 'Active partnership not found.'], 404);
            }

            // Save SP's Termination Signature
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1];
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'term_request_sp_' . $user->id . '_' . time() . '.' . $imageType;
                
                $signaturePath = 'agreements/terminations/signatures/' . $fileName;
                Storage::disk('public')->put($signaturePath, $imageBase64);
            }

            // Generate the Termination Request HTML Document
            $distributor = User::with('distributorRequirement')->find($partnership->distributor_id);
            $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';
            $dateStr = now()->format('F j, Y');
            $signatureUrl = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($signaturePath));

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
                    <p><strong>Distributor:</strong> {$distName}</p>
                    <p><strong>Service Provider (Initiating Party):</strong> {$spName}</p>
                </div>
                
                <div class='terms'>
                    <h3>Terms of Revocation</h3>
                    <p><strong>1. Access Revocation:</strong> Upon final approval by the Distributor, access to the Distributor's wholesale catalog and partner-tier pricing will be revoked.</p>
                    <p><strong>2. Transaction Fulfillment:</strong> Any pending transactions initiated prior to this date shall be processed normally, subject to standard review.</p>
                    <p><strong>3. Mutual Confidentiality:</strong> Both parties are reminded of their obligation to protect proprietary business data obtained during the partnership period.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Requested By (Service Provider)</div>
                        <div class='signature-line'>
                            <img src='{$signatureUrl}' alt='SP Signature' />
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Awaiting Approval (Distributor)</div>
                        <div class='signature-line'>
                            <span style='color: #cbd5e1; font-style: italic;'>Pending Signature</span>
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$distName}</p>
                            <p><strong>Date:</strong> Pending</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $htmlPath = 'agreements/terminations/documents/termination_request_sp_' . $partnership->service_provider_id . '_' . $distributor->id . '_' . time() . '.html';
            Storage::disk('public')->put($htmlPath, $htmlContent);

            DB::table('service_provider_distributors')->where('id', $partnership->id)->update([
                'status' => 'pending_termination',
                'updated_at' => Carbon::now(),
                'termination_path' => $htmlPath,
                'sp_termination_signed_at' => Carbon::now(),
                'sp_termination_signature_path' => $signaturePath
            ]);

            // Dispatch Emails
            try {
                $distEmail = $distributor->email;

                app()->terminating(function () use ($distEmail, $spName, $distName) {
                    try {
                        if ($distEmail) {
                            $distSubject = "Action Required: Partnership Termination Requested - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 0 auto; color: #333;'>
                                    <h2 style='color: #f97316; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;'>Partnership Termination Requested</h2>
                                    <p>Dear <b>{$distName}</b> Team,</p>
                                    <p>This email is to formally notify you that <b>{$spName}</b> has initiated a request to terminate their commercial partnership with you.</p>
                                    <div style='background-color: #fff7ed; padding: 15px; border-left: 4px solid #f97316; margin: 20px 0;'>
                                        <p style='margin: 0;'><b>Action Required:</b> Please log in to your Distributor portal to review and officially approve this termination request to formally close the partnership.</p>
                                    </div>
                                </div>
                            ";
                            Mail::html($distHtml, function($msg) use ($distEmail, $distSubject) {
                                $msg->to($distEmail)->subject($distSubject);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Termination Mail Error: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail hook failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true, 
                'message' => 'Termination successfully requested.',
                'signed_at' => Carbon::now()
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to request termination.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Approves the Termination Request (Finalizes Revocation)
     */
    public function approveTermination(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'signature' => 'required|string',
            ]);

            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->first();

            // Explicit Check: Did the Distributor ACTUALLY sign it? If not, it shouldn't be approvable here.
            if (!$partnership || $partnership->status !== 'pending_termination' || empty($partnership->distributor_termination_signed_at)) {
                return response()->json(['success' => false, 'message' => 'Valid pending distributor-initiated termination not found.'], 404);
            }

            // Save SP's Termination Signature
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = $imageTypeAux[1];
                $imageBase64 = base64_decode($imageParts[1]);
                $fileName = 'term_sp_' . $user->id . '_' . time() . '.' . $imageType;
                
                $signaturePath = 'agreements/terminations/signatures/' . $fileName;
                Storage::disk('public')->put($signaturePath, $imageBase64);
            }

            // Re-build final termination HTML document with both signatures
            $distributor = User::with('distributorRequirement')->find($partnership->distributor_id);
            $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';
            $dateStr = now()->format('F j, Y');

            // Get base64 for Distributor Signature safely
            $distSignatureStr = '';
            if (!empty($partnership->distributor_termination_signature_path) && Storage::disk('public')->exists($partnership->distributor_termination_signature_path)) {
                $distSignatureStr = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($partnership->distributor_termination_signature_path));
            }

            $spSignatureStr = $request->signature;

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
                    <p><strong>Distributor (Initiating Party):</strong> {$distName}</p>
                    <p><strong>Service Provider (Approving Party):</strong> {$spName}</p>
                </div>
                
                <div class='terms'>
                    <h3>Terms of Revocation</h3>
                    <p><strong>1. Access Revocation:</strong> The Service Provider's access to the Distributor's wholesale catalog and partner-tier pricing is officially revoked.</p>
                    <p><strong>2. Transaction Fulfillment:</strong> Any pending transactions initiated prior to this date shall be processed normally, subject to standard review.</p>
                    <p><strong>3. Mutual Confidentiality:</strong> Both parties are reminded of their obligation to protect proprietary business data obtained during the partnership period.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Requested By (Distributor)</div>
                        <div class='signature-line'>
                            " . ($distSignatureStr ? "<img src='{$distSignatureStr}' alt='Distributor Signature' />" : "") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> Authorized Rep.</p>
                            <p><strong>Date:</strong> " . Carbon::parse($partnership->distributor_termination_signed_at)->format('F j, Y') . "</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Approved By (Service Provider)</div>
                        <div class='signature-line'>
                            <img src='{$spSignatureStr}' alt='SP Signature' />
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $htmlPath = 'agreements/terminations/documents/termination_final_sp_' . $partnership->service_provider_id . '_' . $distributor->id . '_' . time() . '.html';
            Storage::disk('public')->put($htmlPath, $htmlContent);

            DB::table('service_provider_distributors')->where('id', $partnership->id)->update([
                'status' => 'disconnected',
                'updated_at' => Carbon::now(),
                'termination_path' => $htmlPath,
                'sp_termination_signed_at' => Carbon::now(),
                'sp_termination_signature_path' => $signaturePath
            ]);

            // Dispatch Emails
            try {
                $spEmail = $user->email;
                $distEmail = $distributor->email;
                $attachmentPath = storage_path('app/public/' . $htmlPath);

                app()->terminating(function () use ($spEmail, $distEmail, $spName, $distName, $attachmentPath) {
                    try {
                        if ($distEmail) {
                            $distSubject = "Partnership Terminated Successfully - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #ef4444;'>Termination Approved</h2>
                                    <p><b>{$spName}</b> has formally approved your termination request. The partnership is now disconnected.</p>
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
                                    <p>You have approved the termination request with <b>{$distName}</b>. Your wholesale access has been revoked.</p>
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

            return response()->json([
                'success' => true, 
                'message' => 'Termination successfully approved.',
                'termination_url' => asset('storage/' . $htmlPath)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve termination.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Declines the Termination Request (Reverts to Active)
     */
    public function declineTermination(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->first();

            if (!$partnership || $partnership->status !== 'pending_termination' || empty($partnership->distributor_termination_signed_at)) {
                return response()->json(['success' => false, 'message' => 'Valid pending distributor-initiated termination not found.'], 404);
            }

            DB::table('service_provider_distributors')->where('id', $partnership->id)->update([
                'status' => 'active',
                'updated_at' => Carbon::now(),
                // Completely clear ALL termination paths safely
                'termination_path' => null,
                'distributor_termination_signed_at' => null,
                'distributor_termination_signature_path' => null,
                'sp_termination_signed_at' => null,
                'sp_termination_signature_path' => null,
            ]);

            // Dispatch Emails
            try {
                $distributor = User::with('distributorRequirement')->find($partnership->distributor_id);
                $distEmail = $distributor->email;
                $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
                $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';

                app()->terminating(function () use ($distEmail, $spName, $distName) {
                    try {
                        if ($distEmail) {
                            $distSubject = "Notice: Termination Request Declined - {$spName}";
                            $distHtml = "
                                <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                                    <h2 style='color: #f59e0b;'>Termination Declined</h2>
                                    <p>Please be advised that <b>{$spName}</b> has <b>declined</b> your request to terminate the partnership.</p>
                                    <p>The status of the commercial relationship has been reverted back to <b>Active</b>.</p>
                                </div>";
                            Mail::html($distHtml, function($msg) use ($distEmail, $distSubject) {
                                $msg->to($distEmail)->subject($distSubject);
                            });
                        }
                    } catch (\Exception $mailException) {
                        Log::error('Termination Decline Mail Error: ' . $mailException->getMessage());
                    }
                });
            } catch (\Exception $e) {
                Log::error('Mail hook failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Termination successfully declined. Partnership restored.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline termination.', 'error' => $e->getMessage()], 500);
        }
    }
}