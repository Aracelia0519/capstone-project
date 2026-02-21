<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use App\Models\OperationDistributor\ServiceProviderAgreement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceProviderRequestController extends Controller
{
    /**
     * Fetch all service provider requests for the current distributor
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $od = $user->operationalDistributor;

            if (!$od) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access. Only Operational Distributors can access this.'], 403);
            }

            $distributorId = $od->parent_distributor_id;

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
                    ];
                });

            return response()->json(['success' => true, 'data' => $requests], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requests', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve partnership request and generate an agreement document
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user(); // Operational Distributor Head
            $od = $user->operationalDistributor;

            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)
                    ->where('distributor_id', $od->parent_distributor_id)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found.'], 404);
            }

            // Update Status
            $req->update([
                'status' => 'active',
                'approved_at' => Carbon::now()
            ]);

            // Get Names instead of IDs
            $odHeadName = $user->full_name ?? 'Unknown Operational Distributor';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';

            // --- Generate Formal Agreement Document ---
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; border: 1px solid #ddd;'>
                    <h1 style='text-align: center; color: #333;'>Official Partnership Agreement</h1>
                    <p><strong>Effective Date:</strong> " . Carbon::now()->toFormattedDateString() . "</p>
                    <hr/>
                    <h3>Parties Involved</h3>
                    <ul>
                        <li><strong>Operational Distributor Head:</strong> {$odHeadName}</li>
                        <li><strong>Service Provider:</strong> {$spName}</li>
                    </ul>
                    <h3>Terms of Agreement</h3>
                    <p>This document certifies that the aforementioned Service Provider is officially authorized to procure materials, access wholesale catalog pricing, and submit platform requests to the Distributor. Both parties are bound to platform compliance and confidentiality standards.</p>
                    <br/><br/>
                    <p><em>Electronically Signed and Verified by System.</em></p>
                </div>
            ";

            // Save Document to Storage
            $fileName = 'agreements/sp_dist_agreement_' . $req->id . '_' . time() . '.html';
            Storage::disk('public')->put($fileName, $htmlContent);

            // Record Agreement to Database
            ServiceProviderAgreement::create([
                'service_provider_distributor_id' => $req->id,
                'document_path' => $fileName,
                'generated_at' => Carbon::now()
            ]);

            return response()->json(['success' => true, 'message' => 'Partnership Approved and Agreement Document generated.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Decline partnership request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $request->validate(['reason' => 'required|string|max:1000']);

            $user = Auth::user();
            $od = $user->operationalDistributor;

            $req = ServiceProviderDistributor::where('id', $id)
                    ->where('distributor_id', $od->parent_distributor_id)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found.'], 404);
            }

            $req->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);

            return response()->json(['success' => true, 'message' => 'Partnership request declined.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline request.', 'error' => $e->getMessage()], 500);
        }
    }
}