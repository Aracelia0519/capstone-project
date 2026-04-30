<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $targetUserId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $targetUserId)
    {
        $this->message = $message;
        $this->targetUserId = $targetUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcasts directly to the user receiving the update
        return [
            new PrivateChannel('chat.' . $this->targetUserId)
        ];
    }

    public function broadcastAs()
    {
        return 'MessageUpdated';
    }
}