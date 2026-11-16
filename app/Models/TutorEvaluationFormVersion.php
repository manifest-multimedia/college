<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorEvaluationFormVersion extends Model
{
    protected $fillable = ['form_id', 'version', 'schema', 'published_at'];

    protected $casts = ['schema' => 'array', 'published_at' => 'datetime'];

    public function form()
    {
        return $this->belongsTo(TutorEvaluationForm::class, 'form_id');
    }
}
