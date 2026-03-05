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

class ProcurementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = ProcurementRequest::query();
            
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
            
            // FIX: Changed from has() to filled()
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // FIX: Changed from has() to filled()
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            
            // FIX: Changed from has() to filled()
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

    public function supplierProducts(Request $request, $supplierId)
    {
        try {
            $products = SupplierRawMaterial::where('user_id', $supplierId)
                ->where('is_active', true)
                ->get();

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
            $distributorId = $this->getDistributorId($user);

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
                    
                    return [
                        'id' => $partner->supplier_id,
                        'name' => $companyName ? $companyName : $supplierName,
                        'value' => $companyName ? $companyName : $supplierName
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
                    'addresses' => $addresses
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
            $distributorId = $this->getDistributorId($user);
            
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:users,id',
                'supplier' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:supplier_raw_materials,id',
                'items.*.quantity' => 'required|integer|min:1|max:5000',
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
                    'payment_terms' => $request->payment_terms ?? 'net30',
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
    
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $procurementRequest = ProcurementRequest::findOrFail($id);
            
            if (!$this->canUpdateRequest($user, $procurementRequest)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this request'
                ], 403);
            }
            
            $updatableFields = [];
            
            if ($user->id === $procurementRequest->requester_id && $procurementRequest->status === 'pending') {
                $updatableFields = ['delivery_address', 'instructions', 'required_by_date'];
            }
            
            if ($user->id === $procurementRequest->distributor_id) {
                if ($request->has('status')) {
                    $procurementRequest->updateStatus($request->status, $request->rejection_reason);
                }
                if ($request->has('shipping_method')) {
                    $updatableFields[] = 'shipping_method';
                }
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
            
            if ($user->id !== $procurementRequest->requester_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the requester can cancel this request'
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
            $query = ProcurementRequest::query();

            if ($user->role === 'distributor') {
                $query->where('distributor_id', $user->id);
            } elseif (in_array($user->role, ['operational_distributor', 'employee', 'hr_manager', 'finance_manager'])) {
                $query->where('requester_id', $user->id);
            }
            
            // FIX: Changed from has() to filled()
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
    
    private function canViewRequest($user, $request)
    {
        if ($user->role === 'admin') return true;
        if ($user->id === $request->requester_id) return true;
        if ($user->id === $request->distributor_id) return true;
        return false;
    }
    
    private function canUpdateRequest($user, $request)
    {
        if ($user->role === 'admin') return true;
        if ($user->id === $request->requester_id && $request->status === 'pending') return true;
        if ($user->id === $request->distributor_id) return true;
        return false;
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