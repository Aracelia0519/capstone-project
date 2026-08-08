<?php

namespace App\Events\Notification;

use App\Models\SystemNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(SystemNotification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        // Broadcast specifically to the user who is receiving it
        return new PrivateChannel('notifications.' . $this->notification->receiver_id);
    }

    public function broadcastAs()
    {
        return 'NotificationSent';
    }
}