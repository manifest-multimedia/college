<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ElectionCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_position_id',
        'student_id',
        'name',
        'photo',
        'bio',
        'manifesto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    protected $appends = [
        'photo_url',
    ];

    /**
     * Get the position that this candidate belongs to.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(ElectionPosition::class, 'election_position_id');
    }

    /**
     * Get the student that this candidate represents.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the votes for this candidate.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class);
    }

    /**
     * Get the vote count for this candidate.
     */
    public function getVoteCount(): int
    {
        return $this->votes()->count();
    }
    
    /**
     * Get the URL for the candidate's photo.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && Storage::disk('public')->exists('candidates/' . $this->photo)) {
            return Storage::url('candidates/' . $this->photo);
        }
        
        // Return default avatar if no photo is available
        return asset('images/default-avatar.png');
    }
    
    /**
     * Get the percentage of votes this candidate has received in their position.
     */
    public function getVotePercentage(): float
    {
        $totalVotes = $this->position->getTotalVotes();
        
        if ($totalVotes === 0) {
            return 0;
        }
        
        $candidateVotes = $this->getVoteCount();
        return round(($candidateVotes / $totalVotes) * 100, 1);
    }
}