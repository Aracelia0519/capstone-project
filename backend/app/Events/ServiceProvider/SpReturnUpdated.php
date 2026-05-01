<?php
// Product return
namespace App\Events\ServiceProvider;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpReturnUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distributorId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($distributorId)
    {
        $this->distributorId = $distributorId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcasts to both the Returns module and the Finance module simultaneously.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('distributor.' . $this->distributorId . '.returns'),
            new PrivateChannel('distributor.' . $this->distributorId . '.finance'),
            new PrivateChannel('admin.finance')
        ];
    }

    public function broadcastAs()
    {
        return 'sp.return.updated';
    }
}