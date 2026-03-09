<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceJobController extends Controller
{
    /**
     * Fetch all job requests for the authenticated Service Provider
     */
    public function index(Request $request)
    {
        try {
            $providerId = Auth::id(); 
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

            $jobRequests = ClientServiceRequest::with(['client', 'serviceOffering'])
                ->where('provider_id', $providerId)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedRequests = $jobRequests->map(function ($req) use ($baseUrl) {
                // Fetch the Official Deal and Payment Term if they exist
                $deal = OfficialDeal::where('client_service_request_id', $req->id)->latest()->first();
                $paymentTerm = $deal ? OfficialPaymentTerm::where('official_deal_id', $deal->id)->latest()->first() : null;
                
                if ($paymentTerm && $paymentTerm->proof_of_payment) {
                     $cleanProof = preg_replace('/^\/?storage\//', '', $paymentTerm->proof_of_payment);
                     $paymentTerm->proof_of_payment_url = $baseUrl . '/storage/' . ltrim($cleanProof, '/');
                }

                $req->official_deal = $deal;
                $req->payment_term = $paymentTerm;
                
                return $req;
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRequests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service job requests.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve the job request (Changes status to 'verifying')
     */
    public function approve($id)
    {
        try {
            $providerId = Auth::id();
            
            $job = ClientServiceRequest::where('provider_id', $providerId)->findOrFail($id);
            $job->status = 'verifying';
            $job->save();

            return response()->json([
                'success' => true,
                'message' => 'Job request approved successfully.',
                'data' => $job
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve job request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject the job request (Changes status to 'rejected')
     */
    public function reject($id)
    {
        try {
            $providerId = Auth::id();
            
            $job = ClientServiceRequest::where('provider_id', $providerId)->findOrFail($id);
            $job->status = 'rejected';
            $job->save();

            return response()->json([
                'success' => true,
                'message' => 'Job request rejected successfully.',
                'data' => $job
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject job request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve the On-Hand Payment Proof
     */
    public function approvePaymentProof($termId)
    {
        try {
            $term = OfficialPaymentTerm::findOrFail($termId);
            $term->status = 'paid';
            $term->save();

            // Mark the transaction ledger as completed too
            $transaction = ServicePaymentTransaction::where('payment_term_id', $term->id)->first();
            if ($transaction) {
                $transaction->status = 'completed';
                $transaction->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment proof verified successfully. Status changed to Paid.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve proof.'], 500);
        }
    }

    /**
     * Fetch SP's GCash Details & Total Earnings
     */
    public function getGcashDetails()
    {
        try {
            $providerId = Auth::id();
            
            // FIX 1: Use the correct column name "service_provider_id" based on the SQL schema
            $settings = DB::table('service_provider_payment_settings')
                ->where('service_provider_id', $providerId)
                ->first();
            
            // FIX 2: Fetch the provider's name from the Users table since gcash_name doesn't exist in settings
            $provider = User::find($providerId);
            $providerName = $provider ? $provider->first_name . ' ' . $provider->last_name : 'Not configured';
            
            // Sum all completed GCash transactions for this provider
            $totalGcashEarnings = ServicePaymentTransaction::where('provider_id', $providerId)
                ->where('payment_method', 'gcash')
                ->where('status', 'completed')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'gcash_number' => $settings && $settings->gcash_number ? $settings->gcash_number : 'Not configured',
                    'gcash_name' => $providerName,
                    'total_earnings' => (float) $totalGcashEarnings
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch GCash info: ' . $e->getMessage()
            ], 500);
        }
    }
}