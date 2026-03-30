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
                    
                    // Check if there's an existing partnership or request
                    $partnership = ServiceProviderDistributor::where('service_provider_id', $user->id)
                        ->where('distributor_id', $distributor->id)
                        ->first();

                    // Get specialties from their actual active products
                    $specialties = DB::table('distributor_products')
                        ->where('distributor_id', $distributor->id)
                        ->where('is_active', 1)
                        ->distinct()
                        ->pluck('category')
                        ->toArray();

                    if (empty($specialties)) {
                        $specialties = ['General Materials'];
                    }

                    // Format Address
                    $address = 'Location not provided';
                    if ($distributor->distributorRequirement && $distributor->distributorRequirement->address) {
                        $addr = $distributor->distributorRequirement->address;
                        $address = "{$addr->city}, {$addr->province}";
                    }

                    // Fetch Formal Agreement Paper if exists
                    $agreementUrl = null;
                    if ($partnership && $partnership->agreement_path) {
                        $agreementUrl = asset('storage/' . $partnership->agreement_path);
                    }

                    return [
                        'id' => $distributor->id,
                        'name' => $distributor->distributorRequirement->company_name ?? $distributor->full_name,
                        'location' => $address,
                        'specialties' => $specialties,
                        'rating' => 4.5, // You can make this dynamic if you have a ratings table later
                        'status' => $partnership ? $partnership->status : 'none',
                        'agreement_url' => $agreementUrl,
                        'logoUrl' => null // Update if you store a specific business logo
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
     * Send a partnership request to a distributor.
     */
    public function requestPartnership(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'distributor_id' => 'required|exists:users,id',
                'request_message' => 'nullable|string|max:1000',
                'signature' => 'required|string', // Base64 signature
            ]);

            /** @var \App\Models\User $user */
            $user = \App\Models\User::find(Auth::id());

            // Check if request already exists
            $existing = ServiceProviderDistributor::where('service_provider_id', $user->id)
                ->where('distributor_id', $request->distributor_id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'A partnership request already exists with this distributor.'
                ], 400);
            }

            // Save the Signature to Storage
            $signaturePath = null;
            if ($request->filled('signature')) {
                $imageParts = explode(";base64,", $request->signature);
                if (count($imageParts) == 2) {
                    $imageTypeAux = explode("image/", $imageParts[0]);
                    $imageType = $imageTypeAux[1] ?? 'png';
                    $imageBase64 = base64_decode($imageParts[1]);
                    
                    $fileName = 'sp_' . $user->id . '_dist_' . $request->distributor_id . '_' . time() . '.' . $imageType;
                    $signaturePath = 'agreements/signatures/' . $fileName;
                    Storage::disk('public')->put($signaturePath, $imageBase64);
                }
            }

            // Generate HTML Agreement Document
            $distributor = User::with('distributorRequirement')->find($request->distributor_id);
            $user->load('serviceProviderRequirement');
            
            $dateStr = now()->format('F j, Y');
            $spName = $user->serviceProviderRequirement->company_name ?? $user->full_name;
            $distName = $distributor->distributorRequirement->company_name ?? $distributor->full_name;
            $signatureImgUrl = $signaturePath ? asset('storage/' . $signaturePath) : '';

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
                    <p><strong>1. Formation of Agreement:</strong> By executing this document, the Service Provider extends a formal proposal to the Distributor to enter into a commercial supply relationship. Final approval by the Distributor constitutes acceptance of this agreement.</p>
                    <p><strong>2. Procurement and Pricing:</strong> Upon acceptance, the Service Provider shall be eligible to procure materials under established partner-tier pricing. The Distributor reserves the exclusive right to modify pricing tiers and stock availability.</p>
                    <p><strong>3. Compliance and Representation:</strong> The Service Provider agrees to maintain all necessary local business licenses and industry certifications.</p>
                    <p><strong>4. Confidentiality:</strong> Both parties mutually agree to hold in strict confidence any sensitive business intelligence shared during this partnership.</p>
                </div>
                
                <div class='signatures'>
                    <div class='signature-box'>
                        <div class='sig-label'>Service Provider</div>
                        <div class='signature-line'>
                            " . ($signatureImgUrl ? "<img src='{$signatureImgUrl}' alt='SP Signature' />" : "") . "
                        </div>
                        <div class='sig-details'>
                            <p><strong>Signed by:</strong> {$spName}</p>
                            <p><strong>Date:</strong> {$dateStr}</p>
                        </div>
                    </div>
                    <div class='signature-box'>
                        <div class='sig-label'>Distributor</div>
                        <div class='signature-line'>
                            <span style='color: #cbd5e1; font-style: italic; position: absolute; bottom: 10px;'>Awaiting Signature</span>
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

            // Create new request in database
            $partnership = ServiceProviderDistributor::create([
                'service_provider_id' => $user->id,
                'distributor_id' => $request->distributor_id,
                'status' => 'pending',
                'request_message' => $request->request_message,
                'agreement_path' => $agreementPath,
                'sp_signature_path' => $signaturePath,
                'sp_signed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Partnership request sent successfully!',
                'data' => $partnership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}