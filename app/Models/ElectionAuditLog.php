<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionAuditLog extends Model
{
    protected $fillable = [
        'election_id',
        'user_type',
        'user_id',
        'event',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the election this audit log belongs to.
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Log an event.
     */
    public static function log(
        ?Election $election,
        string $userType,
        string $userId,
        string $event,
        string $description,
        array $metadata = [],
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::create([
            'election_id' => $election?->id,
            'user_type' => $userType,
            'user_id' => $userId,
            'event' => $event,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }
}