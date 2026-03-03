<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceJobController extends Controller
{
    /**
     * Fetch all job requests for the authenticated Service Provider
     */
    public function index()
    {
        try {
            $providerId = Auth::id(); // Get the logged-in Service Provider's ID

            // Fetch requests with client and service offering relationships
            $jobRequests = ClientServiceRequest::with(['client', 'serviceOffering'])
                ->where('provider_id', $providerId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobRequests
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
}