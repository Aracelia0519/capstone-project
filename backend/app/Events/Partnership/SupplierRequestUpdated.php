<?php

namespace App\Events\Partnership;

//SP AND OPERATIONAL

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $supplierId;
    public $distributorId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
        $this->supplierId = $request->supplier_id;
        $this->distributorId = $request->distributor_id;
    }

    /**
     * Get the channels the event should broadcast on.
     * We broadcast to BOTH the supplier and the distributor so both UIs update.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('supplier.' . $this->supplierId . '.requests'),
            new PrivateChannel('distributor.' . $this->distributorId . '.requests')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'SupplierRequest.Updated';
    }
}