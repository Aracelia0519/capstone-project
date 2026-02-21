<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use App\Models\OperationDistributor\ServiceProviderAgreement; // ADDED IMPORT
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceProviderDistributorController extends Controller
{
    /**
     * Fetch all verified distributors and their partnership status with the logged-in service provider.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $distributors = User::where('role', 'distributor')
                ->whereHas('distributorRequirement', function ($query) {
                    $query->where('status', 'approved');
                })
                ->with(['distributorRequirement.address'])
                ->get()
                ->map(function ($distributor) use ($user) {
                    
                    // Check if there's an existing partnership or request
                    $partnership = ServiceProviderDistributor::where('service_provider_id', $user->id)
                        ->where('distributor_id', $distributor->id)
                        ->first();

                    // Get specialties from their actual active products
                    $specialties = DB::table('distributor_products')
                        ->where('distributor_id', $distributor->id)
                        ->where('is_active', 1)
                        ->distinct()
                        ->pluck('category')
                        ->toArray();

                    if (empty($specialties)) {
                        $specialties = ['General Materials'];
                    }

                    // Format Address
                    $address = 'Location not provided';
                    if ($distributor->distributorRequirement && $distributor->distributorRequirement->address) {
                        $addr = $distributor->distributorRequirement->address;
                        $address = "{$addr->city}, {$addr->province}";
                    }

                    // NEW: Fetch Formal Agreement Paper if active
                    $agreementUrl = null;
                    if ($partnership && $partnership->status === 'active') {
                        $agreement = ServiceProviderAgreement::where('service_provider_distributor_id', $partnership->id)
                            ->latest()
                            ->first();
                        $agreementUrl = $agreement ? asset('storage/' . $agreement->document_path) : null;
                    }

                    return [
                        'id' => $distributor->id,
                        'name' => $distributor->distributorRequirement->company_name ?? $distributor->full_name,
                        'location' => $address,
                        'specialties' => $specialties,
                        'rating' => 4.5, // You can make this dynamic if you have a ratings table later
                        'status' => $partnership ? $partnership->status : 'none',
                        'agreement_url' => $agreementUrl, // ADDED URL
                        'logoUrl' => null // Update if you store a specific business logo
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $distributors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch distributors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a partnership request to a distributor.
     */
    public function requestPartnership(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'distributor_id' => 'required|exists:users,id',
                'request_message' => 'nullable|string|max:1000',
            ]);

            $user = Auth::user();

            // Check if request already exists
            $existing = ServiceProviderDistributor::where('service_provider_id', $user->id)
                ->where('distributor_id', $request->distributor_id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'A partnership request already exists with this distributor.'
                ], 400);
            }

            // Create new request
            $partnership = ServiceProviderDistributor::create([
                'service_provider_id' => $user->id,
                'distributor_id' => $request->distributor_id,
                'status' => 'pending',
                'request_message' => $request->request_message,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Partnership request sent successfully!',
                'data' => $partnership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}