<?php

namespace App\Http\Controllers\Api\SupplierDelivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier\SupplierDelivery;
use App\Models\OperationDistributor\ProcurementRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\ProcurementRequestUpdated;
use App\Events\SupplierOrderUpdated;

class SupplierDeliveryController extends Controller
{
    /**
     * Fetch all assigned deliveries for the logged-in delivery personnel.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Find the personnel record mapped to this user
        $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();

        if (!$personnel) {
            return response()->json(['message' => 'Unauthorized. Delivery personnel profile not found.'], 403);
        }

        // Fetch Supplier's Location for the Remittance Step
        $supplierLatitude = null;
        $supplierLongitude = null;
        $supplierReq = DB::table('supplier_requirements')->where('user_id', $personnel->supplier_id)->first();
        
        if ($supplierReq) {
            $suppAddr = DB::table('supplier_addresses')->where('supplier_requirements_id', $supplierReq->id)->first() 
                     ?? DB::table('supplier_addresses')->where('supplier_id', $personnel->supplier_id)->first();
            
            if ($suppAddr) {
                $supplierLatitude = $suppAddr->latitude;
                $supplierLongitude = $suppAddr->longitude;
            }
        }

        $deliveries = SupplierDelivery::with(['procurementRequest', 'procurementRequest.distributor'])
            ->where('delivery_personnel_id', $personnel->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($d) use ($supplierLatitude, $supplierLongitude) {
                $req = $d->procurementRequest;
                
                $latitude = null;
                $longitude = null;
                $fullAddress = $req->delivery_address ?? 'Unknown Address';
                $distCompanyName = 'Unknown Distributor';

                if ($req && $req->distributor_id) {
                    $distReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
                    if ($distReq) {
                        $distCompanyName = $distReq->company_name ?? 'Unknown Distributor';
                        $address = DB::table('distributor_addresses')->where('distributor_requirements_id', $distReq->id)->first();
                        if ($address) {
                            $latitude = $address->latitude;
                            $longitude = $address->longitude;
                            $fullAddress = "{$address->block_address}, {$address->barangay}, {$address->city}, {$address->province}";
                        }
                    }
                }

                // Fetch Requester (Operational Distributor) Details
                $requesterName = 'Unknown';
                $requesterPhone = 'N/A';
                $requesterEmail = 'N/A';
                
                if ($req && $req->requester_id) {
                    $requesterUser = DB::table('users')->where('id', $req->requester_id)->first();
                    if ($requesterUser) {
                        $requesterName = trim($requesterUser->first_name . ' ' . $requesterUser->last_name);
                        $requesterPhone = $requesterUser->phone ?? 'N/A';
                        $requesterEmail = $requesterUser->email ?? 'N/A';
                    }
                }

                $isReplacement = false;
                if ($req) {
                    $hasActiveReturn = DB::table('procurement_returns')
                        ->where('procurement_request_id', $req->id)
                        ->whereNotIn('status', ['completed', 'rejected'])
                        ->exists();
                    
                    if ($hasActiveReturn || str_contains($d->notes ?? '', '[REPLACEMENT DELIVERY]')) {
                        $isReplacement = true;
                    }
                }

                // Resolve Vehicle Details attached to delivery (Uses Safe Dynamic Naming Check)
                $vehicleName = 'Unknown Delivery Truck';
                if ($d->vehicle_id) {
                    $veh = DB::table('supplier_vehicles')->where('id', $d->vehicle_id)->first();
                    if ($veh) {
                        $vehicleName = $veh->vehicle_name ?? $veh->vehicle_type ?? $veh->plate_number ?? $veh->model ?? $veh->make ?? $veh->name ?? ('Vehicle #' . $veh->id);
                    }
                }

                return [
                    'id' => $d->id,
                    'code' => $req ? $req->request_code : 'Unknown',
                    'customer' => $distCompanyName, // Swapped to Business Name
                    'requesterName' => $requesterName, // Added
                    'requesterPhone' => $requesterPhone, // Added
                    'requesterEmail' => $requesterEmail, // Added
                    'address' => $fullAddress,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'supplierLatitude' => $supplierLatitude,
                    'supplierLongitude' => $supplierLongitude,
                    'itemCount' => $req ? $req->quantity : 0,
                    'productName' => $req ? $req->product_name : 'Unknown Product',
                    'paymentTerms' => $isReplacement ? 'NON-COD' : ($req ? strtoupper($req->payment_terms) : 'NON-COD'),
                    'totalAmount' => $isReplacement ? 0 : ($req ? $req->total_cost : 0),
                    'status' => $d->status,
                    'is_ready_to_go' => (bool)$d->is_ready_to_go,
                    'vehicleName' => $vehicleName,
                    'notes' => $d->notes ?? ($req ? $req->instructions : ''),
                    'timestamp' => $d->delivered_at ? date('M d, Y h:i A', strtotime($d->delivered_at)) : null,
                    'arrivalProof' => $d->arrival_proof_path ? asset($d->arrival_proof_path) : null,
                    'isReplacement' => $isReplacement
                ];
            });

        return response()->json($deliveries);
    }

    /**
     * Start the delivery process
     */
    public function startDelivery(Request $request, $id)
    {
        $delivery = SupplierDelivery::findOrFail($id);
        
        if (!$delivery->is_ready_to_go) {
            return response()->json(['message' => 'Supplier has not signaled ready for departure. Loading may not be finished.'], 400);
        }

        $delivery->status = 'in_transit'; 
        $delivery->save();

        if ($delivery->procurement_request_id) {
            
            $activeReturn = DB::table('procurement_returns')
                ->where('procurement_request_id', $delivery->procurement_request_id)
                ->whereNotIn('status', ['completed', 'rejected'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($activeReturn) {
                DB::table('procurement_returns')
                    ->where('id', $activeReturn->id)
                    ->update([
                        'status' => 'out_for_delivery',
                        'updated_at' => now()
                    ]);
            } else {
                $req = ProcurementRequest::find($delivery->procurement_request_id);
                if ($req) {
                    $req->status = 'in_transit'; 
                    if (!$req->shipped_at) {
                        $req->shipped_at = now();
                    }
                    $req->save();
                }
            }

            $req = ProcurementRequest::find($delivery->procurement_request_id);
            if ($req) {
                event(new ProcurementRequestUpdated($req->distributor_id));
                event(new SupplierOrderUpdated($req->supplier_id));
            }
        }

        return response()->json(['message' => 'Delivery started successfully']);
    }

    /**
     * Mark the delivery as arrived at Distributor
     */
    public function arrive(Request $request, $id)
    {
        $delivery = SupplierDelivery::findOrFail($id);
        $req = ProcurementRequest::find($delivery->procurement_request_id);
        
        $activeReturn = DB::table('procurement_returns')
            ->where('procurement_request_id', $delivery->procurement_request_id)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->first();

        $isReplacement = $activeReturn ? true : str_contains($delivery->notes ?? '', '[REPLACEMENT DELIVERY]');
        $isCOD = (!$isReplacement && $req && strtoupper($req->payment_terms) === 'COD');

        $rules = [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ];

        if ($isCOD) {
            $rules['payment_image'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
        }

        $request->validate($rules);

        $bypassLocation = filter_var($request->input('bypass_location', false), FILTER_VALIDATE_BOOLEAN);

        if (!$bypassLocation && $req && $req->distributor_id) {
            $distReq = DB::table('distributor_requirements')->where('user_id', $req->distributor_id)->first();
            if ($distReq) {
                $address = DB::table('distributor_addresses')->where('distributor_requirements_id', $distReq->id)->first();
                if ($address && $address->latitude && $address->longitude) {
                    $distance = $this->calculateDistance($request->latitude, $request->longitude, $address->latitude, $address->longitude);
                    if ($distance > 500) {
                        return response()->json(['message' => 'You are too far from the destination to complete the delivery.'], 400);
                    }
                }
            }
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $filename = 'arrival_proof_' . $delivery->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('supplier/arrival_proofs', $filename, 'public');
                $delivery->arrival_proof_path = 'storage/' . $path;
            }

            if ($isCOD && $request->hasFile('payment_image')) {
                $file = $request->file('payment_image');
                $filename = 'payment_rcvd_' . $delivery->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('supplier/payment_proofs', $filename, 'public');
                $delivery->payment_received_proof_path = 'storage/' . $path;
            }

            $delivery->status = $isCOD ? 'remitting' : 'completed';
            $delivery->delivered_at = now();
            $delivery->save();

            if ($activeReturn) {
                DB::table('procurement_returns')
                    ->where('id', $activeReturn->id)
                    ->update([
                        'status' => 'delivered',
                        'updated_at' => now()
                    ]);
            } else {
                if ($req) {
                    $req->status = 'delivered';
                    $req->delivered_at = now();
                    $req->save();
                }
            }

            DB::commit();

            if ($req) {
                event(new ProcurementRequestUpdated($req->distributor_id));
                event(new SupplierOrderUpdated($req->supplier_id));
            }

            return response()->json(['message' => 'Delivery marked as arrived successfully!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process arrival: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark the COD delivery as fully remitted at Supplier
     */
    public function remit(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'remittance_image' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $delivery = SupplierDelivery::findOrFail($id);

        if ($delivery->status !== 'remitting') {
            return response()->json(['message' => 'Delivery is not in remitting status.'], 400);
        }

        $bypassLocation = filter_var($request->input('bypass_location', false), FILTER_VALIDATE_BOOLEAN);

        $user = $request->user();
        $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
        $supplierReq = DB::table('supplier_requirements')->where('user_id', $personnel->supplier_id)->first();
        
        if (!$bypassLocation && $supplierReq) {
            $suppAddr = DB::table('supplier_addresses')->where('supplier_requirements_id', $supplierReq->id)->first()
                     ?? DB::table('supplier_addresses')->where('supplier_id', $personnel->supplier_id)->first();
            
            if ($suppAddr && $suppAddr->latitude && $suppAddr->longitude) {
                $distance = $this->calculateDistance($request->latitude, $request->longitude, $suppAddr->latitude, $suppAddr->longitude);
                if ($distance > 500) {
                    return response()->json(['message' => 'You are too far from the Supplier HQ to remit funds.'], 400);
                }
            }
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('remittance_image')) {
                $file = $request->file('remittance_image');
                $filename = 'remittance_proof_' . $delivery->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('supplier/remittance_proofs', $filename, 'public');
                $delivery->remittance_proof_path = 'storage/' . $path;
            }

            $delivery->status = 'completed';
            $delivery->save();

            DB::commit();

            $req = ProcurementRequest::find($delivery->procurement_request_id);
            if ($req) {
                event(new ProcurementRequestUpdated($req->distributor_id));
                event(new SupplierOrderUpdated($req->supplier_id));
            }

            return response()->json(['message' => 'Funds remitted and delivery cycle completed!']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process remittance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delivery Personnel cancels/rejects the assigned delivery
     */
    public function rejectDelivery(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $delivery = SupplierDelivery::findOrFail($id);

        $user = $request->user();
        $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
        $personnelName = $personnel ? trim($personnel->first_name . ' ' . $personnel->last_name) : 'Unknown Delivery Personnel';

        DB::beginTransaction();

        try {
            $req = ProcurementRequest::find($delivery->procurement_request_id);
            
            if ($req) {
                $req->status = 'prepared';
                $req->rejection_reason = "Rejected by {$personnelName}: " . $request->reason;
                $req->save();
            }

            $delivery->delete();

            DB::commit();

            if ($req) {
                event(new ProcurementRequestUpdated($req->distributor_id));
                event(new SupplierOrderUpdated($req->supplier_id));
            }

            return response()->json(['message' => 'Delivery rejected successfully.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reject delivery: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Haversine formula to calculate distance
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}