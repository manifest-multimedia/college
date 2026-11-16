<?php

namespace App\Services\TutorEvaluations;

use App\Models\TutorEvaluationCampaign;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /** Institutional totals and privacy-safe rating trends across campaigns. */
    public function overview($campaigns): array
    {
        $trends = [];
        foreach ($campaigns as $campaign) {
            $report = $this->report($campaign);
            $scores = [];
            foreach ($report['details'] as $questions) {
                foreach ($questions as $question) {
                    if ($question['type'] === 'rating' && isset($question['average']) && $question['average'] !== null) {
                        $scores[] = ['value' => $question['average'], 'count' => $question['count']];
                    }
                }
            }
            $weight = array_sum(array_column($scores, 'count'));
            $trends[] = [
                'campaign' => $campaign->name,
                'semester' => $campaign->semester?->name,
                'academic_year' => $campaign->semester?->academicYear?->name,
                'responses' => $report['total_responses'],
                'reportable_groups' => count($report['details']),
                'average' => $weight ? round(array_sum(array_map(fn ($item) => $item['value'] * $item['count'], $scores)) / $weight, 2) : null,
            ];
        }
        return $trends;
    }

    public function report(TutorEvaluationCampaign $campaign, ?int $lecturerId = null, ?int $subjectId = null): array
    {
        $campaign->loadMissing('formVersion', 'semester.academicYear');
        $base = DB::table('tutor_evaluation_responses')->where('campaign_id', $campaign->id)
            ->when($lecturerId, fn ($q) => $q->where('lecturer_id', $lecturerId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));
        $groups = (clone $base)->select('lecturer_id', 'subject_id')->selectRaw('COUNT(*) as total')
            ->groupBy('lecturer_id', 'subject_id')->get();
        $pairs = $groups->map(function ($group) use ($campaign) {
            $count = (int) $group->total;
            $eligible = DB::table('course_registrations')->where('subject_id', $group->subject_id)
                ->where('semester_id', $campaign->semester_id)
                ->where('academic_year_id', $campaign->semester->academic_year_id)
                ->where('is_approved', true)->whereNull('rejected_at')->distinct()->count('student_id');
            return [
                'lecturer_id' => $group->lecturer_id, 'subject_id' => $group->subject_id,
                'lecturer' => DB::table('users')->where('id', $group->lecturer_id)->value('name'),
                'course' => DB::table('subjects')->where('id', $group->subject_id)->value('name'),
                'responses' => $count, 'eligible' => $eligible,
                'visible' => $count >= $campaign->report_minimum,
            ];
        })->all();
        $details = [];
        // Never combine small groups to reveal their individual answers.
        foreach ($pairs as $pair) {
            if (! $pair['visible']) continue;
            $responses = (clone $base)->where('lecturer_id', $pair['lecturer_id'])
                ->where('subject_id', $pair['subject_id'])->pluck('answers')
                ->map(fn ($json) => json_decode($json, true) ?: []);
            $questions = [];
            foreach ($campaign->formVersion->schema as $section) {
                foreach ($section['questions'] as $question) {
                    $values = $responses->pluck($question['id'])->filter(fn ($v) => $v !== null && $v !== '');
                    $type = $question['type'];
                    $item = ['label' => $question['label'], 'type' => $type, 'count' => $values->count()];
                    if ($type === 'rating') {
                        $item['average'] = $values->isNotEmpty() ? round($values->avg(), 2) : null;
                        $item['distribution'] = $values->countBy(fn ($v) => (string) $v)->all();
                    } elseif (in_array($type, ['yes_no', 'single_choice', 'multiple_choice'], true)) {
                        $item['distribution'] = $values->flatMap(fn ($v) => is_array($v) ? $v : [$v])->countBy(fn ($v) => (string) $v)->all();
                    } else {
                        // Avoid exposing individual free-text answers from small groups.
                        $item['comments'] = $values->values()->all();
                    }
                    $questions[] = $item;
                }
            }
            $details[$pair['lecturer_id'].':'.$pair['subject_id']] = $questions;
        }
        return ['pairs' => $pairs, 'details' => $details, 'total_responses' => array_sum(array_column($pairs, 'responses'))];
    }
}
