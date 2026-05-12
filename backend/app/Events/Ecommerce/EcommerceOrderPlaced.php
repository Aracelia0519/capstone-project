<?php

namespace App\Events\Ecommerce;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EcommerceOrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distributorId;
    public $orderData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($distributorId, $orderData = [])
    {
        $this->distributorId = $distributorId;
        $this->orderData = $orderData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        
        // 1. Broadcast to the Global Admin Order Channel
        $channels[] = new PrivateChannel('admin.orders');

        // 2. Broadcast to the specific Distributor's Order Channel
        if ($this->distributorId) {
            $channels[] = new PrivateChannel('distributor.' . $this->distributorId . '.orders');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     * Keeps the frontend listener intact even if we changed the PHP class name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'order.placed';
    }
}