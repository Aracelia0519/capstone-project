<?php

namespace App\Events\Partnership;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnershipRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $distributorId;
    public $supplierId; // Add supplier ID

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->distributorId = $request->distributor_id;
        $this->supplierId = $request->supplier_id; // Initialize it
    }

    /**
     * Get the channels the event should broadcast on.
     * We broadcast to BOTH the distributor and the supplier so both UIs update!
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('distributor.' . $this->distributorId . '.requests'),
            new PrivateChannel('supplier.' . $this->supplierId . '.requests')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'PartnershipRequest.Updated';
    }
}