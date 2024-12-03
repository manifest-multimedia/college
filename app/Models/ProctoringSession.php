<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProctoringSession extends Model
{
    protected $fillable = [
        'user_id',
        'exam_id',
        'ip_address',
        'user_agent',
        'proctor_id',
        'started_at',
        'ended_at',
        'flagged',
        'report',
    ];
}
