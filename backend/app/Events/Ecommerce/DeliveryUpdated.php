<?php

namespace App\Events\Ecommerce;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distributorId;
    public $personnelId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($distributorId, $personnelId = null)
    {
        $this->distributorId = $distributorId;
        $this->personnelId = $personnelId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel('distributor.' . $this->distributorId . '.orders'),
            new PrivateChannel('admin.orders')
        ];

        // Also broadcast specifically to the assigned personnel if applicable
        if ($this->personnelId) {
            $channels[] = new PrivateChannel('personnel.' . $this->personnelId . '.deliveries');
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'delivery.updated';
    }
}