<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVote extends Model
{
    protected $fillable = [
        'election_id',
        'election_position_id',
        'election_candidate_id',
        'student_id',
        'ip_address',
        'user_agent',
        'vote_type',
    ];

    protected $casts = [
        'vote_type' => 'string',
    ];

    /**
     * Get the election this vote belongs to.
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the position this vote belongs to.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(ElectionPosition::class, 'election_position_id');
    }

    /**
     * Get the candidate this vote belongs to.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'election_candidate_id');
    }

    /**
     * Get the student who cast this vote.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
