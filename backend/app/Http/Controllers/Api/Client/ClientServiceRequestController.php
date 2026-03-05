<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use Illuminate\Support\Facades\Auth;

class ClientServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $clientId = Auth::id();

        // Dynamically get the base URL of the device/domain making the request
        // e.g., http://192.168.1.x:8000 (mobile) or https://yourdomain.com (live)
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');

        // Fetch requests associated with the logged-in client, eager load the related tables
        $serviceRequests = ClientServiceRequest::with(['serviceOffering', 'provider'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Format the image paths dynamically before sending to Vue
        $formattedRequests = $serviceRequests->map(function ($req) use ($baseUrl) {
            $service = $req->serviceOffering;
            
            if ($service && !empty($service->image_paths)) {
                $formattedPaths = array_map(function ($path) use ($baseUrl) {
                    // 1. Remove old hardcoded localhost URLs if they exist in the DB
                    if (str_starts_with($path, 'http')) {
                        $parsedUrl = parse_url($path);
                        $path = $parsedUrl['path'] ?? $path; // Extracts just the path part
                    }
                    
                    // 2. Strip '/storage/' to get the raw relative path
                    $cleanPath = preg_replace('/^\/?storage\//', '', $path);
                    $cleanPath = ltrim($cleanPath, '/');
                    
                    // 3. Attach the perfect dynamic base URL for the current device
                    return $baseUrl . '/storage/' . $cleanPath;
                }, $service->image_paths);

                $service->image_paths = $formattedPaths;
            }
            
            return $req;
        });

        return response()->json([
            'success' => true,
            'data' => $formattedRequests
        ]);
    }
}