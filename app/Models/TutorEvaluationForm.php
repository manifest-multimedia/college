<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorEvaluationForm extends Model
{
    protected $fillable = ['name', 'description', 'created_by'];

    public function versions()
    {
        return $this->hasMany(TutorEvaluationFormVersion::class, 'form_id');
    }
}
