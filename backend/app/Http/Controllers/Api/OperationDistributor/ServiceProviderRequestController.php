<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\ServiceProviderDistributor;
use App\Models\OperationDistributor\ServiceProviderAgreement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceProviderRequestController extends Controller
{
    /**
     * Retrieves the specific permissions for a user on a given module.
     */
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'can_view' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false
        ];

        // Main distributors and head operational distributors automatically have full access
        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
            ];
        }

        // Check RBAC for standard employees
        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id)
                ->first();
            if (!$position) return $defaults;

            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey)
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_create' => (bool) $access->can_create,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete,
                ];
            }
        }

        return $defaults;
    }

    private function checkRbacAccess($user, $permissionKey, $action)
    {
        $permissions = $this->getPermissions($user, $permissionKey);
        return $permissions[$action] ?? false;
    }

    /**
     * Fetch all service provider requests for the current distributor
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Get permissions and check RBAC Read Access using 'ec_service_provider'
            $permissions = $this->getPermissions($user, 'ec_service_provider');
            
            if (!$permissions['can_view']) {
                return response()->json(['message' => 'Access Denied: You do not have permission to view service provider requests.'], 403);
            }

            // Logic to get Distributor ID based on the user role
            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            if (!$distributorId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access. Distributor context not found.'], 403);
            }

            $requests = ServiceProviderDistributor::with(['serviceProvider.serviceProviderRequirement'])
                ->where('distributor_id', $distributorId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $sp = $req->serviceProvider;
                    $spReq = $sp ? $sp->serviceProviderRequirement : null;
                    
                    $addressStr = 'Address not provided';
                    $location = 'Location not provided';
                    
                    if ($spReq) {
                        $addr = DB::table('service_provider_addresses')->where('service_provider_requirements_id', $spReq->id)->first();
                        if ($addr) {
                            $location = "{$addr->city}, {$addr->province}";
                            $addressStr = "{$addr->block_address}, {$addr->barangay}, {$addr->city}, {$addr->province}";
                        }
                    }

                    return [
                        'id' => $req->id,
                        'provider_name' => $sp ? $sp->full_name : 'Unknown',
                        'email' => $sp ? $sp->email : 'Unknown',
                        'phone' => $sp ? $sp->phone : 'Unknown',
                        'location' => $location,
                        'full_address' => $addressStr,
                        'date_requested' => $req->created_at->format('Y-m-d'),
                        'message' => $req->request_message,
                        'status' => $req->status,
                    ];
                });

            return response()->json([
                'success' => true, 
                'data' => $requests,
                'permissions' => $permissions
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requests', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve partnership request and generate an agreement document
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Hard backend RBAC check for updating
            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_update')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to approve partnership requests.'], 403);
            }

            // Logic to get Distributor ID based on the user role
            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            $req = ServiceProviderDistributor::with('serviceProvider')->where('id', $id)
                    ->where('distributor_id', $distributorId)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found or unauthorized.'], 404);
            }

            // Update Status
            $req->update([
                'status' => 'active',
                'approved_at' => Carbon::now()
            ]);

            // Get Names instead of IDs
            $authorizedName = $user->full_name ?? 'Authorized Representative';
            $spName = $req->serviceProvider ? $req->serviceProvider->full_name : 'Unknown Service Provider';

            // --- Generate Formal Agreement Document ---
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; border: 1px solid #ddd;'>
                    <h1 style='text-align: center; color: #333;'>Official Partnership Agreement</h1>
                    <p><strong>Effective Date:</strong> " . Carbon::now()->toFormattedDateString() . "</p>
                    <hr/>
                    <h3>Parties Involved</h3>
                    <ul>
                        <li><strong>Authorized Representative:</strong> {$authorizedName}</li>
                        <li><strong>Service Provider:</strong> {$spName}</li>
                    </ul>
                    <h3>Terms of Agreement</h3>
                    <p>This document certifies that the aforementioned Service Provider is officially authorized to procure materials, access wholesale catalog pricing, and submit platform requests to the Distributor. Both parties are bound to platform compliance and confidentiality standards.</p>
                    <br/><br/>
                    <p><em>Electronically Signed and Verified by System.</em></p>
                </div>
            ";

            // Save Document to Storage
            $fileName = 'agreements/sp_dist_agreement_' . $req->id . '_' . time() . '.html';
            Storage::disk('public')->put($fileName, $htmlContent);

            // Record Agreement to Database
            ServiceProviderAgreement::create([
                'service_provider_distributor_id' => $req->id,
                'document_path' => $fileName,
                'generated_at' => Carbon::now()
            ]);

            return response()->json(['success' => true, 'message' => 'Partnership Approved and Agreement Document generated.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to approve request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Decline partnership request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Hard backend RBAC check for updating/rejecting
            if (!$this->checkRbacAccess($user, 'ec_service_provider', 'can_update')) {
                return response()->json(['message' => 'Access Denied: You do not have permission to reject partnership requests.'], 403);
            }

            $request->validate(['reason' => 'required|string|max:1000']);

            // Logic to get Distributor ID based on the user role
            $distributorId = null;
            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                $distributorId = $opDist ? $opDist->parent_distributor_id : null;
            } elseif ($user->role === 'employee') {
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                $distributorId = $employee ? $employee->parent_distributor_id : null;
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            $req = ServiceProviderDistributor::where('id', $id)
                    ->where('distributor_id', $distributorId)
                    ->first();

            if (!$req) {
                return response()->json(['success' => false, 'message' => 'Partnership request not found or unauthorized.'], 404);
            }

            $req->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);

            return response()->json(['success' => true, 'message' => 'Partnership request declined.'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to decline request.', 'error' => $e->getMessage()], 500);
        }
    }
}