<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVotingSession extends Model
{
    protected $fillable = [
        'election_id',
        'student_id',
        'started_at',
        'expires_at',
        'completed_at',
        'vote_submitted',
        'ip_address',
        'session_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'vote_submitted' => 'boolean',
    ];

    /**
     * Get the election this voting session belongs to.
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the student this voting session belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Check if the session has expired.
     */
    public function hasExpired(): bool
    {
        return now() > $this->expires_at;
    }

    /**
     * Check if the session is still valid.
     */
    public function isValid(): bool
    {
        return ! $this->vote_submitted && ! $this->hasExpired() && $this->completed_at === null;
    }

    /**
     * Mark the session as completed.
     */
    public function markAsCompleted(): self
    {
        $this->update([
            'completed_at' => now(),
            'vote_submitted' => true,
        ]);

        return $this;
    }

    /**
     * Get the remaining time in seconds.
     */
    public function getRemainingTimeInSeconds(): int
    {
        if ($this->hasExpired()) {
            return 0;
        }

        // Calculate the time remaining by getting the difference from now to the expiry time
        // Use timestamp comparison to ensure correct direction
        $now = now();
        $secondsRemaining = $this->expires_at->timestamp - $now->timestamp;

        // Ensure we don't return negative values
        return max(0, $secondsRemaining);
    }
}
