<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultsSmsUploadRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 'row_number', 'student_record_id', 'student_id', 'student_id_hash', 'message', 'message_hash',
        'uploaded_status', 'status', 'safe_reason', 'masked_recipient', 'normalized_recipient', 'provider_response',
        'attempt_count', 'sent_at', 'processed_at',
    ];

    protected $casts = [
        'student_id' => 'encrypted',
        'message' => 'encrypted',
        'uploaded_status' => 'encrypted',
        'normalized_recipient' => 'encrypted',
        'provider_response' => 'encrypted:array',
        'sent_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function batch(): BelongsTo { return $this->belongsTo(ResultsSmsUploadBatch::class, 'batch_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class, 'student_record_id'); }
}
