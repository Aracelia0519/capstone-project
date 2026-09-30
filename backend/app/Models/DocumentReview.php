<?php

namespace App\Models;

use App\Support\Documents\DocumentExpiry;
use App\Support\Documents\DocumentFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One renewed tracked document awaiting an admin's decision.
 *
 * @property int         $id
 * @property string      $document_key
 * @property string      $file_path
 * @property string|null $expiration_date
 * @property string      $status
 * @property string|null $rejection_reason
 * @property string|null $reviewed_at
 * @property int|null    $reviewed_by
 */
class DocumentReview extends Model
{
    protected $fillable = [
        'document_key',
        'file_path',
        'expiration_date',
        'previous_file_path',
        'previous_expiration_date',
        'status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'expiration_date'          => 'date',
        'previous_expiration_date' => 'date',
        'reviewed_at'              => 'datetime',
    ];

    /**
     * The requirements row this review belongs to.
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Absolute URL for the snapshotted file.
     *
     * Read from this row, not from the requirements row: the whole point of the
     * snapshot is that it is the file that was reviewed, which may since have
     * been replaced by a later renewal.
     */
    public function getFileUrlAttribute(): ?string
    {
        return DocumentFile::url($this->file_path);
    }

    /**
     * The front-end shape.
     *
     * @return array<string, mixed>
     */
    public function toDisplayArray(): array
    {
        return [
            'id'            => $this->id,
            'document_key'  => $this->document_key,
            'label'         => DocumentExpiry::label($this->document_key),
            'file_path'     => $this->file_path,
            'file_url'      => $this->file_url,
            'expiration_at' => $this->expiration_date?->format('Y-m-d'),
            'previous_expiration_at' => $this->previous_expiration_date?->format('Y-m-d'),
            'status'        => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at'   => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'reviewed_by'   => $this->reviewed_by,
            'submitted_at'  => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
