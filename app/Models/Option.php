<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['question_id', 'option_text', 'is_correct'];

    /**
     * Question to which this option belongs.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
