<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OperationalDistributor;
use App\Models\OperationDistributor\DistributorSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerSupplierController extends Controller
{
    /**
     * Fetch RBAC permissions for the logged-in user (Level-Based).
     */
    private function getPermissions($user)
    {
        $defaultPermissions = [
            'can_view' => true,
            'can_manage' => true,
            'can_approve' => true
        ];

        // Non-employees bypass this specific RBAC check and get full access
        if ($user->role !== 'employee') {
            return $defaultPermissions;
        }

        $noAccess = [
            'can_view' => false,
            'can_manage' => false,
            'can_approve' => false
        ];

        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if (!$employee) return $noAccess;

        $position = DB::table('positions')
            ->where('title', $employee->position)
            ->where('distributor_id', $employee->parent_distributor_id)
            ->first();

        if (!$position) return $noAccess;

        // Fetch using the newly updated permission format with the 'ec_partner_supplier' key
        $access = DB::table('position_accessibilities')
            ->where('position_id', $position->id)
            ->where('permission_key', 'ec_partner_supplier')
            ->first();

        if (!$access || !$access->is_granted) return $noAccess;

        return [
            'can_view' => (bool)$access->can_view,
            'can_manage' => (bool)$access->can_manage,
            'can_approve' => (bool)$access->can_approve,
        ];
    }

    /**
     * Display a listing of suppliers available for partnership.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Get permissions and check Level-Based RBAC Read Access
            $permissions = $this->getPermissions($user);
            
            if (!$permissions['can_view']) {
                return response()->json(['message' => 'Access Denied: You do not have permission to view partner suppliers.'], 403);
            }

            // Determine the Parent Distributor ID
            $distributorId = null;

            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                if ($opDist) {
                    $distributorId = $opDist->parent_distributor_id;
                }
            } elseif ($user->role === 'employee') {
                // Logic for Employee
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                if ($employee) {
                    $distributorId = $employee->parent_distributor_id;
                }
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            if (!$distributorId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access or Distributor not found'], 403);
            }

            // Fetch Users with role 'supplier' and their details
            $suppliers = User::where('role', 'supplier')
                ->where('status', 'active') // Only fetch active suppliers
                ->with(['supplierRequirements' => function($q) {
                    $q->select('id', 'user_id', 'company_name', 'valid_id_photo', 'business_registration_number');
                }, 'supplierRequirements.address']) 
                ->get()
                ->map(function ($supplier) use ($distributorId) {
                    // Check if a partnership exists
                    $partnership = DistributorSupplier::where('distributor_id', $distributorId)
                        ->where('supplier_id', $supplier->id)
                        ->first();

                    // Determine Frontend Status
                    $status = 'available';
                    if ($partnership) {
                        if ($partnership->status === 'active') {
                            $status = 'connected';
                        } elseif (in_array($partnership->status, ['pending_internal', 'pending_supplier'])) {
                            $status = 'pending';
                        } elseif ($partnership->status === 'rejected') {
                            $status = 'rejected'; 
                        }
                    }

                    // Extract Location safely
                    $location = 'N/A';
                    $req = $supplier->supplierRequirements;
                    
                    if ($req && $req->address) {
                        $addr = $req->address;
                        $location = "{$addr->city}, {$addr->province}";
                    }

                    // Safe access to requirement properties
                    $companyName = $req ? $req->company_name : $supplier->full_name;
                    $idPhoto = ($req && $req->valid_id_photo) ? $req->valid_id_photo : null;
                    $regNumber = ($req && $req->business_registration_number) ? $req->business_registration_number : 'N/A';

                    // Prepare Response Object
                    return [
                        'id' => $supplier->id,
                        'name' => $companyName ?? $supplier->full_name,
                        'contact_person' => $supplier->full_name,
                        'email' => $supplier->email,
                        'logo' => $idPhoto 
                            ? url('storage/' . $idPhoto) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($supplier->full_name),
                        'category' => 'General Supply', // Placeholder as not in DB
                        'rating' => 5.0, // Placeholder
                        'location' => $location,
                        'description' => "Verified Supplier. ID: " . $regNumber,
                        'min_order' => 0, // Placeholder
                        'fulfillment_rate' => 100, // Placeholder
                        'status' => $status,
                        'partnership_details' => $partnership
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'permissions' => $permissions // Send permissions back to frontend
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to fetch suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a partnership request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Level-Based backend RBAC check for creating requests (maps to 'manage')
        $permissions = $this->getPermissions($user);
        if (!$permissions['can_manage']) {
            return response()->json(['message' => 'Access Denied: You do not have permission to request partnerships.'], 403);
        }

        $request->validate([
            'supplier_id' => 'required|exists:users,id',
            'message' => 'nullable|string'
        ]);

        try {
            // Logic to get Distributor ID (Parent)
            $distributorId = null;

            if ($user->role === 'operational_distributor') {
                $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
                if ($opDist) {
                    $distributorId = $opDist->parent_distributor_id;
                }
            } elseif ($user->role === 'employee') {
                // Logic for Employee
                $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
                if ($employee) {
                    $distributorId = $employee->parent_distributor_id;
                }
            } elseif ($user->role === 'distributor') {
                $distributorId = $user->id;
            }

            if (!$distributorId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized or Distributor not found'], 403);
            }

            // Check if already exists
            $existing = DistributorSupplier::where('distributor_id', $distributorId)
                ->where('supplier_id', $request->supplier_id)
                ->first();

            if ($existing && $existing->status !== 'rejected') {
                return response()->json(['success' => false, 'message' => 'Request already exists or active.'], 400);
            }

            // Determine initial status
            // If created by Op Distributor OR Employee -> pending_internal (needs Owner approval)
            // If created by Distributor -> pending_supplier (goes directly to supplier)
            $initialStatus = in_array($user->role, ['operational_distributor', 'employee']) 
                ? 'pending_internal' 
                : 'pending_supplier';

            $partnership = DistributorSupplier::create([
                'distributor_id' => $distributorId,
                'supplier_id' => $request->supplier_id,
                'created_by' => $user->id,
                'status' => $initialStatus,
                'request_message' => $request->message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Partnership request submitted successfully.',
                'data' => $partnership
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending request: ' . $e->getMessage()
            ], 500);
        }
    }
}