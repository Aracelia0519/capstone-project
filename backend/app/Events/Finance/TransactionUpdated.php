<?php
// Refund toh bro
namespace App\Events\Finance;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionUpdated implements ShouldBroadcastNow
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
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('distributor.' . $this->distributorId . '.finance'),
            new PrivateChannel('admin.finance')
        ];
    }

    public function broadcastAs()
    {
        return 'transaction.updated';
    }
}