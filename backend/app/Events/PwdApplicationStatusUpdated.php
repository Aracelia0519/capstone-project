<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PwdApplicationStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;
    public $rejectionReason;
    public $userId;

    public function __construct($userId, $status, $rejectionReason = null)
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pwd.application.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PwdApplicationStatusUpdated';
    }
}