<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\SPMessage;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm; 
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class ClientChatController extends Controller
{
    public function getContacts()
    {
        $clientId = Auth::id();

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
                    })
                    ->latest()
                    ->first();

                $unreadCount = SPMessage::where('sender_id', $providerId)
                    ->where('receiver_id', $clientId)
                    ->where('is_read', false)
                    ->count();

                $contacts[$providerId] = [
                    'id' => $providerId,
                    'name' => $req->provider ? $req->provider->first_name . ' ' . $req->provider->last_name : 'Unknown',
                    'service_request_id' => $req->id,
                    'service_title' => $req->serviceOffering ? $req->serviceOffering->title : 'Custom Job',
                    'last_message' => $lastMsg ? $lastMsg->message : 'No messages yet.',
                    'last_time' => $lastMsg ? $lastMsg->created_at->format('h:i A') : '',
                    'unread' => $unreadCount,
                    'status' => $req->status,
                    'date' => $req->created_at->format('M d'),
                    'requestContext' => $req 
                ];
            }
        }

        return response()->json(['success' => true, 'contacts' => array_values($contacts)]);
    }

    public function getMessages($providerId)
    {
        $clientId = Auth::id();
        $baseUrl = rtrim(request()->getSchemeAndHttpHost(), '/');

        SPMessage::where('sender_id', $providerId)
            ->where('receiver_id', $clientId)
            ->update(['is_read' => true]);

        $messages = SPMessage::where(function($q) use ($clientId, $providerId) {
                $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
            })->orWhere(function($q) use ($clientId, $providerId) {
                $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $messages->map(function ($msg) use ($clientId, $baseUrl) {
            
            $text = $msg->message;
            if ($msg->type === 'image' && !empty($msg->message)) {
                $text = $baseUrl . '/storage/' . ltrim(str_replace('/storage/', '', $msg->message), '/');
            }

            return [
                'id' => $msg->id,
                'sender' => $msg->sender_id === $clientId ? 'me' : 'them',
                'text' => $text,
                'type' => $msg->type,
                'payload' => $msg->payload,
                'time' => $msg->created_at->format('h:i A'),
                'status' => $msg->is_read ? 'read' : 'sent',
                'is_deleted' => isset($msg->payload['is_deleted']) && $msg->payload['is_deleted'] === true
            ];
        });

        return response()->json(['success' => true, 'messages' => $formattedMessages]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'service_request_id' => 'required',
            'message' => 'nullable|string', 
            'type' => 'required|string', 
            'payload' => 'nullable|array'
        ]);

        $message = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'service_request_id' => $request->service_request_id,
            'message' => $request->message ?? 'Attachment',
            'type' => $request->type,
            'payload' => $request->payload,
            'is_read' => false
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'message' => clone $message]);
    }

    public function sendImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'receiver_id' => 'required|exists:users,id',
            'service_request_id' => 'required'
        ]);

        $path = $request->file('image')->store('chat_images', 'public');
        
        $message = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'service_request_id' => $request->service_request_id,
            'message' => $path,
            'type' => 'image',
            'payload' => null,
            'is_read' => false
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $message->message = $baseUrl . '/storage/' . $path;

        return response()->json(['success' => true, 'message' => clone $message]);
    }

    public function updateMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $message = SPMessage::where('sender_id', Auth::id())->findOrFail($id);

        if ($message->type === 'text') {
            $message->update(['message' => $request->message]);
        }

        return response()->json(['success' => true, 'message' => clone $message]);
    }

    public function deleteMessage($id)
    {
        $message = SPMessage::where('sender_id', Auth::id())->findOrFail($id);
        
        $payload = $message->payload ?? [];
        $payload['is_deleted'] = true;

        $message->update([
            'message' => 'This message was deleted',
            'payload' => $payload
        ]);

        return response()->json(['success' => true]);
    }

    public function respondToDeal(Request $request, $dealId)
    {
        $request->validate([
            'action' => 'required|in:accept,decline',
            'message_id' => 'required|exists:sp_messages,id'
        ]);

        $deal = OfficialDeal::findOrFail($dealId);
        $chatMessage = SPMessage::findOrFail($request->message_id);
        $serviceRequest = ClientServiceRequest::findOrFail($deal->client_service_request_id);

        if ($request->action === 'accept') {
            $deal->update(['status' => 'ongoing']);
            $serviceRequest->update(['status' => 'ongoing']);
        } else {
            $deal->update(['status' => 'declined']);
            $serviceRequest->update(['status' => 'pending']);
        }

        $payload = $chatMessage->payload;
        $payload['deal_status'] = $deal->status;
        $chatMessage->update(['payload' => $payload]);

        $replyText = $request->action === 'accept' 
            ? 'I have accepted the official deal! We can proceed with the next steps.' 
            : 'I have declined the official deal. Please adjust the offer.';

        $replyMsg = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $deal->provider_id,
            'service_request_id' => $chatMessage->service_request_id,
            'message' => $replyText,
            'type' => 'text',
            'is_read' => false
        ]);

        broadcast(new MessageSent($replyMsg))->toOthers();

        return response()->json(['success' => true, 'updated_message' => clone $chatMessage]);
    }

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

        return response()->json(['success' => true, 'updated_message' => clone $chatMessage]);
    }
}