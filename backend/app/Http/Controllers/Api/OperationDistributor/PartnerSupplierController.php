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
     * Display a listing of suppliers available for partnership.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Get permissions and check RBAC Read Access using 'ec_partner_supplier'
            $permissions = $this->getPermissions($user, 'ec_partner_supplier');
            
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
        // Hard backend RBAC check for creating requests
        if (!$this->checkRbacAccess($request->user(), 'ec_partner_supplier', 'can_create')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to request partnerships.'], 403);
        }

        $request->validate([
            'supplier_id' => 'required|exists:users,id',
            'message' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();

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