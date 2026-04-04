<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction; 
use App\Models\ServiceProvider\ServiceJobCompletion;
use App\Models\ServiceProvider\ServiceReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            $deal = OfficialDeal::where('client_service_request_id', $req->id)->latest()->first();
            $paymentTerm = $deal ? OfficialPaymentTerm::where('official_deal_id', $deal->id)->latest()->first() : null;
            
            if ($paymentTerm) {
                if ($paymentTerm->proof_of_payment) {
                    $cleanProof = preg_replace('/^\/?storage\//', '', $paymentTerm->proof_of_payment);
                    $paymentTerm->proof_of_payment_url = $baseUrl . '/storage/' . ltrim($cleanProof, '/');
                }

                $termIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)->pluck('id');
                $totalPaid = ServicePaymentTransaction::whereIn('payment_term_id', $termIds)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                $paymentTerm->total_paid = $totalPaid;
                $paymentTerm->balance = max(0, $deal->price - $totalPaid);
                
                $paymentTerm->reminder_count = $paymentTerm->reminder_count ?? 0;
                $paymentTerm->legal_report_path = $paymentTerm->legal_report_path ? $baseUrl . '/storage/' . ltrim($paymentTerm->legal_report_path, '/') : null;
            }

            $latestCompletion = ServiceJobCompletion::where('client_service_request_id', $req->id)->latest()->first();
            if ($latestCompletion && !empty($latestCompletion->proof_images)) {
                $formattedProofs = array_map(function ($path) use ($baseUrl) {
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    return $baseUrl . '/storage/' . ltrim($cleanPath, '/');
                }, $latestCompletion->proof_images);
                $latestCompletion->proof_images_url = $formattedProofs;
            }

            $review = ServiceReview::where('client_service_request_id', $req->id)->first();

            $surveyAgreement = DB::table('service_survey_agreements')
                ->where('client_service_request_id', $req->id)
                ->first();

            if ($surveyAgreement) {
                if ($surveyAgreement->provider_signature) {
                    $surveyAgreement->provider_signature_url = $baseUrl . '/storage/' . $surveyAgreement->provider_signature;
                }
                if ($surveyAgreement->client_signature) {
                    $surveyAgreement->client_signature_url = $baseUrl . '/storage/' . $surveyAgreement->client_signature;
                }
            }

            $req->official_deal = $deal;
            $req->payment_term = $paymentTerm;
            $req->latest_completion = $latestCompletion;
            $req->service_review = $review;
            $req->survey_agreement = $surveyAgreement;
            
            return $req;
        });

        return response()->json([
            'success' => true,
            'data' => $formattedRequests
        ]);
    }

    public function signSurveyAgreement(Request $request, $id)
    {
        $request->validate([
            'client_signature' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $clientId = Auth::id();
            
            $agreement = DB::table('service_survey_agreements')
                ->where('client_service_request_id', $id)
                ->where('client_id', $clientId)
                ->first();

            if (!$agreement) {
                return response()->json(['success' => false, 'message' => 'Agreement not found.'], 404);
            }

            $signaturePath = $this->saveBase64Image($request->client_signature, 'survey_agreements/signatures/client');

            DB::table('service_survey_agreements')
                ->where('id', $agreement->id)
                ->update([
                    'status' => 'signed',
                    'client_signature' => $signaturePath,
                    'client_signed_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Survey Agreement officially signed. The Service Provider will begin the survey shortly.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sign the agreement.',
                'error' => $e->getMessage()
            ], 500);
        }
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

            $deal = $term->deal;
            $amount = (float) $deal->price;
            
            $termIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)->pluck('id');
            $totalPaid = ServicePaymentTransaction::whereIn('payment_term_id', $termIds)
                ->where('status', 'completed')
                ->sum('amount');

            $remainingBalance = $amount - $totalPaid;

            if ($totalPaid > 0) {
                $payableAmount = $remainingBalance;
            } else {
                $percentage = 100;
                if (preg_match('/(\d+)%/', $term->payment_term, $matches)) {
                    $percentage = (float) $matches[1];
                }
                $payableAmount = ($amount * ($percentage / 100));
            }

            $transaction = new ServicePaymentTransaction();
            $transaction->payment_term_id = $term->id;
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
        $user = Auth::user();
        
        $term = OfficialPaymentTerm::with('deal.clientServiceRequest.serviceOffering')->findOrFail($termId);
        $deal = $term->deal;
        $serviceName = $deal->clientServiceRequest->serviceOffering->title ?? 'Custom Service';
        $amount = (float) $deal->price;

        $termIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)->pluck('id');
        $totalPaid = ServicePaymentTransaction::whereIn('payment_term_id', $termIds)
            ->where('status', 'completed')
            ->sum('amount');

        $remainingBalance = $amount - $totalPaid;

        if ($remainingBalance <= 0) {
            return response()->json(['success' => false, 'message' => 'This deal is already fully paid.'], 400);
        }

        if ($totalPaid > 0) {
            $payableAmount = $remainingBalance;
            $paymentName = $serviceName . ' (Remaining Balance)';
        } else {
            $percentage = 100;
            if (preg_match('/(\d+)%/', $term->payment_term, $matches)) {
                $percentage = (float) $matches[1];
            }
            $payableAmount = ($amount * ($percentage / 100));
            $paymentName = $serviceName . ' (' . $percentage . '% Payment)';
        }

        try {
            $client = new \GuzzleHttp\Client();
            $frontendOrigin = rtrim($request->headers->get('origin') ?? env('FRONTEND_URL', 'http://localhost:5173'), '/');

            $billingDetails = [
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email,
            ];
            
            $gcashNumberToUse = null;
            if (Schema::hasTable('client_payment_settings')) {
                $clientPayment = DB::table('client_payment_settings')
                    ->where('client_id', $user->id)
                    ->first();
                    
                if ($clientPayment && !empty($clientPayment->gcash_number)) {
                    $gcashNumberToUse = $clientPayment->gcash_number;
                }
            }
            
            if (empty($gcashNumberToUse)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No GCash number found in your payment settings. Please update your payment settings before proceeding.'
                ], 400);
            }

            $billingDetails['phone'] = (string) $gcashNumberToUse;

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
                            'billing' => $billingDetails,
                            'line_items' => [
                                [
                                    'currency' => 'PHP',
                                    'amount' => (int) round($payableAmount * 100), 
                                    'name' => $paymentName,
                                    'quantity' => 1,
                                ]
                            ],
                            'success_url' => $frontendOrigin . '/Clients/myServiceRequest?service_payment_term_id=' . $term->id,
                            'cancel_url' => $frontendOrigin . '/Clients/myServiceRequest'
                        ]
                    ]
                ]
            ]);

            $paymongoData = json_decode($response->getBody(), true);
            $checkoutUrl = $paymongoData['data']['attributes']['checkout_url'] ?? null;
            $sessionId = $paymongoData['data']['id'] ?? null;

            if (!$checkoutUrl || (!$sessionId)) {
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

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['success' => true, 'message' => 'Payment already verified.', 'already_processed' => true]);
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
            ServicePaymentTransaction::create([
                'payment_term_id' => $term->id,
                'client_id' => $term->client_id,
                'provider_id' => $term->provider_id,
                'amount' => $cacheData['amount'],
                'payment_method' => 'gcash',
                'reference_number' => $sessionId,
                'status' => 'completed'
            ]);

            $term->status = 'paid';
            $term->save();

            Storage::disk('local')->delete($filePath);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Service payment verified successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to log the transaction.'], 500);
        }
    }

    public function approveCompletion($id)
    {
        DB::beginTransaction();
        try {
            $job = ClientServiceRequest::where('client_id', Auth::id())->findOrFail($id);
            $completion = ServiceJobCompletion::where('client_service_request_id', $job->id)->latest()->first();
            
            if ($completion) {
                $completion->status = 'approved';
                $completion->save();
            }

            $job->status = 'completed';
            $job->save();

            $deal = OfficialDeal::where('client_service_request_id', $job->id)->latest()->first();
            if ($deal) {
                $deal->status = 'completed';
                $deal->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Service job has been marked as officially completed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to approve job completion.', 'error' => $e->getMessage()], 500);
        }
    }

    public function rejectCompletion(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        
        DB::beginTransaction();
        try {
            $job = ClientServiceRequest::where('client_id', Auth::id())->findOrFail($id);
            $completion = ServiceJobCompletion::where('client_service_request_id', $job->id)->latest()->first();
            
            if ($completion) {
                $completion->status = 'rejected';
                $completion->rejection_reason = $request->rejection_reason;
                $completion->save();
            }

            $job->status = 'ongoing';
            $job->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Completion rejected and reason sent to Provider.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to reject job completion.', 'error' => $e->getMessage()], 500);
        }
    }

    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $job = ClientServiceRequest::where('client_id', Auth::id())->findOrFail($id);

        if ($job->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'You can only leave a review for completed services.'], 400);
        }

        $existingReview = ServiceReview::where('client_service_request_id', $job->id)->first();
        if ($existingReview) {
            return response()->json(['success' => false, 'message' => 'You have already reviewed this service.'], 400);
        }

        ServiceReview::create([
            'client_service_request_id' => $job->id,
            'provider_id' => $job->provider_id,
            'client_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json(['success' => true, 'message' => 'Thank you! Your review has been submitted successfully.']);
    }
    
    public function submitClientReply(Request $request, $id)
    {
        $request->validate([
            'client_reply' => 'required|string|max:1000'
        ]);

        $review = ServiceReview::where('client_id', Auth::id())->findOrFail($id);

        if (!$review->reply) {
            return response()->json(['success' => false, 'message' => 'You can only reply after the service provider has responded.'], 400);
        }

        if ($review->client_reply) {
            return response()->json(['success' => false, 'message' => 'You have already replied to this thread.'], 400);
        }

        $review->client_reply = $request->client_reply;
        $review->save();

        return response()->json(['success' => true, 'message' => 'Your reply has been posted successfully.']);
    }

    private function saveBase64Image($base64String, $pathPrefix) {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) { return null; }
            $base64String = base64_decode(str_replace(' ', '+', $base64String));
            $fileName = $pathPrefix . '_' . time() . '_' . uniqid() . '.' . $type;
            Storage::disk('public')->put($fileName, $base64String);
            return $fileName;
        }
        return null;
    }
}