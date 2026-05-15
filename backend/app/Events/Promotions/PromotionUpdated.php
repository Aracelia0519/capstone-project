<?php

namespace App\Events\Promotions;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distributorId;
    public $action; // 'created', 'approved', 'rejected'
    public $promotionName;

    public function __construct($distributorId, $action, $promotionName)
    {
        $this->distributorId = $distributorId;
        $this->action = $action;
        $this->promotionName = $promotionName;
    }

    public function broadcastOn()
    {
        // Broadcasts to the specific distributor's promotion channel
        return new PrivateChannel('distributor.' . $this->distributorId . '.promotions');
    }

    public function broadcastAs()
    {
        return 'PromotionUpdated';
    }
}