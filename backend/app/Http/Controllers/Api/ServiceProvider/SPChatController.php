<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\SPMessage;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm; 
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SPChatController extends Controller
{
    public function getContacts()
    {
        $providerId = Auth::id();

        $requests = ClientServiceRequest::with(['client', 'serviceOffering'])
            ->where('provider_id', $providerId)
            ->where('status', '!=', 'rejected') 
            ->orderBy('updated_at', 'desc')
            ->get();

        $contacts = [];

        foreach ($requests as $req) {
            $clientId = $req->client_id;
            
            if (!isset($contacts[$clientId])) {
                $lastMsg = SPMessage::where(function($q) use ($providerId, $clientId) {
                        $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
                    })->orWhere(function($q) use ($providerId, $clientId) {
                        $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
                    })
                    ->latest()
                    ->first();

                $unreadCount = SPMessage::where('sender_id', $clientId)
                    ->where('receiver_id', $providerId)
                    ->where('is_read', false)
                    ->count();

                $contacts[$clientId] = [
                    'id' => $clientId,
                    'name' => $req->client ? $req->client->first_name . ' ' . $req->client->last_name : 'Unknown',
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

    public function getMessages($clientId)
    {
        $providerId = Auth::id();
        $baseUrl = rtrim(request()->getSchemeAndHttpHost(), '/');

        SPMessage::where('sender_id', $clientId)
            ->where('receiver_id', $providerId)
            ->update(['is_read' => true]);

        $messages = SPMessage::where(function($q) use ($providerId, $clientId) {
                $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
            })->orWhere(function($q) use ($providerId, $clientId) {
                $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $messages->map(function ($msg) use ($providerId, $baseUrl) {
            
            $text = $msg->message;
            if ($msg->type === 'image' && !empty($msg->message)) {
                $text = $baseUrl . '/storage/' . ltrim(str_replace('/storage/', '', $msg->message), '/');
            }

            return [
                'id' => $msg->id,
                'sender' => $msg->sender_id === $providerId ? 'me' : 'them',
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

        $payload = $request->payload ?? [];

        if ($request->type === 'official_deal') {
            $deal = OfficialDeal::create([
                'provider_id' => Auth::id(),
                'client_id' => $request->receiver_id,
                'client_service_request_id' => $request->service_request_id,
                'price' => $payload['price'],
                'description' => $payload['description'],
                'colors' => isset($payload['colors']) && !empty($payload['colors']) ? json_encode($payload['colors']) : null,
                'status' => 'pending'
            ]);

            $payload['deal_id'] = $deal->id;
            $payload['deal_status'] = 'pending';
        }

        if ($request->type === 'payment_term') {
            $activeDeal = OfficialDeal::where('client_id', $request->receiver_id)
                                      ->where('provider_id', Auth::id())
                                      ->where('client_service_request_id', $request->service_request_id)
                                      ->latest()
                                      ->first();

            if (!$activeDeal) {
                return response()->json(['success' => false, 'message' => 'No official deal found for this request.']);
            }

            $term = OfficialPaymentTerm::create([
                'official_deal_id' => $activeDeal->id, 
                'provider_id' => Auth::id(),
                'client_id' => $request->receiver_id,
                'payment_method' => $payload['payment_method'],
                'payment_term' => $payload['payment_term'],
                'status' => 'pending'
            ]);

            $payload['deal_id'] = $activeDeal->id; 
            $payload['term_id'] = $term->id;
            $payload['term_status'] = 'pending';
        }

        $message = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'service_request_id' => $request->service_request_id,
            'message' => $request->message ?? 'Attachment',
            'type' => $request->type,
            'payload' => $payload,
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
}