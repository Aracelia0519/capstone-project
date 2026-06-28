<?php
//Client and adm in Verification and conversation

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportMessage;
use App\Events\SupportMessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    // Client Methods
    public function getClientMessages()
    {
        if (!Auth::check()) return response()->json(['message' => 'Unauthorized'], 401);
        
        $userId = Auth::id();
        $messages = SupportMessage::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['status' => 'success', 'messages' => $messages]);
    }

    public function sendClientMessage(Request $request)
    {
        if (!Auth::check()) return response()->json(['message' => 'Unauthorized'], 401);
        
        $request->validate(['message' => 'required|string']);
        $userId = Auth::id();

        $message = SupportMessage::create([
            'sender_id' => $userId,
            'receiver_id' => null, // Goes to Admin pool
            'message' => $request->message
        ]);

        broadcast(new SupportMessageSent($message, $userId))->toOthers();

        return response()->json(['status' => 'success', 'message_data' => $message]);
    }

    // Admin Methods
    public function getAdminMessages($userId)
    {
        if (Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);

        $messages = SupportMessage::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read when admin opens it
        SupportMessage::where('sender_id', $userId)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'messages' => $messages]);
    }

    public function sendAdminMessage(Request $request, $userId)
    {
        if (Auth::user()->role !== 'admin') return response()->json(['message' => 'Unauthorized'], 403);
        
        $request->validate(['message' => 'required|string']);
        
        $message = SupportMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $userId,
            'message' => $request->message
        ]);

        broadcast(new SupportMessageSent($message, $userId))->toOthers();

        return response()->json(['status' => 'success', 'message_data' => $message]);
    }
}