<?php

namespace App\Events\Requirements;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * An administrator decided on one submitted document.
 *
 * The owner's channel is the important one: a rejection rewinds the date and the
 * file on their panel back to the last thing an admin accepted, so without this
 * they would keep looking at a rejected document as though it were live until
 * they reloaded the page. The decision is also the only notification the user
 * gets that their renewal was refused, so it carries the reason.
 *
 * The admin channel is on it because the same decision removes the row from the
 * review queue -- a second administrator with the list open is otherwise looking
 * at a card that can no longer be approved.
 *
 * The label is passed in rather than derived from the key because this also fires
 * for the user's extra documents, whose names are whatever they typed. The caller
 * owns the wording, and DocumentExpiry stays the only source for the two fixed
 * documents.
 */
class DocumentReviewDecided implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    /** A tracked document key, or null for a related document. */
    public $documentKey;

    public $documentLabel;

    /** @var 'approved'|'rejected' */
    public $decision;

    public $rejectionReason;

    /** @var array<string, mixed>  the full document block, as the API returns it */
    public $documents;

    public function __construct(
        int $userId,
        ?string $documentKey,
        string $documentLabel,
        string $decision,
        ?string $rejectionReason,
        array $documents
    ) {
        $this->userId          = $userId;
        $this->documentKey     = $documentKey;
        $this->documentLabel   = $documentLabel;
        $this->decision        = $decision;
        $this->rejectionReason = $rejectionReason;
        $this->documents       = $documents;
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
        return 'DocumentReviewDecided';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'user_id'          => $this->userId,
            'document_key'     => $this->documentKey,
            'document_label'   => $this->documentLabel,
            'decision'         => $this->decision,
            'rejection_reason' => $this->rejectionReason,
            'documents'        => $this->documents,
        ];
    }
}
