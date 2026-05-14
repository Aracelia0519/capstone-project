<?php

namespace App\Events\Requirements;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // <-- CHANGED
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequirementSubmitted implements ShouldBroadcastNow // <-- CHANGED
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $userName;
    public $role;

    public function __construct($user)
    {
        $this->userId = $user->id;
        $this->userName = $user->first_name . ' ' . $user->last_name;
        $this->role = $user->role;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin.requirements');
    }

    public function broadcastAs()
    {
        return 'RequirementSubmitted';
    }
}