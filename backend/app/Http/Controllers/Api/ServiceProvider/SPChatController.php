<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\SPMessage;
use App\Models\ServiceProvider\OfficialDeal; // NEW
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class SPChatController extends Controller
{
    public function getContacts()
    {
        $providerId = Auth::id();

        $requests = ClientServiceRequest::with(['client', 'serviceOffering'])
            ->where('provider_id', $providerId)
            ->whereNotIn('status', ['pending', 'rejected'])
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
                    })->orderBy('created_at', 'desc')->first();

                $unreadCount = SPMessage::where('sender_id', $clientId)
                    ->where('receiver_id', $providerId)
                    ->where('is_read', false)
                    ->count();

                $contacts[$clientId] = [
                    'id' => $clientId,
                    'name' => $req->client->first_name . ' ' . $req->client->last_name,
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

    public function getMessages($clientId)
    {
        $providerId = Auth::id();

        SPMessage::where('sender_id', $clientId)
            ->where('receiver_id', $providerId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SPMessage::where(function($q) use ($providerId, $clientId) {
                $q->where('sender_id', $providerId)->where('receiver_id', $clientId);
            })->orWhere(function($q) use ($providerId, $clientId) {
                $q->where('sender_id', $clientId)->where('receiver_id', $providerId);
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
            'type' => 'required|in:text,request_summary,official_deal',
            'payload' => 'nullable|array'
        ]);

        $payload = $request->payload;

        // **NEW LOGIC: Store the Official Deal in DB before sending**
        if ($request->type === 'official_deal' && $request->service_request_id) {
            $clientReq = ClientServiceRequest::find($request->service_request_id);
            
            if ($clientReq) {
                $deal = OfficialDeal::create([
                    'client_service_request_id' => $clientReq->id,
                    'service_offering_id' => $clientReq->service_offering_id,
                    'provider_id' => Auth::id(),
                    'client_id' => $request->receiver_id,
                    'price' => $payload['price'] ?? null,
                    'preferred_date' => $payload['preferred_date'] ?? null,
                    'time_preference' => $payload['time_preference'] ?? null,
                    'contact_number' => $payload['contact_number'] ?? null,
                    'address' => $payload['address'] ?? null,
                    'description' => $payload['description'] ?? null,
                    'status' => 'pending'
                ]);

                // Attach deal tracking to payload so client can respond to it
                $payload['deal_id'] = $deal->id;
                $payload['deal_status'] = 'pending';
            }
        }

        $message = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'service_request_id' => $request->service_request_id,
            'message' => $request->message,
            'type' => $request->type,
            'payload' => $payload,
            'is_read' => false
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true, 'data' => $message]);
    }
}