<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QuestionAttachment extends Model
{
    protected $fillable = [
        'question_id',
        'attachment_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the question that owns the attachment.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the full URL for the attachment file.
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('exams')->url($this->file_path);
    }

    /**
     * Check if the attachment is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return $this->attachment_type === 'image';
    }

    /**
     * Check if the attachment is a table.
     */
    public function getIsTableAttribute(): bool
    {
        return $this->attachment_type === 'table';
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '0 B';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
