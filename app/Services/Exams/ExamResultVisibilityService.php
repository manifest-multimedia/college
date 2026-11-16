<?php

namespace App\Services\Exams;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;

class ExamResultVisibilityService
{
    public function policy(Exam $exam): array
    {
        $global = DB::table('online_exam_result_policies')->first();
        $mode = $exam->result_release_override ?? ($global && $global->enabled ? $global->release_mode : 'disabled');
        $level = $exam->result_detail_override ?? ($global?->detail_level ?? 'score');
        return ['mode' => $mode, 'level' => $level];
    }

    public function canView(ExamSession $session): bool
    {
        // Restored sessions may have a future completed_at; never publish these.
        if (! $session->completed_at || $session->completed_at->isFuture() || $session->is_restored) return false;
        $exam = $session->exam;
        $mode = $this->policy($exam)['mode'];
        return match ($mode) {
            'immediate' => true,
            'after_end' => $exam->end_date !== null && $exam->end_date->isPast(),
            'manual' => $exam->results_published_at !== null && $exam->results_published_at->isPast(),
            default => false,
        };
    }
}
