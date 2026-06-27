<?php

namespace App\Events;

use App\Models\PartnershipMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnershipMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(PartnershipMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('distributor.' . $this->message->receiver_id . '.requests'),
            new PrivateChannel('supplier.' . $this->message->receiver_id . '.requests'),
            new PrivateChannel('distributor.' . $this->message->sender_id . '.requests'),
            new PrivateChannel('supplier.' . $this->message->sender_id . '.requests'),
        ];
    }

    public function broadcastAs()
    {
        return 'PartnershipMessageSent';
    }
}