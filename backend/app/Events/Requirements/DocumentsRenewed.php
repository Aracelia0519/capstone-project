<?php

namespace App\Events\Requirements;

use App\Support\Documents\DocumentExpiry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A user replaced one of their business documents.
 *
 * Sent to the owner's own settings channel and to the admin renewals channel, so
 * the panel the user just submitted from, and the queue an administrator is
 * working through, both redraw from the pushed state rather than waiting for
 * someone to press refresh.
 *
 * The whole `documents` block travels with the event. Pushing a bare "something
 * changed" would force every subscriber to re-fetch a payload it already had,
 * and a refresh is exactly what the user is trying to stop doing -- the request
 * that fills this screen is the slow part, not the render.
 */
class DocumentsRenewed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $role;

    /** @var array<string, string>  document keys replaced by this request */
    public $renewed;

    /** @var array<int, string>  labels, for the admin's toast */
    public $labels;

    /** @var array<string, mixed>  the full document block, as the API returns it */
    public $documents;

    public function __construct(int $userId, string $role, array $renewed, array $documents)
    {
        $this->userId    = $userId;
        $this->role      = $role;
        $this->renewed   = $renewed;
        $this->labels    = array_map(fn (string $key) => DocumentExpiry::label($key), $renewed);
        $this->documents = $documents;
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId . '.requirements'),
            new PrivateChannel('admin.renewals'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DocumentsRenewed';
    }

    /**
     * The name is derived from the pushed data rather than passed in, so the
     * label the user reads and the label the admin reads cannot disagree.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->userId,
            'role'      => $this->role,
            'renewed'   => $this->renewed,
            'labels'    => $this->labels,
            'documents' => $this->documents,
        ];
    }
}
