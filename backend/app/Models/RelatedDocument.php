<?php

namespace App\Models;

use App\Support\Documents\DocumentExpiry;
use App\Support\Documents\DocumentFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A document a supplier or distributor attached beyond the fixed set, under a
 * name they chose.
 */
class RelatedDocument extends Model
{
    protected $fillable = [
        'document_name',
        'file_path',
        'expiration_date',
        'status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'reviewed_at'     => 'datetime',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFileUrlAttribute(): ?string
    {
        return DocumentFile::url($this->file_path);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        return DocumentExpiry::daysRemaining(
            $this->expiration_date?->format('Y-m-d')
        );
    }

    /**
     * The front-end shape, with the same summary vocabulary the fixed documents
     * use so one component can render both kinds.
     *
     * @return array<string, mixed>
     */
    public function toDisplayArray(): array
    {
        $expiration = $this->expiration_date?->format('Y-m-d');

        return array_merge([
            'id'            => $this->id,
            'document_name' => $this->document_name,
            'file_path'     => $this->file_path,
            'file_url'      => $this->file_url,
            'status'        => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at'   => $this->reviewed_at?->format('Y-m-d H:i:s'),
        ], DocumentExpiry::summary('related_document', $expiration), [
            // The name the user typed is the label, not the generic key.
            'label' => $this->document_name,
        ]);
    }
}
