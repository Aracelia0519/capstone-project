<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use App\Events\Partnership\PartnershipStatusUpdated;
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
     * Fetch all verified distributors and their partnership status.
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

                    $status = $partnership ? $partnership->status : null;
                    
                    // Mark as expired if active and past the contract end date
                    if ($status === 'active' && $partnership->contract_end_date && Carbon::parse($partnership->contract_end_date)->isPast()) {
                        $status = 'expired';
                    }

                    return [
                        'id' => $distributor->id,
                        'partnership_id' => $partnership ? $partnership->id : null,
                        'name' => $distributor->distributorRequirement->company_name ?? 'Unknown Distributor',
                        'location' => $locationString,
                        'specialties' => $specialties,
                        'status' => $status,
                        'contract_end_date' => $partnership ? $partnership->contract_end_date : null,
                        'proposed_end_date' => $partnership ? $partnership->proposed_end_date : null,
                        'last_proposed_by' => $partnership ? ($partnership->last_proposed_by ?? 'service_provider') : null,
                        'agreement_url' => ($partnership && $partnership->agreement_path) ? asset('storage/' . $partnership->agreement_path) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $distributors,
                'current_user_id' => $user->id
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch distributors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function saveSignature($signatureBase64, $userId, $prefix = 'sp_')
    {
        $imageParts = explode(";base64,", $signatureBase64);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1];
        $imageBase64 = base64_decode($imageParts[1]);
        $fileName = $prefix . $userId . '_' . time() . '.' . $imageType;
        
        $signaturePath = 'agreements/signatures/' . $fileName;
        Storage::disk('public')->put($signaturePath, $imageBase64);
        return $signaturePath;
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
                'proposed_end_date' => 'required|date|after_or_equal:' . now()->addDays(28)->format('Y-m-d'),
                'signature' => 'required|string',
            ], [
                'proposed_end_date.after_or_equal' => 'Contract must be at least 1 month in duration.'
            ]);

            $user = Auth::user();
            $distributor = User::with('distributorRequirement')->find($request->distributor_id);

            $existing = ServiceProviderDistributor::where('service_provider_id', $user->id)
                ->where('distributor_id', $request->distributor_id)
                ->whereIn('status', ['pending', 'negotiating', 'active'])
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'A pending, negotiating, or active partnership already exists.'], 400);
            }

            $signaturePath = $this->saveSignature($request->signature, $user->id);

            ServiceProviderDistributor::updateOrCreate(
                ['service_provider_id' => $user->id, 'distributor_id' => $request->distributor_id],
                [
                    'status' => 'pending',
                    'request_message' => $request->request_message,
                    'proposed_end_date' => $request->proposed_end_date,
                    'last_proposed_by' => 'service_provider',
                    'sp_signature_path' => $signaturePath,
                    'sp_signed_at' => now(),
                ]
            );

            event(new PartnershipStatusUpdated($request->distributor_id, $user->id, 'partnership_requested'));
            return response()->json(['success' => true, 'message' => 'Partnership request sent successfully!'], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send request', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Counters a Distributor's proposed date
     */
    public function counterProposal(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'signature' => 'required|string',
                'proposed_end_date' => 'required|date|after_or_equal:' . now()->addDays(28)->format('Y-m-d')
            ], [
                'proposed_end_date.after_or_equal' => 'Contract must be at least 1 month in duration.'
            ]);

            $user = Auth::user();
            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->firstOrFail();

            $signaturePath = $this->saveSignature($request->signature, $user->id);

            $partnership->update([
                'status' => 'negotiating',
                'proposed_end_date' => $request->proposed_end_date,
                'last_proposed_by' => 'service_provider',
                'sp_signature_path' => $signaturePath,
                'sp_signed_at' => now(),
            ]);

            event(new PartnershipStatusUpdated($partnership->distributor_id, $user->id, 'contract_countered'));
            return response()->json(['success' => true, 'message' => 'Counter-proposal sent successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to counter proposal', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Accepts Distributor's proposed date and finalizes contract
     */
    public function acceptProposal(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([ 'signature' => 'required|string' ]);
            $user = Auth::user();
            
            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->where('last_proposed_by', 'distributor')
                            ->firstOrFail();

            $signaturePath = $this->saveSignature($request->signature, $user->id);
            
            $distributor = User::with('distributorRequirement')->find($partnership->distributor_id);
            $spName = $user->serviceProviderRequirement->business_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? 'Authorized Distributor';
            $dateStr = now()->format('F j, Y');
            $endDateStr = Carbon::parse($partnership->proposed_end_date)->format('F j, Y');

            $spSigUrl = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($signaturePath));
            $distSigUrl = $partnership->distributor_signature_path ? 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($partnership->distributor_signature_path)) : '';

            $agreementHtml = "
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
                    <p>Commercial Supply Relationship</p>
                </div>

                <div class='date-ref'>Effective Date: {$dateStr} <br/> Expiration Date: {$endDateStr}</div>
                
                <div class='parties'>
                    <p>This Partnership Agreement is formally finalized between:</p>
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
                        <div class='signature-line'><img src='{$spSigUrl}' alt='SP Signature' /></div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Distributor (Authorized Rep)</div>
                        <div class='signature-line'><img src='{$distSigUrl}' alt='Distributor Signature' /></div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$distName}</p>
                            <p><strong>Date:</strong> " . ($partnership->distributor_signed_at ? Carbon::parse($partnership->distributor_signed_at)->format('F j, Y') : 'Unknown') . "</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $agreementPath = 'agreements/sp_partnerships/final_' . $user->id . '_' . $partnership->distributor_id . '_' . time() . '.html';
            Storage::disk('public')->put($agreementPath, $agreementHtml);

            $partnership->update([
                'status' => 'active',
                'contract_end_date' => $partnership->proposed_end_date,
                'agreement_path' => $agreementPath,
                'sp_signature_path' => $signaturePath,
                'sp_signed_at' => now(),
            ]);

            event(new PartnershipStatusUpdated($partnership->distributor_id, $user->id, 'contract_accepted'));
            return response()->json(['success' => true, 'message' => 'Contract Finalized.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to finalize contract', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * SP Requests Renewal of an expired or active contract
     */
    public function renewContract(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'signature' => 'required|string',
                'proposed_end_date' => 'required|date|after_or_equal:' . now()->addDays(28)->format('Y-m-d'),
                'message' => 'nullable|string|max:1000'
            ], [
                'proposed_end_date.after_or_equal' => 'Contracts must be at least 1 month in duration.'
            ]);

            $partnership = ServiceProviderDistributor::where('id', $id)
                            ->where('service_provider_id', $user->id)
                            ->firstOrFail();

            $signaturePath = $this->saveSignature($request->signature, $user->id, 'sp_renew_');

            $message = $request->message ?? 'We would like to formally request the renewal of our commercial partnership contract.';

            $partnership->update([
                'status' => 'pending',
                'request_message' => $message,
                'proposed_end_date' => $request->proposed_end_date,
                'last_proposed_by' => 'service_provider',
                'sp_signature_path' => $signaturePath,
                'sp_signed_at' => now(),
                'distributor_signature_path' => null,
                'distributor_signed_at' => null,
            ]);

            event(new PartnershipStatusUpdated($partnership->distributor_id, $user->id, 'reactivation_requested'));
            return response()->json(['success' => true, 'message' => 'Renewal requested successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to request renewal.', 'error' => $e->getMessage()], 500);
        }
    }
}