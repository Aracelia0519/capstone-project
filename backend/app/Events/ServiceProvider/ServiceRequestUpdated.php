<?php

namespace App\Events\ServiceProvider;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $clientId;
    public $providerId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($clientId, $providerId)
    {
        $this->clientId = $clientId;
        $this->providerId = $providerId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcasts to BOTH the specific Client AND the Service Provider
        return [
            new PrivateChannel('client.' . $this->clientId . '.requests'),
            new PrivateChannel('provider.' . $this->providerId . '.requests')
        ];
    }

    public function broadcastAs()
    {
        return 'request.updated';
    }
}