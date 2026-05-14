<?php

namespace App\Events\Requirements;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequirementStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $status;
    public $reason;

    public function __construct($userId, $status, $reason = null)
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId . '.requirements');
    }

    public function broadcastAs()
    {
        return 'RequirementStatusUpdated';
    }
}