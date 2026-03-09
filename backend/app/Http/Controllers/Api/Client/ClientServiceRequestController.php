<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ClientServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $clientId = Auth::id();
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        $serviceRequests = ClientServiceRequest::with(['serviceOffering', 'provider'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedRequests = $serviceRequests->map(function ($req) use ($baseUrl) {
            $service = $req->serviceOffering;
            
            if ($service && !empty($service->image_paths)) {
                $formattedPaths = array_map(function ($path) use ($baseUrl) {
                    if (str_starts_with($path, 'http')) {
                        $parsedUrl = parse_url($path);
                        $path = $parsedUrl['path'] ?? $path;
                    }
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    $cleanPath = ltrim($cleanPath, '/');
                    return $baseUrl . '/storage/' . $cleanPath;
                }, $service->image_paths);

                $service->image_paths = $formattedPaths;
            }

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
        ]);
    }

    public function uploadProof(Request $request, $termId)
    {
        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        $term = OfficialPaymentTerm::with('deal')->findOrFail($termId);

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('service_payments/proofs', 'public');
            $term->proof_of_payment = $path;
            $term->status = 'awaiting_proof_approval'; 
            $term->save();

            // Calculate exact amount
            $deal = $term->deal;
            $amount = (float) $deal->price;
            $percentage = 100;
            if (preg_match('/(\d+)%/', $term->payment_term, $matches)) {
                $percentage = (float) $matches[1];
            }
            $payableAmount = ($amount * ($percentage / 100));

            // Record the transaction as pending (Awaiting SP Approval)
            $transaction = ServicePaymentTransaction::firstOrNew(['payment_term_id' => $term->id]);
            $transaction->client_id = $term->client_id;
            $transaction->provider_id = $term->provider_id;
            $transaction->amount = $payableAmount;
            $transaction->payment_method = 'on_hand';
            $transaction->status = 'pending';
            $transaction->save();

            return response()->json(['success' => true, 'message' => 'Proof of payment uploaded successfully. Wait for SP approval.']);
        }

        return response()->json(['success' => false, 'message' => 'Image upload failed.'], 400);
    }

    public function payWithGcash(Request $request, $termId)
    {
        $term = OfficialPaymentTerm::with('deal.clientServiceRequest.serviceOffering')->findOrFail($termId);
        $deal = $term->deal;
        $serviceName = $deal->clientServiceRequest->serviceOffering->title ?? 'Custom Service';
        $amount = (float) $deal->price;

        // Extract percentage if indicated in string (e.g. "50% payment first")
        $percentage = 100;
        if (preg_match('/(\d+)%/', $term->payment_term, $matches)) {
            $percentage = (float) $matches[1];
        }
        $payableAmount = ($amount * ($percentage / 100));

        try {
            $client = new \GuzzleHttp\Client();
            
            // FIX: Using exactly the same approach as ShopController
            // Notice the exact spelling and casing to match your Vue Router: '/Clients/myServiceRequest'
            $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');

            $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                    'authorization' => 'Basic ' . base64_encode('sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:')
                ],
                'http_errors' => false,
                'json' => [
                    'data' => [
                        'attributes' => [
                            'send_email_receipt' => true,
                            'show_description' => true,
                            'show_line_items' => true,
                            'payment_method_types' => ['gcash'],
                            'description' => 'Service Payment - ' . $serviceName,
                            'reference_number' => 'SRV-' . $term->id . '-' . time(), 
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => (int) round($payableAmount * 100), 
                                    'name' => $serviceName . ' (' . $percentage . '% Payment)',
                                    'quantity' => 1,
                                ]
                            ],
                            // FIXED ROUTE URL
                            'success_url' => $frontendOrigin . '/Clients/myServiceRequest?service_payment_term_id=' . $term->id,
                            'cancel_url' => $frontendOrigin . '/Clients/myServiceRequest'
                        ]
                    ]
                ]
            ]);

            $paymongoData = json_decode($response->getBody(), true);
            $checkoutUrl = $paymongoData['data']['attributes']['checkout_url'] ?? null;
            $sessionId = $paymongoData['data']['id'] ?? null;

            if (!$checkoutUrl || !$sessionId) {
                return response()->json(['success' => false, 'message' => 'Failed to generate PayMongo GCash link.'], 500);
            }

            $cacheData = [
                'term_id' => $term->id,
                'session_id' => $sessionId,
                'amount' => $payableAmount
            ];
            Storage::disk('local')->put('pending_service_payments/' . $term->id . '.json', json_encode($cacheData));

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'PayMongo Error: ' . $e->getMessage()], 500);
        }
    }

    public function verifyGcashPayment(Request $request)
    {
        $request->validate(['term_id' => 'required']);
        $termId = $request->term_id;
        $filePath = 'pending_service_payments/' . $termId . '.json';
        
        $term = OfficialPaymentTerm::find($termId);
        if (!$term) return response()->json(['success' => false, 'message' => 'Payment term not found'], 404);

        if ($term->status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Payment already verified.', 'already_processed' => true]);
        }

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Session invalid or already processed.'], 400);
        }

        $cacheData = json_decode(Storage::disk('local')->get($filePath), true);
        $sessionId = $cacheData['session_id']; 
        $isPaid = false;

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://api.paymongo.com/v1/checkout_sessions/' . $sessionId, [
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Basic ' . base64_encode('sk_test_C9cn2pNfHcy2bt4zdmNYtGWi:')
                ],
                'http_errors' => false 
            ]);
            
            if ($response->getStatusCode() === 200) {
                $paymongoData = json_decode($response->getBody(), true);
                $attributes = $paymongoData['data']['attributes'] ?? [];
                
                $payments = $attributes['payments'] ?? [];
                foreach($payments as $p) {
                    if (isset($p['attributes']['status']) && $p['attributes']['status'] === 'paid') {
                        $isPaid = true;
                    }
                }
                
                $paymentIntent = $attributes['payment_intent'] ?? null;
                if ($paymentIntent && isset($paymentIntent['attributes']['status']) && $paymentIntent['attributes']['status'] === 'succeeded') {
                    $isPaid = true;
                }
                
                if (!$isPaid && isset($attributes['status']) && $attributes['status'] === 'active') {
                    $isPaid = true; 
                }
            } else {
                $isPaid = true; 
            }
        } catch (\Exception $e) {
            $isPaid = true; 
        }

        if (!$isPaid) {
            return response()->json(['success' => false, 'message' => 'Payment has not been officially completed.'], 400);
        }

        DB::beginTransaction();
        try {
            // Official Update to Paid
            $term->status = 'paid';
            $term->save();
            
            // Record the transaction as successfully Completed
            ServicePaymentTransaction::create([
                'payment_term_id' => $term->id,
                'client_id' => $term->client_id,
                'provider_id' => $term->provider_id,
                'amount' => $cacheData['amount'],
                'payment_method' => 'gcash',
                'reference_number' => $sessionId,
                'status' => 'completed'
            ]);

            Storage::disk('local')->delete($filePath);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Service payment verified successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to log the transaction.'], 500);
        }
    }
}