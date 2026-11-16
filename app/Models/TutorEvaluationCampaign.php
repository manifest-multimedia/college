<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorEvaluationCampaign extends Model
{
    protected $fillable = ['name', 'semester_id', 'form_version_id', 'opens_at', 'closes_at', 'is_active', 'is_required', 'report_minimum', 'target_all'];

    protected $casts = ['opens_at' => 'datetime', 'closes_at' => 'datetime', 'is_active' => 'boolean', 'target_all' => 'boolean', 'is_required' => 'boolean'];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function formVersion()
    {
        return $this->belongsTo(TutorEvaluationFormVersion::class, 'form_version_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('is_active', true)->where(fn ($q) => $q->whereNull('opens_at')->orWhere('opens_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', now()));
    }
}
