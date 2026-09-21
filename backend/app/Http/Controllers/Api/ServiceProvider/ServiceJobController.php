<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction;
use App\Models\ServiceProvider\MaterialExpenseRequest;
use App\Models\ServiceProvider\MaterialExpenseItem;
use App\Models\ServiceProvider\ServiceJobCompletion;
use App\Models\ServiceProvider\DailyWorkLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Events\ServiceProvider\ServiceRequestUpdated;
use App\Support\DailyBilling;
use App\Support\MaterialExpenses;
use App\Support\ProviderGroups;

class ServiceJobController extends Controller
{
    public function index(Request $request)
    {
        try {
            $providerId = Auth::id(); 
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

            // Group jobs: every ACCEPTED member of the group sees the request.
            $groupIds = ProviderGroups::acceptedGroupIds($providerId);
            $jobRequests = ClientServiceRequest::with(['client', 'serviceOffering'])
                ->where('provider_id', $providerId)
                ->when(count($groupIds) > 0, fn($q) => $q->orWhereIn('group_id', $groupIds))
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedRequests = $jobRequests->map(function ($req) use ($baseUrl, $providerId) {
                $deal = OfficialDeal::where('client_service_request_id', $req->id)->latest()->first();
                $paymentTerm = $deal ? OfficialPaymentTerm::where('official_deal_id', $deal->id)->where('is_materials_term', false)->latest()->first() : null;
                
                if ($paymentTerm) {
                    if ($paymentTerm->proof_of_payment) {
                        $cleanProof = preg_replace('/^\/?storage\//', '', $paymentTerm->proof_of_payment);
                        $paymentTerm->proof_of_payment_url = $baseUrl . '/storage/' . ltrim($cleanProof, '/');
                    }

                    $termIds = OfficialPaymentTerm::where('official_deal_id', $deal->id)->where('is_materials_term', false)->pluck('id');
                    $totalPaid = ServicePaymentTransaction::whereIn('payment_term_id', $termIds)
                        ->where('status', 'completed')
                        ->sum('amount');
                    
                    $paymentTerm->total_paid = $totalPaid;
                    $paymentTerm->balance = max(0, $deal->price - $totalPaid);

                    // Add new properties mapped from database
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
                $req->survey_agreement = $surveyAgreement;

                // --- DAILY BILLING SNAPSHOT (Daily-priced services only) ---
                $dailyBilling = DailyBilling::snapshot($req, $deal, $paymentTerm, $baseUrl);
                $req->daily_billing = $dailyBilling;
                if ($dailyBilling && $paymentTerm) {
                    $paymentTerm->total_paid = $dailyBilling['total_paid'];
                    $paymentTerm->balance = $dailyBilling['outstanding'];
                }
                // -----------------------------------------------------------

                // --- MATERIALS REIMBURSEMENT (provider-bought materials) ---
                MaterialExpenses::attach($req, $deal, $baseUrl);
                // -----------------------------------------------------------

                // --- GROUP (team) metadata, action locks & revenue split ---
                ProviderGroups::attachToJob($req, $baseUrl, $providerId);
                // -----------------------------------------------------------

                // GET CLIENT EXACT LOCATION FOR LEAFLET VERIFICATION
                $clientReq = DB::table('client_requirements')
                    ->where('user_id', $req->client_id)
                    ->where('status', 'approved')
                    ->first();

                if ($clientReq) {
                    $clientAddress = DB::table('client_addresses')
                        ->where('client_requirements_id', $clientReq->id)
                        ->first();

                    if ($clientAddress && $clientAddress->latitude && $clientAddress->longitude) {
                        $req->target_lat = $clientAddress->latitude;
                        $req->target_lng = $clientAddress->longitude;
                    } else {
                        $req->target_lat = 14.4200;
                        $req->target_lng = 120.9600;
                    }
                } else {
                    $req->target_lat = 14.4200;
                    $req->target_lng = 120.9600;
                }

                // --- INVOICE, PWD DISCOUNT & RECEIPT LOGIC ---
                $pwd_discount_applied = false;
                $pwd_discount_text = null;
                if ($deal && !empty($deal->description) && str_contains($deal->description, 'PWD Discount Applied')) {
                    $pwd_discount_applied = true;
                    if (preg_match('/\[System Note:\s*(.*?)\]/i', $deal->description, $matches)) {
                        $pwd_discount_text = $matches[1];
                    } else {
                        $pwd_discount_text = 'PWD Discount Applied';
                    }
                }

                if ($deal && $paymentTerm && in_array($paymentTerm->status, ['agreed', 'awaiting_proof_approval', 'paid'])) {
                    $invoiceTotal = $deal->price;
                    $invoicePaid = $paymentTerm->total_paid ?? 0;
                    $invoiceBalance = $paymentTerm->balance ?? 0;
                    if ($dailyBilling) {
                        $invoiceTotal = $dailyBilling['total_due'];
                        $invoicePaid = $dailyBilling['total_paid'];
                        $invoiceBalance = $dailyBilling['outstanding'];
                    }
                    $req->invoice_details = [
                        'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($deal->id, 5, '0', STR_PAD_LEFT),
                        'issued_date' => $paymentTerm->created_at,
                        'paid_date' => $paymentTerm->status === 'paid' ? $paymentTerm->updated_at : null,
                        'pwd_discount_applied' => $pwd_discount_applied,
                        'pwd_discount_text' => $pwd_discount_text,
                        'total_amount' => $invoiceTotal,
                        'amount_paid' => $invoicePaid,
                        'balance' => $invoiceBalance,
                        'materials_amount' => ($req->materials_summary['total_approved'] ?? 0),
                        'payment_method' => strtoupper(str_replace('_', ' ', $paymentTerm->payment_method)),
                        'status' => $paymentTerm->status
                    ];
                }

                if ($req->status === 'completed') {
                    $req->receipt_details = [
                        'receipt_number' => 'OR-' . date('Y') . '-' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                        'completion_date' => $req->updated_at,
                        'total_paid' => $paymentTerm ? ($paymentTerm->total_paid ?? $deal->price) : ($deal ? $deal->price : 0),
                        'service_name' => $req->serviceOffering ? $req->serviceOffering->title : 'Custom Service',
                        'client_name' => $req->client ? $req->client->first_name . ' ' . $req->client->last_name : 'N/A'
                    ];
                }
                // ---------------------------------------------
                
                return $req;
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRequests,
                'provider_id' => $providerId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service job requests.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createSurveyAgreement(Request $request, $id)
    {
        $request->validate([
            'provider_signature' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $providerId = Auth::id();
            $job = $this->findGroupJob($id)->load(['client', 'serviceOffering']);
            $denied = $this->groupJobGuard($job, 'survey_agreement');
            if ($denied) return $denied;

            $agreementText = "FORMAL SURVEY AGREEMENT\n\n";
            $agreementText .= "Date Issued: " . now()->format('F j, Y') . "\n";
            $agreementText .= "Service Provider: " . Auth::user()->first_name . " " . Auth::user()->last_name . "\n";
            $agreementText .= "Client: " . ($job->client ? $job->client->first_name . " " . $job->client->last_name : "Unknown") . "\n";
            $agreementText .= "Service Location: " . ($job->address ?? 'N/A') . "\n\n";
            $agreementText .= "By signing this agreement, the Client formally authorizes the Service Provider to enter the specified premises to conduct a comprehensive site survey. ";
            $agreementText .= "This survey is required to evaluate the scope of work, verify measurements, and confirm the feasibility of the requested service: '" . ($job->serviceOffering->title ?? 'Custom Job') . "'.\n\n";
            $agreementText .= "This agreement does not commit the Client to a final contract but ensures mutual understanding and safety during the inspection phase.";

            $fileName = 'survey_agreements/agreement_job_' . $job->id . '_' . time() . '.txt';
            Storage::disk('public')->put($fileName, $agreementText);

            $signaturePath = $this->saveBase64Image($request->provider_signature, 'survey_agreements/signatures/provider');

            DB::table('service_survey_agreements')->insert([
                'client_service_request_id' => $job->id,
                'client_id' => $job->client_id,
                'provider_id' => $job->provider_id,
                'agreement_text' => $agreementText,
                'storage_path' => $fileName,
                'provider_signature' => $signaturePath,
                'status' => 'pending_client',
                'provider_signed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // Release the one-at-a-time group lock (individual jobs: no-op).
            $this->groupJobRelease($job, 'survey_agreement');

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            return response()->json([
                'success' => true,
                'message' => 'Survey agreement successfully generated, signed, and sent to the client.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create agreement.', 'error' => $e->getMessage()], 500);
        }
    }

    public function startSurvey($id)
    {
        try {
            $providerId = Auth::id();
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'start_survey');
            if ($denied) return $denied;

            DB::table('service_survey_agreements')
                ->where('client_service_request_id', $job->id)
                ->update([
                    'status' => 'in_progress',
                    'survey_started_at' => now()
                ]);

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'start_survey');

            return response()->json([
                'success' => true,
                'message' => 'Survey officially started. Please ensure you record your measurements.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to start survey.', 'error' => $e->getMessage()], 500);
        }
    }

    public function completeSurvey($id)
    {
        try {
            $providerId = Auth::id();
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'complete_survey');
            if ($denied) return $denied;

            DB::table('service_survey_agreements')
                ->where('client_service_request_id', $job->id)
                ->update([
                    'status' => 'completed',
                    'survey_completed_at' => now()
                ]);
            
            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'complete_survey');

            return response()->json([
                'success' => true,
                'message' => 'Survey completed! You may now officially Approve or Reject the job request.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to complete survey.', 'error' => $e->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        try {
            $providerId = Auth::id();
            
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'approve_request');
            if ($denied) return $denied;

            $job->status = 'verifying';
            $job->save();

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'approve_request');

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

    public function reject($id)
    {
        try {
            $providerId = Auth::id();
            
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'reject_request');
            if ($denied) return $denied;

            $job->status = 'rejected';
            $job->save();

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'reject_request');

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

    public function approvePaymentProof($termId)
    {
        try {
            $term = OfficialPaymentTerm::with('deal')->findOrFail($termId);
            $term->load('deal.clientServiceRequest.serviceOffering');
            $isDaily = DailyBilling::isDaily($term->deal?->clientServiceRequest?->serviceOffering);

            $denied = $this->groupActionGuard(
                $term->deal?->group_id ? (int) $term->deal->group_id : null,
                'official_payment_term',
                (int) $term->id,
                'approve_proof'
            );
            if ($denied) return $denied;

            if ($term->is_materials_term) {
                // Materials term: flip to 'paid' only once fully covered.
                $materialsPaid = ServicePaymentTransaction::where('payment_term_id', $term->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                $term->status = $materialsPaid >= (float) $term->amount ? 'paid' : 'agreed';
            } else {
                // Daily-priced services keep their term 'agreed' so the client can
                // keep paying each day; only fixed deals flip to 'paid'.
                $term->status = $isDaily ? 'agreed' : 'paid';
            }
            $term->save();

            $transaction = ServicePaymentTransaction::where('payment_term_id', $term->id)
                ->where('status', 'pending')
                ->first();
                
            if ($transaction) {
                $transaction->status = 'completed';
                $transaction->save();
            }

            // Broadcast Event
            event(new ServiceRequestUpdated($term->client_id, $term->provider_id));

            ProviderGroups::releaseLock((int) Auth::id(), 'official_payment_term', (int) $term->id, 'approve_proof');

            return response()->json([
                'success' => true,
                'message' => 'Payment proof verified successfully. Client balance updated.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve proof.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getGcashDetails()
    {
        try {
            $providerId = Auth::id();
            
            $settings = DB::table('service_provider_payment_settings')
                ->where('service_provider_id', $providerId)
                ->first();
            
            $provider = User::find($providerId);
            $providerName = $provider ? $provider->first_name . ' ' . $provider->last_name : 'Not configured';
            
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

    public function submitCompletion(Request $request, $id)
    {
        $request->validate([
            'proof_images' => 'required|array',
            'proof_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_bypassed' => 'nullable|boolean'
        ]);

        DB::beginTransaction();
        try {
            $providerId = Auth::id();
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'submit_completion');
            if ($denied) return $denied;

            $imagePaths = [];
            if ($request->hasFile('proof_images')) {
                foreach ($request->file('proof_images') as $image) {
                    $imagePaths[] = $image->store('service_jobs/completions', 'public');
                }
            }

            ServiceJobCompletion::create([
                'client_service_request_id' => $job->id,
                'proof_images' => $imagePaths,
                'status' => 'pending'
            ]);

            $job->status = 'completion_review';
            $job->save();

            DB::commit();

            // Release the one-at-a-time group lock (individual jobs: no-op).
            $this->groupJobRelease($job, 'submit_completion');

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            return response()->json(['success' => true, 'message' => 'Job marked as complete. Awaiting client approval.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to submit completion.', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendPaymentReminder($termId)
    {
        try {
            $term = OfficialPaymentTerm::with(['deal.clientServiceRequest.client', 'deal.clientServiceRequest.serviceOffering'])->findOrFail($termId);
            $client = $term->deal->clientServiceRequest->client;
            $serviceName = $term->deal->clientServiceRequest->serviceOffering->title ?? 'Custom Service';

            $term->reminder_count = ($term->reminder_count ?? 0) + 1;
            $term->save();

            $totalPaid = ServicePaymentTransaction::where('payment_term_id', $term->id)->where('status', 'completed')->sum('amount');
            $balance = max(0, $term->deal->price - $totalPaid);

            // Daily-priced services: outstanding = accrued days not yet paid
            $serviceOffering = $term->deal->clientServiceRequest->serviceOffering ?? null;
            if (DailyBilling::isDaily($serviceOffering)) {
                $snapshot = DailyBilling::snapshot($term->deal->clientServiceRequest, $term->deal, $term);
                if ($snapshot) {
                    $totalPaid = $snapshot['total_paid'];
                    $balance = $snapshot['outstanding'];
                }
            }

            $htmlContent = "
                <div style='font-family: sans-serif; line-height: 1.6;'>
                    <h3>Hello {$client->first_name},</h3>
                    <p>This is a formal reminder that the service <strong>{$serviceName}</strong> has been successfully completed, however, there is still an outstanding balance of <strong>₱" . number_format($balance, 2) . "</strong>.</p>
                    <p style='color: #d97706; font-weight: bold;'>This is reminder attempt " . $term->reminder_count . " of 3.</p>
                    <p>Please log in to your account and settle the remaining balance as soon as possible.</p>
                    <p>Thank you.</p>
                </div>
            ";

            Mail::html($htmlContent, function ($message) use ($client, $serviceName) {
                $message->to($client->email)
                        ->subject("Payment Reminder: Remaining Balance for {$serviceName}");
            });

            // Broadcast Event
            event(new ServiceRequestUpdated($term->client_id, $term->provider_id));

            return response()->json(['success' => true, 'message' => 'Payment reminder email sent successfully.', 'reminder_count' => $term->reminder_count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send payment reminder.', 'error' => $e->getMessage()], 500);
        }
    }

    public function generateLegalReport($termId)
    {
        try {
            $term = OfficialPaymentTerm::with(['deal.clientServiceRequest.client', 'deal.clientServiceRequest.serviceOffering', 'deal.clientServiceRequest.provider'])->findOrFail($termId);
            $client = $term->deal->clientServiceRequest->client;
            $provider = $term->deal->clientServiceRequest->provider;
            $serviceName = $term->deal->clientServiceRequest->serviceOffering->title ?? 'Custom Service';
            
            $totalPaid = ServicePaymentTransaction::where('payment_term_id', $term->id)->where('status', 'completed')->sum('amount');
            $balance = max(0, $term->deal->price - $totalPaid);

            // Daily-priced services: outstanding = accrued days not yet paid
            $serviceOffering = $term->deal->clientServiceRequest->serviceOffering ?? null;
            if (DailyBilling::isDaily($serviceOffering)) {
                $snapshot = DailyBilling::snapshot($term->deal->clientServiceRequest, $term->deal, $term);
                if ($snapshot) {
                    $totalPaid = $snapshot['total_paid'];
                    $balance = $snapshot['outstanding'];
                }
            }

            $htmlContent = "
            <html>
            <head>
                <title>Official Legal Report</title>
                <style>
                    body { font-family: 'Times New Roman', Times, serif; line-height: 1.6; color: #000; padding: 30px; }
                    .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 20px; }
                    .title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px; }
                    .subtitle { font-size: 14px; color: #555; }
                    .section { margin-bottom: 30px; }
                    .section-title { font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 15px; text-transform: uppercase; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ccc; font-size: 14px; }
                    th { width: 40%; font-weight: bold; background-color: #f9f9f9; }
                    .total-row th, .total-row td { border-top: 2px solid #000; font-size: 16px; font-weight: bold; background-color: #fff; }
                    .footer { margin-top: 60px; border-top: 1px solid #000; padding-top: 15px; font-size: 12px; text-align: center; color: #555; }
                    p { font-size: 14px; text-align: justify; margin-bottom: 15px; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <div class='title'>Official Non-Payment Report</div>
                    <div class='title'>& Legal Complaint</div>
                    <div class='subtitle'>Date Issued: " . now()->format('F j, Y, g:i A') . "</div>
                </div>

                <div class='section'>
                    <div class='section-title'>I. Service Provider Information</div>
                    <table>
                        <tr><th>Provider Name</th><td>{$provider->first_name} {$provider->last_name}</td></tr>
                        <tr><th>Email Address</th><td>{$provider->email}</td></tr>
                    </table>
                </div>

                <div class='section'>
                    <div class='section-title'>II. Client Information</div>
                    <table>
                        <tr><th>Client Name</th><td>{$client->first_name} {$client->last_name}</td></tr>
                        <tr><th>Email Address</th><td>{$client->email}</td></tr>
                    </table>
                </div>

                <div class='section'>
                    <div class='section-title'>III. Service Details & Financials</div>
                    <table>
                        <tr><th>Service Title</th><td>{$serviceName}</td></tr>
                        <tr><th>Total Agreed Price</th><td>PHP " . number_format($term->deal->price, 2) . "</td></tr>
                        <tr><th>Total Paid via Transactions</th><td>PHP " . number_format($totalPaid, 2) . "</td></tr>
                        <tr class='total-row'><th>Outstanding Balance</th><td>PHP " . number_format($balance, 2) . "</td></tr>
                    </table>
                </div>

                <div class='section'>
                    <div class='section-title'>IV. Status Declaration</div>
                    <p>
                        This document serves to formally declare that the requested service, <strong>{$serviceName}</strong>, has been thoroughly and fully completed by the designated Service Provider. 
                    </p>
                    <p>
                        Despite making <strong>" . ($term->reminder_count ?? 3) . "</strong> official payment reminders via email communication, the Client has failed to settle the outstanding remaining balance of <strong>PHP " . number_format($balance, 2) . "</strong>.
                    </p>
                    <p>
                        Therefore, this document serves as an official and formal record of non-payment. This report may be utilized to initiate further legal, collections, or administrative actions against the Client as necessary to recover the owed compensation.
                    </p>
                </div>

                <div class='footer'>
                    This is a system-generated document.<br>
                    Generated electronically by the System Management on " . now()->format('Y-m-d H:i:s') . ".<br>
                    Reference ID: TERM-{$term->id}-" . time() . "
                </div>
            </body>
            </html>
            ";

            $fileName = 'legal_reports/report_term_' . $term->id . '_' . time() . '.pdf';
            
            $pdf = Pdf::loadHTML($htmlContent);
            $pdf->setPaper('A4', 'portrait');
            Storage::disk('public')->put($fileName, $pdf->output());

            $term->legal_report_path = $fileName;
            $term->save();

            $htmlContentMail = "
                <div style='font-family: sans-serif; line-height: 1.6; border: 1px solid #ef4444; padding: 20px; border-radius: 8px;'>
                    <h3 style='color: #dc2626; text-transform: uppercase;'>FINAL NOTICE: Legal Report Issued</h3>
                    <p>Hello {$client->first_name},</p>
                    <p>Despite 3 previous official reminders, your remaining balance of <strong>₱" . number_format($balance, 2) . "</strong> for the completed service <strong>{$serviceName}</strong> remains unpaid.</p>
                    <p><strong>A formal non-payment report has now been generated by the Service Provider.</strong></p>
                    <p>This report may be used for formal legal or administrative action. Please log in immediately and settle your account to resolve this issue.</p>
                </div>
            ";

            Mail::html($htmlContentMail, function ($message) use ($client, $serviceName) {
                $message->to($client->email)
                        ->subject("FINAL NOTICE: Legal Report Issued for Unpaid Service - {$serviceName}");
            });

            // Broadcast Event
            event(new ServiceRequestUpdated($term->client_id, $term->provider_id));

            return response()->json([
                'success' => true,
                'message' => 'Legal report generated and final notice sent to client.',
                'report_url' => url('/storage/' . $fileName)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate report.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Provider marks a work day for a Daily-priced job.
     * worked = false means the provider did NOT work that day, so the
     * client is not charged for it.
     */
    public function markWorkDay(Request $request, $id)
    {
        $request->validate([
            'work_date' => 'required|date_format:Y-m-d',
            'worked' => 'required|boolean',
            'bypass' => 'sometimes|boolean'
        ]);

        try {
            $providerId = Auth::id();
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'work_day');
            if ($denied) return $denied;

            if (!in_array($job->status, ['ongoing', 'completion_review'])) {
                return response()->json(['success' => false, 'message' => 'Work days can only be marked while the job is active.'], 400);
            }

            $deal = OfficialDeal::where('client_service_request_id', $job->id)->latest()->first();
            $start = $deal ? DailyBilling::billingStart($deal) : null;
            if (!$start) {
                return response()->json(['success' => false, 'message' => 'Daily billing has not started for this job.'], 400);
            }

            $workDate = Carbon::parse($request->work_date);
            // `bypass` is a presentation/demo aid: when set, the billing-window
            // date check is skipped so ANY date can be marked during a demo.
            if (!$request->boolean('bypass')) {
                if ($workDate->lt($start->copy()->startOfDay()) || $workDate->gt(Carbon::now())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Work day must be between ' . $start->copy()->startOfDay()->toDateString() . ' and today.'
                    ], 400);
                }
            }

            DailyWorkLog::updateOrCreate(
                ['client_service_request_id' => $job->id, 'work_date' => $request->work_date],
                ['worked' => (bool) $request->worked]
            );

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'work_day');

            return response()->json([
                'success' => true,
                'message' => $request->worked
                    ? 'Day marked as worked. The client is charged for this day.'
                    : 'Day marked as NOT worked. The client is not charged for this day.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update work day.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Provider records a batch of materials he/she bought with his/her own
     * money (item name, quantity, unit price each) plus an optional proof
     * photo. Status starts 'pending' until the client approves or rejects.
     */
    public function addMaterials(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        try {
            $job = $this->findGroupJob($id);
            $denied = $this->groupJobGuard($job, 'add_materials');
            if ($denied) return $denied;

            if (!in_array($job->status, ['ongoing', 'completion_review'])) {
                return response()->json(['success' => false, 'message' => 'Materials can only be added while the job is active.'], 400);
            }

            $proofPath = null;
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('service_materials/proofs', 'public');
            }

            $batch = MaterialExpenseRequest::create([
                'client_service_request_id' => $job->id,
                'provider_id' => Auth::id(),
                'proof_photo_path' => $proofPath,
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                $qty = (int) $item['quantity'];
                $unit = (float) $item['unit_price'];
                MaterialExpenseItem::create([
                    'material_expense_request_id' => $batch->id,
                    'item_name' => $item['item_name'],
                    'quantity' => $qty,
                    'unit_price' => round($unit, 2),
                    'total_price' => round($qty * $unit, 2)
                ]);
            }

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'add_materials');

            return response()->json([
                'success' => true,
                'message' => 'Materials added successfully. The client must approve them before they are billed.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add materials.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Provider deletes one of his/her own materials batches while it is still
     * 'pending' (e.g. a mistake was made). Already-processed batches cannot be
     * deleted.
     */
    public function deleteMaterials($batchId)
    {
        try {
            $batch = MaterialExpenseRequest::findOrFail($batchId);
            $job = $this->findGroupJob($batch->client_service_request_id);
            $denied = $this->groupJobGuard($job, 'delete_materials');
            if ($denied) return $denied;

            if ($batch->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only pending materials requests can be deleted.'], 400);
            }

            if ($batch->proof_photo_path) {
                Storage::disk('public')->delete($batch->proof_photo_path);
            }
            $batch->delete();

            // Broadcast Event
            event(new ServiceRequestUpdated($job->client_id, $job->provider_id));

            $this->groupJobRelease($job, 'delete_materials');

            return response()->json(['success' => true, 'message' => 'Materials request deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete materials.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Provider rejects a Client's on-hand proof of payment (e.g. the client
     * uploaded something but never actually paid). The pending transaction is
     * marked failed so the day is NOT counted as paid, and the client is
     * shown the rejection reason.
     */
    public function rejectProof(Request $request, $termId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $term = OfficialPaymentTerm::with('deal')->findOrFail($termId);

            $requestId = $term->deal?->client_service_request_id;
            if (!$requestId) {
                return response()->json(['success' => false, 'message' => 'This payment term is not linked to a job.'], 404);
            }

            // Group-aware ownership: the acting member must be able to work
            // this job (assigned directly to them OR an accepted member of
            // the job's group). A member other than the term creator can
            // still reject proof — that is the whole point of a team.
            $providerId = Auth::id();
            $groupIds = ProviderGroups::acceptedGroupIds($providerId);
            $canAct = ClientServiceRequest::where('id', (int) $requestId)
                ->where(function ($q) use ($providerId, $groupIds) {
                    $q->where('provider_id', $providerId);
                    if (count($groupIds) > 0) {
                        $q->orWhereIn('group_id', $groupIds);
                    }
                })
                ->exists();

            if (!$canAct) {
                return response()->json(['success' => false, 'message' => 'Payment term not found.'], 404);
            }

            $denied = $this->groupActionGuard(
                $term->deal?->group_id ? (int) $term->deal->group_id : null,
                'official_payment_term',
                (int) $term->id,
                'reject_proof'
            );
            if ($denied) return $denied;

            if ($term->status !== 'awaiting_proof_approval') {
                return response()->json(['success' => false, 'message' => 'There is no pending proof to reject.'], 400);
            }

            $transaction = ServicePaymentTransaction::where('payment_term_id', $term->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($transaction) {
                $transaction->status = 'failed';
                $transaction->save();
            }

            $term->status = 'agreed';
            $term->proof_rejection_reason = $request->reason;
            $term->save();

            ProviderGroups::releaseLock((int) Auth::id(), 'official_payment_term', (int) $term->id, 'reject_proof');

            // Broadcast Event
            event(new ServiceRequestUpdated($term->client_id, $term->provider_id));

            return response()->json([
                'success' => true,
                'message' => 'Proof rejected. The client has been notified and this payment was NOT counted.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reject proof.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Finds a job request the current provider may act on: either a request
     * assigned directly to them, or one belonging to a group they are an
     * ACCEPTED member of.
     */
    private function findGroupJob($id)
    {
        $providerId = Auth::id();
        $groupIds = ProviderGroups::acceptedGroupIds($providerId);

        // Group the ownership check so the `id = ?` from findOrFail binds to
        // the whole predicate. Without the closure, SQL precedence turns
        //   provider_id = ? OR group_id IN (...) AND id = ?
        // into
        //   provider_id = ? OR (group_id IN (...) AND id = ?)
        // and the first row returned is the provider's LOWEST-id job, so every
        // action would write to the wrong request.
        $query = ClientServiceRequest::where(function ($q) use ($providerId, $groupIds) {
            $q->where('provider_id', $providerId);
            if (count($groupIds) > 0) {
                $q->orWhereIn('group_id', $groupIds);
            }
        });

        return $query->findOrFail($id);
    }

    /**
     * One-at-a-time guard for group jobs. When a group member is already
     * performing this action (non-expired lock), the other members get a 409
     * and the action is blocked. Otherwise the current user's lock is taken.
     */
    private function groupActionGuard(?int $groupId, string $entityType, int $entityId, string $action)
    {
        if (!$groupId) return null; // individual jobs do not need group locks

        $lock = ProviderGroups::activeLock($entityType, $entityId, $action);
        if ($lock && (int) $lock->member_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => ProviderGroups::memberName((int) $lock->member_id) . ' is currently handling this action. Please wait a moment.',
            ], 409);
        }

        ProviderGroups::acquireLock((int) Auth::id(), $entityType, $entityId, $action, $groupId);
        return null;
    }

    private function groupJobGuard(ClientServiceRequest $job, string $action)
    {
        return $this->groupActionGuard($job->group_id ? (int) $job->group_id : null, 'client_service_request', (int) $job->id, $action);
    }

    private function groupJobRelease(ClientServiceRequest $job, string $action)
    {
        ProviderGroups::releaseLock((int) Auth::id(), 'client_service_request', (int) $job->id, $action);
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