<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\SPMessage;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm; // NEW
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class ClientChatController extends Controller
{
    public function getContacts()
    {
        $clientId = Auth::id();

        // FIXED: Removed 'pending' filter
        $requests = ClientServiceRequest::with(['provider', 'serviceOffering'])
            ->where('client_id', $clientId)
            ->where('status', '!=', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        $contacts = [];

        foreach ($requests as $req) {
            if (!$req->provider_id) continue;
            
            $providerId = $req->provider_id;
            
            if (!isset($contacts[$providerId])) {
                $lastMsg = SPMessage::where(function($q) use ($clientId, $providerId) {
                        $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
                    })->orWhere(function($q) use ($clientId, $providerId) {
                        $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
                    })->orderBy('created_at', 'desc')->first();

                $unreadCount = SPMessage::where('sender_id', $providerId)
                    ->where('receiver_id', $clientId)
                    ->where('is_read', false)
                    ->count();

                $contacts[$providerId] = [
                    'id' => $providerId,
                    'name' => $req->provider->first_name . ' ' . $req->provider->last_name,
                    'jobTitle' => ($req->serviceOffering ? $req->serviceOffering->title : 'Custom Service') . ' - ' . ucfirst($req->status),
                    'status' => 'online', 
                    'lastMessage' => $lastMsg ? ($lastMsg->type === 'text' ? $lastMsg->message : 'Sent a ' . str_replace('_', ' ', $lastMsg->type)) : 'No messages yet',
                    'time' => $lastMsg ? $lastMsg->created_at->format('h:i A') : $req->updated_at->format('h:i A'),
                    'unread' => $unreadCount,
                    'requestContext' => $req 
                ];
            }
        }

        return response()->json(['success' => true, 'data' => array_values($contacts)]);
    }

    public function getMessages($providerId)
    {
        $clientId = Auth::id();

        SPMessage::where('sender_id', $providerId)
            ->where('receiver_id', $clientId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SPMessage::where(function($q) use ($clientId, $providerId) {
                $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
            })->orWhere(function($q) use ($clientId, $providerId) {
                $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'service_request_id' => 'nullable|exists:client_service_requests,id',
            'message' => 'nullable|string',
            'type' => 'required|in:text,request_summary,official_deal,payment_term', // UPDATED
            'payload' => 'nullable|array'
        ]);

        $message = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'service_request_id' => $request->service_request_id,
            'message' => $request->message,
            'type' => $request->type,
            'payload' => $request->payload,
            'is_read' => false
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function respondToDeal(Request $request, $dealId)
    {
        $request->validate([
            'action' => 'required|in:agree,decline',
            'message_id' => 'required|exists:sp_messages,id'
        ]);

        $deal = OfficialDeal::findOrFail($dealId);
        $chatMessage = SPMessage::findOrFail($request->message_id);

        if ($request->action === 'agree') {
            $deal->update(['status' => 'ongoing']);
            
            if ($deal->client_service_request_id) {
                ClientServiceRequest::where('id', $deal->client_service_request_id)
                    ->update(['status' => 'ongoing']);
            }

            $payload = $chatMessage->payload;
            $payload['deal_status'] = 'ongoing';
            $chatMessage->update(['payload' => $payload]);

            $replyMsg = SPMessage::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $deal->provider_id,
                'service_request_id' => $deal->client_service_request_id,
                'message' => 'I have agreed to the official deal. Let\'s begin the ongoing project!',
                'type' => 'text',
                'is_read' => false
            ]);
            broadcast(new MessageSent($replyMsg))->toOthers();

        } else {
            $deal->update(['status' => 'declined']);
            
            $payload = $chatMessage->payload;
            $payload['deal_status'] = 'declined';
            $chatMessage->update(['payload' => $payload]);

            $replyMsg = SPMessage::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $deal->provider_id,
                'service_request_id' => $deal->client_service_request_id,
                'message' => 'I have declined the official deal. Can we adjust the terms?',
                'type' => 'text',
                'is_read' => false
            ]);
            broadcast(new MessageSent($replyMsg))->toOthers();
        }

        return response()->json(['success' => true, 'updated_message' => $chatMessage]);
    }

    // NEW: Handle Payment Term Acceptance/Decline
    public function respondToPaymentTerm(Request $request, $termId)
    {
        $request->validate([
            'action' => 'required|in:agree,decline',
            'message_id' => 'required|exists:sp_messages,id'
        ]);

        $term = OfficialPaymentTerm::findOrFail($termId);
        $chatMessage = SPMessage::findOrFail($request->message_id);

        $status = $request->action === 'agree' ? 'agreed' : 'declined';
        $term->update(['status' => $status]);

        $payload = $chatMessage->payload;
        $payload['term_status'] = $status;
        $chatMessage->update(['payload' => $payload]);

        $replyText = $request->action === 'agree' 
            ? 'I have agreed to the payment terms.' 
            : 'I have declined the payment terms. Let\'s negotiate.';

        $replyMsg = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $term->provider_id,
            'service_request_id' => $chatMessage->service_request_id,
            'message' => $replyText,
            'type' => 'text',
            'is_read' => false
        ]);
        
        broadcast(new MessageSent($replyMsg))->toOthers();

        return response()->json(['success' => true, 'updated_message' => $chatMessage]);
    }
}