<?php

namespace App\Events;

use App\Models\LoginLog;
use Illuminate\Broadcasting\PrivateChannel; // <-- Changed this
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewLoginLog implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct(LoginLog $log)
    {
        $this->log = $log;
    }

    public function broadcastOn()
    {
        // <-- Must be PrivateChannel to trigger the auth in channels.php
        return new PrivateChannel('admin.login-logs'); 
    }

    public function broadcastAs()
    {
        return 'new-login-log';
    }
}