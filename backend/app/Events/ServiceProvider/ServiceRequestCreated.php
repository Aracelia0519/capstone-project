<?php
//ServiceRequest to bruh
namespace App\Events\ServiceProvider;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $providerId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($providerId)
    {
        $this->providerId = $providerId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcasts specifically to the assigned Service Provider
        return [
            new PrivateChannel('provider.' . $this->providerId . '.requests')
        ];
    }

    public function broadcastAs()
    {
        return 'request.created';
    }
}