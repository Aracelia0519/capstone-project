<?php
namespace App\Events\Chat;

use App\Models\EcommerceClient\ClientServiceRequest;
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

        // Group chat messages also go out on the group channels; tag them so
        // the frontend can skip the 1:1 channel and avoid duplicates.
        $group = null;
        if (isset($this->message->service_request_id) && $this->message->service_request_id) {
            $group = ClientServiceRequest::find($this->message->service_request_id);
        }
        if ($group && $group->group_id) {
            $this->message->is_group = true;
            $this->message->group_id = (int) $group->group_id;
        } else {
            $this->message->is_group = false;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Always broadcast to the user receiving the update.
        $channels = [
            new PrivateChannel('chat.' . $this->targetUserId),
        ];

        // GROUP SERVICE CHATS: mirror the same group channels as MessageSent
        // so every accepted member + the client sees edits/status updates on
        // the shared official deal / payment term / message thread.
        $groupId = $this->message->group_id ?? null;
        if ($groupId) {
            $channels[] = new PrivateChannel('service-provider.group.' . $groupId);
            $channels[] = new PrivateChannel('client.group.' . $groupId);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'MessageUpdated';
    }
}