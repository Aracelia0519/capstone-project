<?php

namespace App\Events\ServiceProvider;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast a payload to every accepted member of a service-provider GROUP
 * (and, via $clientIds, to the client(s) who own the job) whenever ANY
 * group-critical state changes — split proposals/approvals/revokes/locks,
 * service approvals, job locks, proof decisions. Mirrors the proven
 * Partnership message pattern (Pusher + Echo private channel).
 *
 * Frontend listens on:
 *   - provider:  echo.private('service-provider.group.{groupId}').listen('.provider.group.updated')
 *   - client:    echo.private('client.group.{groupId}').listen('.client.group.updated')
 */
class ProviderGroupJobUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupId;
    public $entityTypeible;
    public $entityId;
    public $action;
    public $payload;

    public function __construct(int $groupId, string $entityType, int $entityId, string $action, array $payload = [])
    {
        $this->groupId = $groupId;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->action = $action;
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('service-provider.group.' . $this->groupId),
            new PrivateChannel('client.group.' . $this->groupId),
        ];
    }

    public function broadcastAs()
    {
        return 'provider.group.updated';
    }
}
