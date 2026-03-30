<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OperationalDistributor;
use App\Models\OperationDistributor\DistributorSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
     * Send a partnership request and generate formal terms document.
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

            // =================================================================
            // Generate Formal Terms and Conditions HTML Document
            // =================================================================
            $supplierUser = User::find($request->supplier_id);
            $distributorUser = User::find($distributorId);
            $date = now()->format('F d, Y');
            
            $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Supply Partnership Agreement</title>
                <style>
                    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 40px; }
                    h1 { color: #1e3a8a; text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 15px; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 1px;}
                    h2 { color: #2563eb; margin-top: 30px; font-size: 1.2rem; border-left: 4px solid #3b82f6; padding-left: 10px;}
                    .meta { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
                    .meta p { margin: 8px 0; font-size: 0.95rem; }
                    .footer { margin-top: 50px; font-size: 0.85rem; color: #64748b; text-align: center; border-top: 1px solid #cbd5e1; padding-top: 20px; }
                    .content { font-size: 0.95rem; text-align: justify; }
                </style>
            </head>
            <body>
                <h1>Supply Partnership Agreement</h1>
                <div class='meta'>
                    <p><strong>Effective Date:</strong> {$date}</p>
                    <p><strong>Distributor (Buyer):</strong> {$distributorUser->full_name}</p>
                    <p><strong>Supplier (Seller):</strong> {$supplierUser->full_name}</p>
                </div>
                
                <div class='content'>
                    <h2>1. Introduction and Scope</h2>
                    <p>This Supply Partnership Agreement (\"Agreement\") establishes the official business relationship between the Distributor and the Supplier. The Supplier agrees to provide products to the Distributor according to the terms defined herein and within the operations of the e-commerce supply chain platform.</p>

                    <h2>2. Orders and Fulfillment</h2>
                    <p>The Distributor shall place procurement orders securely through the platform. The Supplier commits to fulfilling these orders in a timely and professional manner, subject to stock availability and operational capacity. The Supplier reserves the right to reject or modify orders provided a valid operational reason is electronically communicated to the Distributor.</p>

                    <h2>3. Pricing, Invoicing, and Payments</h2>
                    <p>Product pricing is dynamically determined by the Supplier's active catalog at the time of order placement. Payment terms (e.g., GCash, Cash on Delivery, Bank Transfer) must be explicitly agreed upon per transaction or established as a default by the Supplier. The Distributor is legally obligated to remit full payment as dictated by the chosen payment method and schedule.</p>

                    <h2>4. Shipping, Delivery, and Risk of Loss</h2>
                    <p>For items shipped directly by the Supplier or its logistics personnel, the risk of loss, theft, or damage passes to the Distributor upon successful delivery and physical confirmation of receipt. The Distributor must conduct a preliminary inspection of all goods upon arrival.</p>

                    <h2>5. Returns, Warranties, and Defective Goods</h2>
                    <p>Any claims regarding defective, damaged, or incorrect products must be processed exclusively through the system's official Return Management module within the allowable timeframe. The Supplier will review and approve valid return requests, subsequently offering replacements or refunds as appropriate.</p>

                    <h2>6. Confidentiality and Data Protection</h2>
                    <p>Both parties agree to rigorously maintain the confidentiality of proprietary business information encountered during this partnership. This includes, but is not limited to, wholesale pricing models, trade secrets, customer demographics, sales volumes, and internal platform communications.</p>

                    <h2>7. Term, Suspension, and Termination</h2>
                    <p>This Agreement remains actively binding until officially terminated by either party. Termination can be executed through the platform's partnership management settings. All pending financial obligations, active orders, and outstanding return requests must be completely settled prior to the full severance of the partnership.</p>
                </div>

                <div class='footer'>
                    <p>Electronically generated and mutually agreed upon via the Platform on {$date}.</p>
                    <p>Digital Document Hash: " . Str::uuid() . "</p>
                    <p>This document is a legally binding system record.</p>
                </div>
            </body>
            </html>
            ";

            $fileName = 'agreements/supplier_partnerships/partnership_' . $partnership->id . '_' . time() . '.html';
            
            // Store the generated T&C HTML to public storage
            Storage::disk('public')->put($fileName, $htmlContent);

            // Conditionally save to database only if the column exists
            if (Schema::hasColumn('distributor_suppliers', 'agreement_path')) {
                $partnership->agreement_path = $fileName;
                $partnership->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Partnership request submitted successfully. Terms recorded.',
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