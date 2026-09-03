<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PwdApplicationSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $fullName;

    public function __construct($userId, $fullName)
    {
        $this->userId = $userId;
        $this->fullName = $fullName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.pwd-applications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'PwdApplicationSubmitted';
    }
}