<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use Illuminate\Support\Facades\Auth;

class ClientServiceRequestController extends Controller
{
    public function index()
    {
        $clientId = Auth::id();

        // Fetch requests associated with the logged-in client, eager load the related tables
        $requests = ClientServiceRequest::with(['serviceOffering', 'provider'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }
}