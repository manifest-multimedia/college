<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['question_id', 'text'];

    /**
     * Question to which this option belongs.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
