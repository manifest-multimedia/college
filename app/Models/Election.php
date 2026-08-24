<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Election extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'is_active',
        'requires_verification',
        'voting_duration_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'requires_verification' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $election): void {
            // Allows application code to be deployed before the additive
            // migration has run on an institution's database.
            if (Schema::hasColumn('elections', 'public_id') && empty($election->public_id)) {
                $election->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return Schema::hasColumn('elections', 'public_id') ? 'public_id' : 'id';
    }

    /**
     * Resolve new public links by ULID while preserving existing numeric links
     * throughout the adoption period.
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        if (Schema::hasColumn('elections', 'public_id')) {
            $election = $this->where('public_id', $value)->first();

            if ($election) {
                return $election;
            }
        }

        return ctype_digit((string) $value)
            ? $this->whereKey($value)->first()
            : null;
    }

    /**
     * Get the positions for this election.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(ElectionPosition::class)->orderBy('display_order');
    }

    /**
     * Get the votes for this election.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class);
    }

    /**
     * Check if the election is currently open for voting.
     */
    public function isOpen(): bool
    {
        $now = now();

        return $this->is_active && $now->between($this->start_time, $this->end_time);
    }

    /**
     * Alias for isOpen() - checks if the election is active and currently in progress.
     */
    public function isActive(): bool
    {
        return $this->isOpen();
    }

    /**
     * Check if the election is upcoming (not yet started).
     */
    public function isUpcoming(): bool
    {
        return $this->is_active && now()->lessThan($this->start_time);
    }

    /**
     * Check if the election has ended.
     */
    public function hasEnded(): bool
    {
        return now()->greaterThan($this->end_time);
    }

    /**
     * Get all candidates for this election across all positions.
     */
    public function getAllCandidates()
    {
        $candidates = collect();

        foreach ($this->positions as $position) {
            $candidates = $candidates->merge($position->candidates);
        }

        return $candidates;
    }

    /**
     * Get the total number of votes cast for this election.
     */
    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }

    /**
     * Get the voter participation percentage.
     * Requires the total number of eligible voters to be passed.
     */
    public function getParticipationPercentage(int $totalEligibleVoters): float
    {
        if ($totalEligibleVoters === 0) {
            return 0;
        }

        // Count unique students who have voted
        $uniqueVoters = $this->votes()->select('student_id')->distinct()->count('student_id');

        return round(($uniqueVoters / $totalEligibleVoters) * 100, 2);
    }
}
