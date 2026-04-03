<?php

namespace App\Http\Controllers\Api\OperationDistributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientReturnRequest;
use App\Models\EcommerceClient\ClientReturnMessage;
use App\Models\ServiceProvider\SpReturnRequest;
use App\Models\ServiceProvider\SpReturnMessage;
use App\Events\ReturnMessageSent;
use App\Events\SpReturnMessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ECReturnController extends Controller
{
    private function getPermissions($user, $permissionKey)
    {
        $defaults = [
            'is_granted' => false,
            'can_view' => false, 
            'can_manage' => false, 
            'can_approve' => false
        ];

        if ($user->role === 'distributor' || $user->role === 'operational_distributor') {
            return [
                'is_granted' => true,
                'can_view' => true, 
                'can_manage' => true, 
                'can_approve' => true
            ];
        }

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
                    'is_granted' => (bool) $access->is_granted,
                    'can_view' => (bool) $access->can_view,
                    'can_manage' => (bool) $access->can_manage,
                    'can_approve' => (bool) $access->can_approve
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

        // 1. Fetch Client Returns
        $clientReturns = ClientReturnRequest::with(['orderItem.product', 'client'])
            ->where('distributor_id', $distId)
            ->get()
            ->map(function ($ret) {
                $item = $ret->toArray(); 
                $item['request_type'] = 'client';
                
                $clientId = $ret->client_id ?? ($ret->client->id ?? null);
                $item['client_has_gcash'] = false;
                $item['client_gcash_number'] = null;

                // FORCE NAME MAPPING FOR THE FRONTEND
                if (isset($item['client'])) {
                    $item['client']['name'] = $ret->client->full_name ?? trim(($ret->client->first_name ?? '') . ' ' . ($ret->client->last_name ?? ''));
                    if (empty(trim($item['client']['name']))) {
                        $item['client']['name'] = 'Unknown Client';
                    }
                } else {
                    $item['client'] = ['name' => 'Unknown Client'];
                }

                if ($clientId) {
                    $paymentSetting = DB::table('client_payment_settings')->where('client_id', $clientId)->first();
                    
                    if ($paymentSetting && !empty($paymentSetting->gcash_number)) {
                        $item['client_has_gcash'] = true;
                        $item['client_gcash_number'] = $paymentSetting->gcash_number;
                        if (isset($item['client'])) {
                            $item['client']['has_gcash'] = true;
                            $item['client']['gcash_number'] = $paymentSetting->gcash_number;
                        }
                    }
                }
                return $item;
            });

        // 2. Fetch Service Provider Returns
        $spReturns = SpReturnRequest::with(['orderItem.product'])
            ->where('distributor_id', $distId)
            ->get()
            ->map(function ($ret) {
                $item = $ret->toArray(); 
                $item['request_type'] = 'sp';
                
                // Construct a mock 'client' structure for frontend compatibility
                $spUser = DB::table('users')->where('id', $ret->sp_id)->first();
                $item['client'] = [
                    'id' => $spUser->id ?? null,
                    'name' => $spUser ? trim($spUser->first_name . ' ' . $spUser->last_name) : 'Unknown Provider',
                    'has_gcash' => false,
                    'gcash_number' => null
                ];
                $item['client_has_gcash'] = false;
                $item['client_gcash_number'] = null;

                return $item;
            });

        // Merge and sort
        $returnData = $clientReturns->concat($spReturns)->sortByDesc('created_at')->values()->toArray();

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
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_approve')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $type = $request->input('request_type', 'client');

        if ($type === 'sp') {
            $returnReq = SpReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            $returnReq->update(['status' => 'approved']);

            $msg = SpReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $distId,
                'receiver_id' => $returnReq->sp_id,
                'type' => 'system',
                'message' => "Your return request has been approved. Please ship the item back to our distributor shop and provide the tracking details here."
            ]);

            broadcast(new SpReturnMessageSent($msg))->toOthers();
        } else {
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
        }

        return response()->json(['success' => true, 'message' => 'Approved']);
    }

    public function rejectReturn(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_approve')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $distId = $this->getDistributorId($user);
        $type = $request->input('request_type', 'client');

        if ($type === 'sp') {
            $returnReq = SpReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            $returnReq->update(['status' => 'rejected']);

            $msg = SpReturnMessage::create([
                'return_request_id' => $returnReq->id,
                'sender_id' => $distId,
                'receiver_id' => $returnReq->sp_id,
                'type' => 'system',
                'message' => "Your return request has been rejected."
            ]);

            broadcast(new SpReturnMessageSent($msg))->toOthers();
        } else {
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
        }

        return response()->json(['success' => true, 'message' => 'Rejected']);
    }

    public function receiveItemAndRequestRefund(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_manage')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'receipt_proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'client_gcash_number' => 'required|string'
        ]);

        $distId = $this->getDistributorId($user);
        $type = $request->input('request_type', 'client');
        $path = $request->file('receipt_proof')->store('ec_refund_receipts', 'public');

        DB::beginTransaction();
        try {
            if ($type === 'sp') {
                $returnReq = SpReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
                $returnReq->update(['status' => 'completed']);

                $msg = SpReturnMessage::create([
                    'return_request_id' => $returnReq->id,
                    'sender_id' => $distId,
                    'receiver_id' => $returnReq->sp_id,
                    'type' => 'system',
                    'message' => "Shop has physically received your item. A refund request has been sent to Finance for processing."
                ]);

                broadcast(new SpReturnMessageSent($msg))->toOthers();
            } else {
                $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
                $returnReq->update(['status' => 'completed']);

                $msg = ClientReturnMessage::create([
                    'return_request_id' => $returnReq->id,
                    'sender_id' => $distId,
                    'receiver_id' => $returnReq->client_id,
                    'type' => 'system',
                    'message' => "Shop has physically received your item. A refund request has been sent to Finance for processing."
                ]);

                broadcast(new ReturnMessageSent($msg))->toOthers();
            }

            // Secure raw DB insert because ECRefundRequest might not have sp_return_request_id in fillables
            DB::table('ec_refund_requests')->insert([
                'return_request_id' => $type === 'client' ? $returnReq->id : null,
                'sp_return_request_id' => $type === 'sp' ? $returnReq->id : null,
                'distributor_id' => $distId,
                'requested_by' => $user->id,
                'amount' => $request->amount,
                'client_gcash_number' => $request->client_gcash_number,
                'receipt_proof_path' => 'storage/' . $path,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Refund processed']);
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
        $type = $request->query('type', 'client');

        if ($type === 'sp') {
            $req = SpReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            SpReturnMessage::where('return_request_id', $req->id)
                ->where('receiver_id', $distId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = SpReturnMessage::where('return_request_id', $req->id)
                ->orderBy('created_at', 'asc')->get();
        } else {
            $req = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            ClientReturnMessage::where('return_request_id', $req->id)
                ->where('receiver_id', $distId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $messages = ClientReturnMessage::where('return_request_id', $req->id)
                ->orderBy('created_at', 'asc')->get();
        }

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
        if (!$this->checkRbacAccess($user, 'ec_returns', 'can_manage')) {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $request->validate([
            'message' => 'nullable|string', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $distId = $this->getDistributorId($user);
        $type = $request->input('type', 'client');
        $msg = null;

        if ($type === 'sp') {
            $returnReq = SpReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('ec_returns_chat', 'public');
                $msg = SpReturnMessage::create([
                    'return_request_id' => $returnReq->id,
                    'sender_id' => $distId,
                    'receiver_id' => $returnReq->sp_id,
                    'type' => 'image',
                    'file_path' => 'storage/' . $path
                ]);
                broadcast(new SpReturnMessageSent($msg))->toOthers();
            }

            if ($request->message) {
                $msg2 = SpReturnMessage::create([
                    'return_request_id' => $returnReq->id,
                    'sender_id' => $distId,
                    'receiver_id' => $returnReq->sp_id,
                    'type' => 'text',
                    'message' => $request->message
                ]);
                broadcast(new SpReturnMessageSent($msg2))->toOthers();
                $msg = $msg2; 
            }
        } else {
            $returnReq = ClientReturnRequest::where('id', $id)->where('distributor_id', $distId)->firstOrFail();
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
        }

        return response()->json(['success' => true, 'data' => $msg]);
    }
}