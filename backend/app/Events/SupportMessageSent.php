<?php
//Client and adm in Verification and conversation
namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $clientId;

    public function __construct(SupportMessage $message, $clientId)
    {
        $this->message = $message;
        $this->clientId = $clientId;
    }

    public function broadcastOn()
    {
        // Broadcasts to the specific user's support channel AND to the shared
        // admin channel so every admin screen (e.g. User Management) can update
        // its unread badges in real time.
        return [
            new PrivateChannel('support.user.' . $this->clientId),
            new PrivateChannel('admin.support'),
        ];
    }

    // ADD THIS METHOD: This tells Laravel the exact name to broadcast
    public function broadcastAs()
    {
        return 'SupportMessageSent';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->toArray()
        ];
    }
}