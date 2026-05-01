<?php

namespace App\Events\Ecommerce;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clientId;
    public $spId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($clientId = null, $spId = null)
    {
        $this->clientId = $clientId;
        $this->spId = $spId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        
        // Route securely to either a regular client or a service provider acting as a client
        if ($this->clientId) {
            $channels[] = new PrivateChannel('client.' . $this->clientId . '.orders');
        }
        if ($this->spId) {
            $channels[] = new PrivateChannel('provider.' . $this->spId . '.orders');
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'order.updated';
    }
}