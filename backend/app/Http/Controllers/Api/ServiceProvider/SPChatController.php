<?php

namespace App\Http\Controllers\Api\ServiceProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EcommerceClient\ClientServiceRequest;
use App\Models\ServiceProvider\SPMessage;
use App\Models\ServiceProvider\OfficialDeal;
use App\Models\ServiceProvider\OfficialPaymentTerm; 
use App\Events\MessageSent;
use App\Events\Chat\MessageUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            $serviceRequest = ClientServiceRequest::find($request->service_request_id);

            $deal = OfficialDeal::create([
                'provider_id' => Auth::id(),
                'client_id' => $request->receiver_id,
                'client_service_request_id' => $request->service_request_id,
                'service_offering_id' => $serviceRequest ? $serviceRequest->service_offering_id : null,
                'price' => str_replace(',', '', $payload['price']),
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
            broadcast(new MessageUpdated($message, $message->receiver_id))->toOthers();
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

        broadcast(new MessageUpdated($message, $message->receiver_id))->toOthers();

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------------
    // VICE-VERSA NEGOTIATION SUPPORT
    // Mirrors ClientChatController::respondToDeal() / respondToPaymentTerm(),
    // but for when the Service Provider is the one responding to an Official
    // Deal / Payment Term that the Client sent. Same DB writes, same events,
    // same payload shape as the Client-side flow - just the responder role
    // is reversed.
    // ------------------------------------------------------------------

    public function respondToDeal(Request $request, int $dealId)
    {
        $request->validate([
            'action' => 'required|in:agree,decline',
            'message_id' => 'required|exists:sp_messages,id'
        ]);

        $deal = OfficialDeal::findOrFail($dealId);
        $chatMessage = SPMessage::findOrFail($request->message_id);
        $serviceRequest = ClientServiceRequest::findOrFail($deal->client_service_request_id);

        if ($request->action === 'agree') {
            $deal->update(['status' => 'ongoing']);
            $serviceRequest->update(['status' => 'ongoing']);
        } else {
            $deal->update(['status' => 'declined']);
            $serviceRequest->update(['status' => 'pending']);
        }

        $payload = $chatMessage->payload;
        $payload['deal_status'] = $deal->status;
        $chatMessage->update(['payload' => $payload]);

        broadcast(new MessageUpdated($chatMessage, $chatMessage->sender_id))->toOthers();

        $replyText = $request->action === 'agree' 
            ? 'I have accepted the official deal! We can proceed with the next steps.' 
            : 'I have declined the official deal. Please adjust the offer.';

        $replyMsg = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $deal->client_id,
            'service_request_id' => $chatMessage->service_request_id,
            'message' => $replyText,
            'type' => 'text',
            'is_read' => false
        ]);

        broadcast(new MessageSent($replyMsg))->toOthers();

        return response()->json(['success' => true, 'updated_message' => clone $chatMessage]);
    }

    public function respondToPaymentTerm(Request $request, int $termId)
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

        broadcast(new MessageUpdated($chatMessage, $chatMessage->sender_id))->toOthers();

        $replyText = $request->action === 'agree' 
            ? 'I have agreed to the payment terms.' 
            : 'I have declined the payment terms. Let\'s negotiate.';

        $replyMsg = SPMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $term->client_id,
            'service_request_id' => $chatMessage->service_request_id,
            'message' => $replyText,
            'type' => 'text',
            'is_read' => false
        ]);

        broadcast(new MessageSent($replyMsg))->toOthers();

        // ------------------------------------------------------------------
        // PWD Verification & Invoice generation triggers when the Payment
        // Term is agreed to - same process as the Client-side flow, keyed
        // off the deal's client_id so it always applies to the actual
        // client on the deal (regardless of who initiated / who responded).
        // ------------------------------------------------------------------
        if ($request->action === 'agree') {
            $deal = OfficialDeal::find($term->official_deal_id);

            if ($deal) {
                // Check if the client on this deal is verified for PWD
                $pwdVerified = DB::table('pwd_applications')
                    ->where('user_id', $deal->client_id)
                    ->where('status', 'verified')
                    ->exists();

                $originalPrice = $deal->price;
                $discountAmount = 0;
                $isPwd = false;

                if ($pwdVerified) {
                    $discountAmount = $originalPrice * 0.20;
                    $newPrice = $originalPrice - $discountAmount;

                    // Apply discount logic directly to the deal and attach system note
                    $deal->update([
                        'price' => $newPrice,
                        'description' => $deal->description . "\n\n[System Note: 20% PWD Discount Applied (Deducted ₱" . number_format($discountAmount, 2) . ")]"
                    ]);
                    $isPwd = true;

                    // Automatically notify the client of the PWD discount via chat
                    $pwdMsg = SPMessage::create([
                        'sender_id' => Auth::id(), // Sent dynamically pretending it is from Provider/System
                        'receiver_id' => $deal->client_id,
                        'service_request_id' => $chatMessage->service_request_id,
                        'message' => "System Notification: You are eligible for a PWD discount! A 20% discount (₱" . number_format($discountAmount, 2) . ") has been automatically applied to your official deal.",
                        'type' => 'text',
                        'is_read' => false
                    ]);
                    broadcast(new MessageSent($pwdMsg))->toOthers();
                }

                // Generate system Invoice Payload
                $invoicePayload = [
                    'deal_id' => $deal->id,
                    'original_price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $deal->price,
                    'payment_method' => $term->payment_method,
                    'payment_term' => $term->payment_term,
                    'is_pwd' => $isPwd
                ];

                // Send invoice automatically into the chat
                $invoiceMsg = SPMessage::create([
                    'sender_id' => Auth::id(),
                    'receiver_id' => $deal->client_id,
                    'service_request_id' => $chatMessage->service_request_id,
                    'message' => 'System Invoice Generated',
                    'type' => 'invoice',
                    'payload' => $invoicePayload,
                    'is_read' => false
                ]);
                broadcast(new MessageSent($invoiceMsg))->toOthers();
            }
        }

        return response()->json(['success' => true, 'updated_message' => clone $chatMessage]);
    }
}