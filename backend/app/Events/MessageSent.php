<?php

namespace App\Events;

use App\Models\ServiceProvider\SPMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(SPMessage $message)
    {
        // Eager load sender for the frontend
        $this->message = $message->load('sender');
    }

    public function broadcastOn()
    {
        // Broadcast to the receiver's private channel
        return new PrivateChannel('chat.' . $this->message->receiver_id);
    }
    
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}