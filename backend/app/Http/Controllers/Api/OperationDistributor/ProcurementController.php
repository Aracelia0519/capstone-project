<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Distributor\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier\SupplierPartner;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\OperationalDistributor; 
use App\Models\Supplier\SupplierRequirements;
use App\Models\Distributor\DistributorAddress; 
use App\Models\Supplier\SupplierRawMaterial;
use App\Models\HR\Employee;
use App\Models\Distributor\HRManager;
use App\Models\Supplier\ProcurementFulfillment;

class ProcurementController extends Controller
{
    /**
     * Check RBAC Permissions for Procurement Module
     */
    private function checkAccess($user, $action = 'can_view')
    {
        // Admin
        if ($user->role === 'admin') {
            return [
                'has_access' => true,
                'distributor_id' => null,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // Distributor
        if ($user->role === 'distributor') {
            return [
                'has_access' => true,
                'distributor_id' => $user->id,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        }

        // Managers and Operational Distributors
        $distributorId = $this->getDistributorId($user);
        if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
            return [
                'has_access' => true,
                'distributor_id' => $distributorId,
                'permissions' => ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
            ];
        } 
        
        // Employee with specific RBAC
        elseif ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $position = DB::table('positions')
                    ->where('distributor_id', $employee->parent_distributor_id)
                    ->where('title', $employee->position)
                    ->first();
                
                if ($position) {
                    $access = DB::table('position_accessibilities')
                        ->where('position_id', $position->id)
                        ->where('permission_key', 'ec_procurement') // Permission key for this module
                        ->first();
                        
                    if ($access) {
                        $hasAccess = false;
                        if ($action === 'can_view' && $access->can_view) $hasAccess = true;
                        if ($action === 'can_create' && $access->can_create) $hasAccess = true;
                        if ($action === 'can_update' && $access->can_update) $hasAccess = true;
                        if ($action === 'can_delete' && $access->can_delete) $hasAccess = true;
                        
                        if ($hasAccess) {
                            return [
                                'has_access' => true,
                                'distributor_id' => $employee->parent_distributor_id,
                                'permissions' => [
                                    'can_view' => (bool)$access->can_view,
                                    'can_create' => (bool)$access->can_create,
                                    'can_update' => (bool)$access->can_update,
                                    'can_delete' => (bool)$access->can_delete,
                                ]
                            ];
                        }
                    }
                }
            }
        }
        
        return [
            'has_access' => false,
            'distributor_id' => null,
            'permissions' => ['can_view' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false]
        ];
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view procurement requests'
                ], 403);
            }

            $query = ProcurementRequest::query();
            
            if ($user->role !== 'admin') {
                $query->where('distributor_id', $accessData['distributor_id']);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('request_code', 'like', "%{$search}%")
                      ->orWhere('product_name', 'like', "%{$search}%")
                      ->orWhere('supplier', 'like', "%{$search}%");
                });
            }
            
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $perPage = $request->get('per_page', 15);
            $requests = $query->with(['requester', 'distributor', 'selectedSupplier'])->paginate($perPage);

            // Fetch and append Supplier Fulfillment (Receipts/Proofs) safely
            $fulfillments = ProcurementFulfillment::whereIn('procurement_request_id', $requests->pluck('id'))->get()->keyBy('procurement_request_id');
            $requests->getCollection()->transform(function ($req) use ($fulfillments) {
                $req->fulfillment = $fulfillments->get($req->id);
                return $req;
            });
            
            return response()->json([
                'success' => true,
                'data' => $requests,
                'permissions' => $accessData['permissions'],
                'message' => 'Procurement requests retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve procurement requests: ' . $e->getMessage()
            ], 500);
        }
    }

    public function supplierProducts(Request $request, $supplierId)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $products = SupplierRawMaterial::where('user_id', $supplierId)
                ->where('is_active', true)
                ->get()
                ->map(function ($product) {
                    if ($product->image_url && !str_starts_with($product->image_url, 'http')) {
                        $product->image_url = asset('storage/' . ltrim($product->image_url, '/'));
                    }
                    return $product;
                });

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Supplier products retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier products: ' . $e->getMessage()
            ], 500);
        }
    }

    public function formOptions(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_create');
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $distributorId = $accessData['distributor_id'];

            // Fetch Current Business Budget
            $salesRecord = DB::table('distributor_overall_sales')
                ->where('distributor_id', $distributorId)
                ->first();
            $availableBudget = $salesRecord ? (float) ($salesRecord->total_revenue ?? 0) : 0;

            $suppliers = SupplierPartner::where('distributor_id', $distributorId)
                ->where('status', 'active')
                ->with('supplier')
                ->get()
                ->map(function($partner) {
                    if (!$partner->supplier) return null;
                    $supplierName = $partner->supplier->full_name ?? ($partner->supplier->first_name . ' ' . $partner->supplier->last_name);
                    
                    $companyName = null;
                    try {
                        $companyName = SupplierRequirements::where('user_id', $partner->supplier_id)->value('company_name');
                    } catch (\Exception $e) {}
                    
                    $paymentSettings = DB::table('supplier_payment_settings')->where('supplier_id', $partner->supplier_id)->first();

                    return [
                        'id' => $partner->supplier_id,
                        'name' => $companyName ? $companyName : $supplierName,
                        'value' => $companyName ? $companyName : $supplierName,
                        'payment_settings' => $paymentSettings ? [
                            'is_cod_enabled' => (bool)$paymentSettings->is_cod_enabled,
                            'is_gcash_enabled' => (bool)$paymentSettings->is_gcash_enabled,
                        ] : [
                            'is_cod_enabled' => true, 
                            'is_gcash_enabled' => false
                        ]
                    ];
                })
                ->filter()
                ->values(); 

            $addresses = [];
            $distributorReq = DistributorRequirements::where('user_id', $distributorId)->first();
            if ($distributorReq) {
                $addresses = DistributorAddress::where('distributor_requirements_id', $distributorReq->id)->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suppliers' => $suppliers,
                    'addresses' => $addresses,
                    'available_budget' => $availableBudget // Sent to frontend for hidden real-time validation
                ],
                'message' => 'Form options retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form options: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_create');
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to create requests'], 403);
            }

            $distributorId = $accessData['distributor_id'];
            
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:users,id',
                'supplier' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:supplier_raw_materials,id',
                'items.*.quantity' => 'required|integer|min:1',
                'priority' => 'required|in:low,medium,high',
                'delivery_address' => 'required|string',
                'shipping_method' => 'nullable|string|in:standard,express,pickup',
                'payment_terms' => 'nullable|string|in:net30,net60,cod,advance,gcash',
                'instructions' => 'nullable|string',
                'required_by_date' => 'nullable|date|after:today'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $totalRequestedCost = 0;

            foreach ($request->items as $item) {
                $material = SupplierRawMaterial::findOrFail($item['id']);
                $minOrder = $material->min_order ?? 1;
                
                if ($item['quantity'] < $minOrder) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['items' => ["Quantity for {$material->name} must be at least {$minOrder}."]]
                    ], 422);
                }

                if (!is_null($material->max_order) && $item['quantity'] > $material->max_order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['items' => ["Quantity for {$material->name} cannot exceed {$material->max_order}."]]
                    ], 422);
                }

                $totalRequestedCost += ($material->price * $item['quantity']);
            }

            // ==========================================
            // STRICT BUDGET VERIFICATION ON BACKEND
            // ==========================================
            $salesRecord = DB::table('distributor_overall_sales')
                ->where('distributor_id', $distributorId)
                ->first();
            
            $availableBudget = $salesRecord ? ($salesRecord->total_revenue ?? 0) : 0;

            if ($totalRequestedCost > $availableBudget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient budget to complete this request. The total amount exceeds the allocated business funds.'
                ], 400); 
            }
            // ==========================================

            DB::beginTransaction();
            $createdRequests = [];

            foreach ($request->items as $item) {
                $material = SupplierRawMaterial::findOrFail($item['id']);
                $totalCost = $material->price * $item['quantity'];
                
                $procurementRequest = ProcurementRequest::create([
                    'requester_id' => $user->id,
                    'distributor_id' => $distributorId,
                    'supplier_id' => $request->supplier_id,
                    'product_id' => null, 
                    'request_code' => ProcurementRequest::generateRequestCode(),
                    'product_name' => $material->name,
                    'category' => $material->category,
                    'supplier' => $request->supplier,
                    'quantity' => $item['quantity'],
                    'unit_price' => $material->price,
                    'total_cost' => $totalCost,
                    'priority' => $request->priority,
                    'status' => 'pending',
                    'shipping_method' => $request->shipping_method ?? 'standard',
                    'payment_terms' => $request->payment_terms ?? 'cod',
                    'delivery_address' => $request->delivery_address,
                    'instructions' => $request->instructions,
                    'required_by_date' => $request->required_by_date,
                    'request_date' => now()->toDateString()
                ]);

                $createdRequests[] = $procurementRequest;
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $createdRequests,
                'message' => count($createdRequests) . ' Procurement requests generated successfully'
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $user = Auth::user();
            $request = ProcurementRequest::with(['requester', 'distributor', 'selectedSupplier'])->findOrFail($id);
            
            $accessData = $this->checkAccess($user, 'can_view');
            
            if (!$accessData['has_access'] || ($user->role !== 'admin' && $request->distributor_id !== $accessData['distributor_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this request'
                ], 403);
            }

            $request->fulfillment = ProcurementFulfillment::where('procurement_request_id', $request->id)->first();
            
            return response()->json([
                'success' => true,
                'data' => $request,
                'message' => 'Procurement request retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $procurementRequest = ProcurementRequest::findOrFail($id);
            
            $accessData = $this->checkAccess($user, 'can_update');
            if (!$accessData['has_access'] || ($user->role !== 'admin' && $procurementRequest->distributor_id !== $accessData['distributor_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this request'
                ], 403);
            }
            
            $updatableFields = [];
            
            if ($procurementRequest->status === 'pending') {
                $updatableFields = ['delivery_address', 'instructions', 'required_by_date', 'shipping_method', 'payment_terms'];
            }
            
            if ($request->has('status')) {
                $procurementRequest->updateStatus($request->status, $request->rejection_reason);
            }
            
            foreach ($updatableFields as $field) {
                if ($request->has($field)) {
                    $procurementRequest->$field = $request->$field;
                }
            }
            
            $procurementRequest->save();
            
            return response()->json([
                'success' => true,
                'data' => $procurementRequest->fresh(['requester', 'distributor']),
                'message' => 'Procurement request updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function cancel(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $procurementRequest = ProcurementRequest::findOrFail($id);
            
            $accessData = $this->checkAccess($user, 'can_update');
            if (!$accessData['has_access'] || ($user->role !== 'admin' && $procurementRequest->distributor_id !== $accessData['distributor_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to cancel this request'
                ], 403);
            }
            
            if (!in_array($procurementRequest->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request cannot be cancelled in its current status'
                ], 400);
            }
            
            $procurementRequest->updateStatus('cancelled');
            
            return response()->json([
                'success' => true,
                'message' => 'Procurement request cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();
            $accessData = $this->checkAccess($user, 'can_view');
            if (!$accessData['has_access']) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $query = ProcurementRequest::query();

            if ($user->role !== 'admin') {
                $query->where('distributor_id', $accessData['distributor_id']);
            }
            
            if ($request->filled('period')) {
                $period = $request->period;
                $now = now();
                switch ($period) {
                    case 'today':
                        $query->whereDate('created_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                        break;
                    case 'year':
                        $query->whereBetween('created_at', [$now->startOfYear(), $now->endOfYear()]);
                        break;
                }
            }
            
            $totalRequests = $query->count();
            $totalCost = $query->sum('total_cost');
            
            $statusCounts = $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status');
            
            $priorityCounts = $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority');
            
            $recentRequests = ProcurementRequest::query()
                ->when($user->role !== 'admin', function($q) use ($accessData) {
                    $q->where('distributor_id', $accessData['distributor_id']);
                })
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_requests' => $totalRequests,
                    'total_cost' => $totalCost,
                    'status_counts' => $statusCounts,
                    'priority_counts' => $priorityCounts,
                    'recent_requests' => $recentRequests
                ],
                'message' => 'Statistics retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDistributorId($user) {
        if ($user->role === 'distributor') return $user->id;
        if ($user->role === 'operational_distributor') {
            $model = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'hr_manager') {
            $model = DB::table('hr_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'finance_manager') {
            $model = DB::table('finance_managers')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        if ($user->role === 'employee') {
            $model = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $model ? $model->parent_distributor_id : $user->id;
        }
        return $user->id;
    }
}