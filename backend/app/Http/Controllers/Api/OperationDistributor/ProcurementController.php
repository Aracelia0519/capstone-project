<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use App\Models\OperationDistributor\ProcurementRequest;
use App\Models\Distributor\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// Import all necessary models
use App\Models\Supplier\SupplierPartner;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\OperationalDistributor; 
use App\Models\Supplier\SupplierRequirements;
use App\Models\Distributor\DistributorAddress; 

class ProcurementController extends Controller
{
    /**
     * Get all procurement requests for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = ProcurementRequest::query();
            
            // Filter based on user role
            if ($user->role === 'distributor') {
                $query->where('distributor_id', $user->id);
            } elseif (in_array($user->role, ['operational_distributor', 'employee', 'hr_manager', 'finance_manager'])) {
                $query->where('requester_id', $user->id);
            } elseif ($user->role === 'admin') {
                // Admin can see all requests
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view procurement requests'
                ], 403);
            }
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('request_code', 'like', "%{$search}%")
                      ->orWhere('product_name', 'like', "%{$search}%")
                      ->orWhere('supplier', 'like', "%{$search}%");
                });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Paginate
            $perPage = $request->get('per_page', 15);
            // Added selectedSupplier to with()
            $requests = $query->with(['product', 'requester', 'distributor', 'selectedSupplier'])->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Procurement requests retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve procurement requests: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get available products for procurement
     */
    public function availableProducts(Request $request)
    {
        try {
            $query = Product::with(['distributor'])->active();
            
            // Apply filters
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }
            
            if ($request->has('distributor_id')) {
                $query->where('distributor_id', $request->distributor_id);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $products = $query->paginate($request->get('per_page', 12));
            
            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Available products retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form options (suppliers and addresses)
     */
    public function formOptions(Request $request)
    {
        try {
            $user = Auth::user();
            $distributorId = $user->id;

            // Determine the distributor ID based on user role
            if ($user->role === 'operational_distributor') {
                $opDist = OperationalDistributor::where('user_id', $user->id)->first();
                if ($opDist) {
                    $distributorId = $opDist->parent_distributor_id;
                }
            }

            // 1. Fetch Partnered Suppliers safely
            // We use 'values()' to re-index array after filter
            $suppliers = SupplierPartner::where('distributor_id', $distributorId)
                ->where('status', 'active')
                ->with('supplier')
                ->get()
                ->map(function($partner) {
                    // Safety check: if supplier user is deleted/null, skip
                    if (!$partner->supplier) {
                        return null;
                    }

                    $supplierName = $partner->supplier->full_name ?? ($partner->supplier->first_name . ' ' . $partner->supplier->last_name);
                    
                    // Try to fetch company name
                    $companyName = null;
                    try {
                        $companyName = SupplierRequirements::where('user_id', $partner->supplier_id)->value('company_name');
                    } catch (\Exception $e) {
                        // If table doesn't exist or error, ignore
                    }
                    
                    return [
                        'id' => $partner->supplier_id,
                        'name' => $companyName ? $companyName : $supplierName,
                        'value' => $companyName ? $companyName : $supplierName
                    ];
                })
                ->filter() // Remove nulls
                ->values(); // Reset keys

            // 2. Fetch Distributor Addresses using direct query to avoid relationship errors
            $addresses = [];
            $distributorReq = DistributorRequirements::where('user_id', $distributorId)->first();
            
            if ($distributorReq) {
                // Manually query addresses using the ID from requirements
                $addresses = DistributorAddress::where('distributor_requirements_id', $distributorReq->id)->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suppliers' => $suppliers,
                    'addresses' => $addresses
                ],
                'message' => 'Form options retrieved successfully'
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging (in a real app)
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form options: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a new procurement request
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:distributor_products,id',
                'quantity' => 'required|integer|min:1',
                'supplier_id' => 'required|exists:users,id', // Added validation for supplier_id
                'supplier' => 'required|string|max:255',
                'priority' => 'required|in:low,medium,high',
                'delivery_address' => 'required|string',
                'shipping_method' => 'nullable|string|in:standard,express,pickup',
                'payment_terms' => 'nullable|string|in:net30,net60,cod,advance',
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
            
            // Get the product
            $product = Product::findOrFail($request->product_id);
            
            // Check quantity
            if ($request->quantity > 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity is too high'
                ], 400);
            }
            
            // Calculate total cost
            $unitCost = $product->cost ?? $product->price; 
            $totalCost = $unitCost * $request->quantity;
            
            // Create the procurement request
            $procurementRequest = ProcurementRequest::create([
                'requester_id' => $user->id,
                'distributor_id' => $product->distributor_id,
                'product_id' => $product->id,
                'supplier_id' => $request->supplier_id, // Saving supplier_id
                'request_code' => ProcurementRequest::generateRequestCode(),
                'product_name' => $product->name,
                'category' => $product->category,
                'supplier' => $request->supplier,
                'quantity' => $request->quantity,
                'unit_price' => $unitCost,
                'total_cost' => $totalCost,
                'priority' => $request->priority,
                'status' => 'pending',
                'shipping_method' => $request->shipping_method ?? 'standard',
                'payment_terms' => $request->payment_terms ?? 'net30',
                'delivery_address' => $request->delivery_address,
                'instructions' => $request->instructions,
                'required_by_date' => $request->required_by_date,
                'request_date' => now()->toDateString()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $procurementRequest->load(['product', 'distributor', 'selectedSupplier']),
                'message' => 'Procurement request created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get a single procurement request
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            // Added selectedSupplier to with()
            $request = ProcurementRequest::with(['product', 'requester', 'distributor', 'selectedSupplier'])->findOrFail($id);
            
            // Check authorization
            if (!$this->canViewRequest($user, $request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this request'
                ], 403);
            }
            
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
    
    /**
     * Update a procurement request (for status updates, etc.)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $procurementRequest = ProcurementRequest::findOrFail($id);
            
            // Check authorization
            if (!$this->canUpdateRequest($user, $procurementRequest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this request'
                ], 403);
            }
            
            // Only certain fields can be updated based on user role and request status
            $updatableFields = [];
            
            if ($user->id === $procurementRequest->requester_id && $procurementRequest->status === 'pending') {
                // Requester can update certain fields if request is still pending
                $updatableFields = ['delivery_address', 'instructions', 'required_by_date'];
            }
            
            if ($user->id === $procurementRequest->distributor_id) {
                // Distributor can update status and rejection reason
                if ($request->has('status')) {
                    $procurementRequest->updateStatus($request->status, $request->rejection_reason);
                }
                
                if ($request->has('shipping_method')) {
                    $updatableFields[] = 'shipping_method';
                }
            }
            
            // Update allowed fields
            foreach ($updatableFields as $field) {
                if ($request->has($field)) {
                    $procurementRequest->$field = $request->$field;
                }
            }
            
            $procurementRequest->save();
            
            return response()->json([
                'success' => true,
                'data' => $procurementRequest->fresh(['product', 'requester', 'distributor']),
                'message' => 'Procurement request updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update procurement request: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel a procurement request
     */
    public function cancel(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $procurementRequest = ProcurementRequest::findOrFail($id);
            
            // Check if user can cancel this request
            if ($user->id !== $procurementRequest->requester_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the requester can cancel this request'
                ], 403);
            }
            
            // Check if request can be cancelled
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
    
    /**
     * Get procurement statistics
     */
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();
            $query = ProcurementRequest::query();
            
            // Filter based on user role
            if ($user->role === 'distributor') {
                $query->where('distributor_id', $user->id);
            } elseif (in_array($user->role, ['operational_distributor', 'employee', 'hr_manager', 'finance_manager'])) {
                $query->where('requester_id', $user->id);
            }
            
            // Time filter
            if ($request->has('period')) {
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
            
            // Calculate statistics
            $totalRequests = $query->count();
            $totalCost = $query->sum('total_cost');
            
            $statusCounts = $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status');
            
            $priorityCounts = $query->groupBy('priority')
                ->selectRaw('priority, count(*) as count')
                ->pluck('count', 'priority');
            
            // Recent requests
            $recentRequests = ProcurementRequest::query()
                ->when($user->role === 'distributor', function($q) use ($user) {
                    $q->where('distributor_id', $user->id);
                })
                ->when(in_array($user->role, ['operational_distributor', 'employee', 'hr_manager', 'finance_manager']), function($q) use ($user) {
                    $q->where('requester_id', $user->id);
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
    
    /**
     * Check if user can view a request
     */
    private function canViewRequest($user, $request)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->id === $request->requester_id) {
            return true;
        }
        
        if ($user->id === $request->distributor_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can update a request
     */
    private function canUpdateRequest($user, $request)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if ($user->id === $request->requester_id && $request->status === 'pending') {
            return true;
        }
        
        if ($user->id === $request->distributor_id) {
            return true;
        }
        
        return false;
    }
}