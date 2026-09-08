<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Added for the OCR API request
use App\Events\PwdApplicationSubmitted; // Import event

class PwdApplicationController extends Controller
{
    public function show(Request $request)
    {
        $application = DB::table('pwd_applications')
            ->where('user_id', Auth::id())
            ->first();

        if (!$application) {
            return response()->json(['status' => 'unsubmitted']);
        }

        return response()->json([
            'status' => $application->status,
            'data' => $application
        ]);
    }

    public function store(Request $request)
    {
        $existing = DB::table('pwd_applications')
            ->where('user_id', Auth::id())
            ->first();

        if ($existing && in_array($existing->status, ['pending', 'verified'])) {
            return response()->json(['message' => 'Application already exists and is under review or verified.'], 400);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'id_number' => 'required|string|max:255',
            'disability_type' => 'required|string|max:255',
            'issuing_lgu' => 'required|string|max:255',
            'expiration_date' => 'required|date',
            'photo_front' => $existing ? 'nullable|image|mimes:jpeg,png,jpg|max:5120' : 'required|image|mimes:jpeg,png,jpg|max:5120',
            'photo_back' => $existing ? 'nullable|image|mimes:jpeg,png,jpg|max:5120' : 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Run the OCR API Checker
        $autoCheckResult = $this->analyzePwdCardWithAI($request);
        
        $finalStatus = $autoCheckResult['is_valid'] ? 'verified' : 'rejected';
        $finalRejectionReason = $autoCheckResult['is_valid'] ? null : $autoCheckResult['reason'];

        $updateData = [
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'id_number' => $request->id_number,
            'disability_type' => $request->disability_type,
            'issuing_lgu' => $request->issuing_lgu,
            'expiration_date' => $request->expiration_date,
            'status' => $finalStatus,
            'rejection_reason' => $finalRejectionReason, 
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo_front')) {
            $updateData['photo_front_path'] = $request->file('photo_front')->store('pwd_ids', 'public');
        }
        if ($request->hasFile('photo_back')) {
            $updateData['photo_back_path'] = $request->file('photo_back')->store('pwd_ids', 'public');
        }

        DB::table('pwd_applications')->updateOrInsert(
            ['user_id' => Auth::id()],
            $updateData
        );

        // Fire the real-time event for the admin dashboard
        event(new PwdApplicationSubmitted(Auth::id(), $request->full_name));

        return response()->json([
            'status' => 'success',
            'message' => 'PWD Application processed successfully',
            'application_status' => $finalStatus,
            'rejection_reason' => $finalRejectionReason
        ]);
    }

    /**
     * Integrates OCR.space API to extract text and validate user inputs against the physical card.
     */
    /**
     * Integrates OCR.space API to extract text and validate user inputs against the physical card.
     */
    private function analyzePwdCardWithAI(Request $request)
    {
        $isValid = true;
        $reasons = [];

        // Rule 1: Expiration Date Check (Cannot be in the past)
        if (strtotime($request->expiration_date) < strtotime('today')) {
            $isValid = false;
            $reasons[] = "The PWD ID is already expired.";
        }

        // Rule 2: OCR Extraction & String Matching
        if ($request->hasFile('photo_front')) {
            try {
                $imagePath = $request->file('photo_front')->getPathname();
                
                // Send the image to the OCR.space API
                $response = Http::attach(
                    'file', file_get_contents($imagePath), 'id_front.jpg'
                )->post('https://api.ocr.space/parse/image', [
                    'apikey' => 'helloworld', // Replace with your actual OCR.space API key
                    'language' => 'eng',
                    'detectOrientation' => 'true',
                    'scale' => 'true', 
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    
                    if (isset($json['IsErroredOnProcessing']) && $json['IsErroredOnProcessing'] == true) {
                         $isValid = false;
                         $reasons[] = "The image could not be processed by our automated system.";
                         return ['is_valid' => $isValid, 'reason' => implode(" ", $reasons)];
                    }

                    $parsedText = $json['ParsedResults'][0]['ParsedText'] ?? '';
                    
                    // Normalize text: lowercase and strip all spaces/special characters
                    $cleanExtractedText = strtolower(preg_replace('/[^a-z0-9]/i', '', $parsedText));
                    $cleanIdNumber = strtolower(preg_replace('/[^a-z0-9]/i', '', $request->id_number));
                    
                    // Split the user's input name to search for parts of it
                    $nameParts = explode(' ', strtolower($request->full_name));
                    $nameMatchCount = 0;

                    foreach ($nameParts as $part) {
                        $cleanPart = preg_replace('/[^a-z0-9]/i', '', $part);
                        if (strlen($cleanPart) > 2 && strpos($cleanExtractedText, $cleanPart) !== false) {
                            $nameMatchCount++;
                        }
                    }

                    // Strict ID Match: Ensures the ID found is NOT surrounded by other digits
                    // (?<!\d) means not preceded by a digit. (?!\d) means not followed by a digit.
                    if (!preg_match('/(?<!\d)' . preg_quote($cleanIdNumber, '/') . '(?!\d)/', $cleanExtractedText)) {
                        $isValid = false;
                        $reasons[] = "The exact ID number '{$request->id_number}' was not found or is incomplete on the uploaded card.";
                    }

                    // Check if at least one distinct part of their name was detected
                    if ($nameMatchCount === 0 && !empty($cleanExtractedText)) {
                        $isValid = false;
                        $reasons[] = "We could not verify your name '{$request->full_name}' on the uploaded ID.";
                    }

                    // Failsafe for an image that contains absolutely no readable text
                    if (empty($cleanExtractedText)) {
                        $isValid = false;
                        $reasons[] = "No readable text was found. Please upload a clearer image without camera glare.";
                    }

                } else {
                    $isValid = false;
                    $reasons[] = "Verification service is currently unreachable.";
                }
            } catch (\Exception $e) {
                $isValid = false;
                $reasons[] = "An error occurred during image processing.";
            }
        } else {
             $isValid = false;
             $reasons[] = "A front photo of the ID is required for verification.";
        }

        return [
            'is_valid' => $isValid,
            'reason' => implode(" ", $reasons)
        ];
    }
}