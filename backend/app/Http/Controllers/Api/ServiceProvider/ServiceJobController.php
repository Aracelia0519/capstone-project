<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm;
use App\Models\ServiceProvider\ServicePaymentTransaction;
use App\Models\ServiceProvider\ServiceJobCompletion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceJobController extends Controller
{
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

    public function createSurveyAgreement(Request $request, $id)
    {
        $request->validate([
            'provider_signature' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $providerId = Auth::id();
            $job = ClientServiceRequest::with(['client', 'serviceOffering'])->where('provider_id', $providerId)->findOrFail($id);

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
            $job = ClientServiceRequest::where('provider_id', $providerId)->findOrFail($id);

            DB::table('service_survey_agreements')
                ->where('client_service_request_id', $job->id)
                ->update([
                    'status' => 'in_progress',
                    'survey_started_at' => now()
                ]);

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
            $job = ClientServiceRequest::where('provider_id', $providerId)->findOrFail($id);

            DB::table('service_survey_agreements')
                ->where('client_service_request_id', $job->id)
                ->update([
                    'status' => 'completed',
                    'survey_completed_at' => now()
                ]);

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

    public function approvePaymentProof($termId)
    {
        try {
            $term = OfficialPaymentTerm::with('deal')->findOrFail($termId);
            $term->status = 'paid';
            $term->save();

            $transaction = ServicePaymentTransaction::where('payment_term_id', $term->id)
                ->where('status', 'pending')
                ->first();
                
            if ($transaction) {
                $transaction->status = 'completed';
                $transaction->save();
            }

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
            'proof_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        DB::beginTransaction();
        try {
            $providerId = Auth::id();
            $job = ClientServiceRequest::where('provider_id', $providerId)->findOrFail($id);

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

            return response()->json([
                'success' => true,
                'message' => 'Legal report generated and final notice sent to client.',
                'report_url' => url('/storage/' . $fileName)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to generate report.', 'error' => $e->getMessage()], 500);
        }
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