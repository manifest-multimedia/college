<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenVerification extends Model
{
    public const DECISION_APPROVED = 'approved';

    public const DECISION_DENIED = 'denied';

    public const DECISION_NOT_FOUND = 'not_found';

    public const DECISION_REVIEW_REQUIRED = 'review_required';

    public const METHOD_OCR = 'ocr';

    public const METHOD_MANUAL = 'manual';

    protected $fillable = [
        'client_request_id',
        'student_id',
        'attendant_user_id',
        'device_id',
        'index_number',
        'verification_method',
        'decision',
        'is_offline',
        'reconciled_decision',
        'student_snapshot',
        'payment_percentage',
        'decision_context',
        'verified_at',
        'synced_at',
    ];

    protected $casts = [
        'is_offline' => 'boolean',
        'student_snapshot' => 'array',
        'payment_percentage' => 'decimal:2',
        'decision_context' => 'array',
        'verified_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendant_user_id');
    }
}
