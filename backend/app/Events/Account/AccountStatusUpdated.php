<?php

namespace App\Events\Account;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $status;
    public $terminationData;

    public function __construct($userId, $status, $terminationData = null)
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->terminationData = $terminationData;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('account.status.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'AccountStatusUpdated';
    }
}