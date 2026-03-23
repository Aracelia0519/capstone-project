<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientReturnRequest;
use App\Models\EcommerceClient\ClientReturnMessage;
use App\Models\OperationDistributor\ECRefundRequest;
use App\Events\ReturnMessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ECReturnController extends Controller
{
    private function getPermissions($user, $permissionKey)
    {
        $defaults = ['can_view' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false];

        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true];
        }

        if ($user->role === 'employee') {
            $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
            if (!$employee) return $defaults;

            $position = DB::table('positions')
                ->where('title', $employee->position)
                ->where('distributor_id', $employee->parent_distributor_id) 
                ->first();
            if (!$position) return $defaults;

            // FIXED: Changed 'module_name' to 'permission_key' to match your database schema
            $access = DB::table('position_accessibilities')
                ->where('position_id', $position->id)
                ->where('permission_key', $permissionKey) 
                ->first();

            if ($access) {
                return [
                    'can_view' => (bool) $access->can_view,
                    'can_create' => (bool) $access->can_create,
                    'can_update' => (bool) $access->can_update,
                    'can_delete' => (bool) $access->can_delete
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

    private function getDistributorId($user)
    {
        if ($user->role === 'distributor') return $user->id;
        
        if ($user->role === 'operational_distributor') {
            $op = DB::table('operational_distributors')->where('user_id', $user->id)->first();
            return $op ? $op->parent_distributor_id : null; 
        }

        if ($user->role === 'employee') {
            $emp = DB::table('hr_employees')->where('user_id', $user->id)->first();
            return $emp ? $emp->parent_distributor_id : null; 
        }
        return null;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_view')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $permissions = $this->getPermissions($user, 'ec_returns');

        $returns = ClientReturnRequest::with(['orderItem.product', 'client'])
            ->where('distributor_id', $distId)
            ->orderBy('created_at', 'desc')
            ->get();

        // BULLETPROOF FIX: Deep injection
        $returnData = $returns->map(function ($ret) {
            $item = $ret->toArray(); 
            
            // Extract safely
            $clientId = $ret->client_id ?? ($ret->client->id ?? null);
            
            $item['client_has_gcash'] = false;
            $item['client_gcash_number'] = null;

            if ($clientId) {
                $paymentSetting = DB::table('client_payment_settings')->where('client_id', $clientId)->first();
                
                if ($paymentSetting && !empty($paymentSetting->gcash_number)) {
                    // Inject at root
                    $item['client_has_gcash'] = true;
                    $item['client_gcash_number'] = $paymentSetting->gcash_number;
                    
                    // Inject directly inside the client object array (un-strippable)
                    if (isset($item['client'])) {
                        $item['client']['has_gcash'] = true;
                        $item['client']['gcash_number'] = $paymentSetting->gcash_number;
                    }
                }
            }
            return $item;
        })->toArray(); // Enforce strict PHP array output

        return response()->json([
            'success' => true,
            'data' => $returnData,
            'distributor_id' => $distId,
            'permissions' => $permissions
        ]);
    }

    public function approveReturn(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_update')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
        
        $returnReq->update(['status' => 'approved']);

        $msg = ClientReturnMessage::create([
            'return_request_id' => $returnReq->id,
            'sender_id' => $distId,
            'receiver_id' => $returnReq->client_id,
            'type' => 'system',
            'message' => "Your return request has been approved. Please ship the item back to our distributor shop and provide the tracking details here."
        ]);

        broadcast(new ReturnMessageSent($msg))->toOthers();

        return response()->json(['success' => true, 'request' => $returnReq]);
    }

    public function rejectReturn(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_update')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
        
        $returnReq->update(['status' => 'rejected']);

        $msg = ClientReturnMessage::create([
            'return_request_id' => $returnReq->id,
            'sender_id' => $distId,
            'receiver_id' => $returnReq->client_id,
            'type' => 'system',
            'message' => "Your return request has been rejected."
        ]);

        broadcast(new ReturnMessageSent($msg))->toOthers();

        return response()->json(['success' => true, 'request' => $returnReq]);
    }

    public function receiveItemAndRequestRefund(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_update')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'receipt_proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'client_gcash_number' => 'required|string' // Enforce string requirement for safety
        ]);

        $distId = $this->getDistributorId($user);
        $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();

        $path = $request->file('receipt_proof')->store('ec_refund_receipts', 'public');

        DB::beginTransaction();
        try {
            $refundReq = ECRefundRequest::create([
                'return_request_id' => $returnReq->id,
                'distributor_id' => $distId,
                'requested_by' => $user->id,
                'amount' => $request->amount,
                'client_gcash_number' => $request->client_gcash_number, // We send it explicitly every time now
                'receipt_proof_path' => 'storage/' . $path,
                'status' => 'pending' // Finance will approve this
            ]);

            $returnReq->update(['status' => 'completed']);

            $msg = ClientReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $distId,
                'receiver_id' => $returnReq->client_id,
                'type' => 'system',
                'message' => "Shop has physically received your item. A refund request has been sent to Finance for processing."
            ]);

            broadcast(new ReturnMessageSent($msg))->toOthers();

            DB::commit();
            return response()->json(['success' => true, 'request' => $returnReq, 'refund' => $refundReq]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getReturnChat(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_view')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $req = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();

        ClientReturnMessage::where('return_request_id', $req->id)
            ->where('receiver_id', $distId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ClientReturnMessage::where('return_request_id', $req->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true, 
            'request' => $req, 
            'messages' => $messages, 
            'distributor_id' => $distId
        ]);
    }

    public function sendReturnMessage(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_update')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $request->validate([
            'message' => 'nullable|string', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $distId = $this->getDistributorId($user);
        $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();

        $msg = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('ec_returns_chat', 'public');
            $msg = ClientReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $distId,
                'receiver_id' => $returnReq->client_id,
                'type' => 'image',
                'file_path' => 'storage/' . $path
            ]);
            broadcast(new ReturnMessageSent($msg))->toOthers();
        }

        if ($request->message) {
            $msg2 = ClientReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $distId,
                'receiver_id' => $returnReq->client_id,
                'type' => 'text',
                'message' => $request->message
            ]);
            broadcast(new ReturnMessageSent($msg2))->toOthers();
            $msg = $msg2; 
        }

        return response()->json(['success' => true, 'data' => $msg]);
    }
}