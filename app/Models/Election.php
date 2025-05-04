<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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