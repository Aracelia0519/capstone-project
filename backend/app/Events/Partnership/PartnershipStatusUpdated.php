<?php
//SP AND OPERATIONAL
namespace App\Events\Partnership;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnershipStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distributorId;
    public $serviceProviderId;
    public $action;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($distributorId, $serviceProviderId, $action)
    {
        $this->distributorId = $distributorId;
        $this->serviceProviderId = $serviceProviderId;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to BOTH the distributor and the service provider
        return [
            new PrivateChannel('partnership.user.' . $this->distributorId),
            new PrivateChannel('partnership.user.' . $this->serviceProviderId),
        ];
    }
    
    public function broadcastAs()
    {
        return 'PartnershipStatusUpdated';
    }
}