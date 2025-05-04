<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectionPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'name',
        'description',
        'max_selections',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the election that this position belongs to.
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the candidates for this position.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class);
    }

    /**
     * Get the votes for this position.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class);
    }

    /**
     * Get max_votes_allowed attribute (maps to max_selections column)
     */
    public function getMaxVotesAllowedAttribute()
    {
        return $this->max_selections;
    }

    /**
     * Set max_votes_allowed attribute (maps to max_selections column)
     */
    public function setMaxVotesAllowedAttribute($value)
    {
        $this->attributes['max_selections'] = $value;
    }

    /**
     * Get the total number of votes for this position.
     */
    public function getTotalVotes(): int
    {
        return $this->votes()->count();
    }

    /**
     * Get the candidate with the most votes.
     */
    public function getLeadingCandidate()
    {
        $candidateVotes = $this->votes()
            ->select('election_candidate_id')
            ->selectRaw('COUNT(*) as vote_count')
            ->groupBy('election_candidate_id')
            ->orderByDesc('vote_count')
            ->first();
            
        if ($candidateVotes) {
            return ElectionCandidate::find($candidateVotes->election_candidate_id);
        }
        
        return null;
    }

    /**
     * Get vote counts for all candidates in this position.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getCandidateVoteCounts()
    {
        $results = $this->votes()
            ->select('election_candidate_id')
            ->selectRaw('COUNT(*) as vote_count')
            ->groupBy('election_candidate_id')
            ->get()
            ->pluck('vote_count', 'election_candidate_id')
            ->toArray();
            
        // Make sure all candidates have an entry, even those with zero votes
        $completeResults = collect();
        
        foreach ($this->candidates as $candidate) {
            $completeResults[$candidate->id] = [
                'candidate' => $candidate,
                'votes' => $results[$candidate->id] ?? 0
            ];
        }
        
        return $completeResults->sortByDesc(function($item) {
            return $item['votes'];
        });
    }
}