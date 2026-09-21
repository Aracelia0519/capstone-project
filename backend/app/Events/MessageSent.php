<?php

namespace App\Events;

use App\Models\EcommerceClient\ClientServiceRequest;
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

    /** @var bool Whether this message belongs to a group service chat. */
    public $groupChannel = false;

    /** @var int|null Id of the provider group when this is a group chat message. */
    public $group_id = null;

    public function __construct(SPMessage $message)
    {
        // Eager load sender for the frontend
        $this->message = $message->load('sender');

        // Group SERVICE requests share one chat thread (client + whole group).
        // Tag the payload so the frontend can route the event to the group
        // channel listeners and skip the 1:1 channel (avoiding duplicates).
        $group = null;
        if ($this->message->service_request_id) {
            $group = ClientServiceRequest::find($this->message->service_request_id);
        }
        if ($group && $group->group_id) {
            $this->groupChannel = true;
            $this->group_id = (int) $group->group_id;
            $this->message->is_group = true;
            $this->message->group_id = (int) $group->group_id;
        } else {
            $this->message->is_group = false;
        }
    }

    public function broadcastOn()
    {
        // Always deliver to the direct receiver's private channel.
        $channels = [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];

        // GROUP SERVICE CHATS: also deliver to the group's channel so EVERY
        // accepted member of the provider group (and the client who owns the
        // job) receives the realtime message for the shared thread.
        if ($this->groupChannel && $this->group_id) {
            $channels[] = new PrivateChannel('service-provider.group.' . $this->group_id);
            $channels[] = new PrivateChannel('client.group.' . $this->group_id);
        }

        return $channels;
    }
    
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}