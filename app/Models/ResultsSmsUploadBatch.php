<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ResultsSmsUploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id', 'uploaded_by', 'confirmed_by', 'original_filename', 'stored_path', 'file_hash', 'file_extension',
        'status', 'total_rows', 'ready_rows', 'sent_rows', 'failed_rows', 'skipped_rows', 'pending_review_rows',
        'missing_student_rows', 'missing_number_rows', 'duplicate_id_rows', 'validated_at', 'confirmed_at', 'completed_at', 'failure_reason',
    ];

    protected $casts = [
        'original_filename' => 'encrypted',
        'failure_reason' => 'encrypted',
        'validated_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $batch): void {
            $batch->public_id ??= (string) Str::uuid();
        });
    }

    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function rows(): HasMany { return $this->hasMany(ResultsSmsUploadRow::class, 'batch_id'); }
}
