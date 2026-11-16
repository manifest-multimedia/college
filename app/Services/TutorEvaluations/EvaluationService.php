<?php

namespace App\Services\TutorEvaluations;

use App\Models\CourseRegistration;
use App\Models\Student;
use App\Models\TutorEvaluationCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvaluationService
{
    public function assignments(Student $student, TutorEvaluationCampaign $campaign)
    {
        return DB::table('course_registrations as cr')
            ->join('subjects as s', 's.id', '=', 'cr.subject_id')
            ->join('course_lecturer as cl', 'cl.subject_id', '=', 's.id')
            ->join('users as u', 'u.id', '=', 'cl.user_id')
            ->where('cr.student_id', $student->id)
            ->where('cr.semester_id', $campaign->semester_id)
            ->where('cr.academic_year_id', $campaign->semester->academic_year_id)
            ->where('cr.is_approved', true)
            ->whereNull('cr.rejected_at')
            ->where('s.semester_id', $campaign->semester_id)
            ->when(! $campaign->target_all, fn ($query) => $query->whereExists(function ($targets) use ($campaign) {
                $targets->selectRaw('1')->from('tutor_evaluation_campaign_targets as t')
                    ->where('t.campaign_id', $campaign->id)
                    ->whereColumn('t.subject_id', 's.id')
                    ->whereColumn('t.lecturer_id', 'u.id');
            }))
            ->select('s.id as subject_id', 's.name as subject_name', 'u.id as lecturer_id', 'u.name as lecturer_name')
            ->distinct()->get();
    }

    /** Preview distinct eligible tutor/course pairs and their approved student counts. */
    public function eligiblePairs(int $semesterId)
    {
        $semester = \App\Models\Semester::findOrFail($semesterId);
        return DB::table('course_registrations as cr')
            ->join('subjects as s', 's.id', '=', 'cr.subject_id')
            ->join('course_lecturer as cl', 'cl.subject_id', '=', 's.id')
            ->join('users as u', 'u.id', '=', 'cl.user_id')
            ->where('cr.semester_id', $semester->id)
            ->where('cr.academic_year_id', $semester->academic_year_id)
            ->where('cr.is_approved', true)->whereNull('cr.rejected_at')
            ->where('s.semester_id', $semester->id)
            ->groupBy('s.id', 's.name', 'u.id', 'u.name')
            ->select('s.id as subject_id', 's.name as subject_name', 'u.id as lecturer_id', 'u.name as lecturer_name')
            ->selectRaw('COUNT(DISTINCT cr.student_id) as student_count')
            ->orderBy('s.name')->orderBy('u.name')->get();
    }

    public function submit(Student $student, TutorEvaluationCampaign $campaign, int $subjectId, int $lecturerId, array $answers): void
    {
        if (! $campaign->is_active || ($campaign->opens_at && now()->lt($campaign->opens_at)) || ($campaign->closes_at && now()->gt($campaign->closes_at))) {
            throw ValidationException::withMessages(['evaluation' => 'This evaluation is not currently open.']);
        }
        if (! $this->assignments($student, $campaign)->contains(fn ($assignment) => (int) $assignment->subject_id === $subjectId && (int) $assignment->lecturer_id === $lecturerId)) {
            throw ValidationException::withMessages(['evaluation' => 'You are not eligible to evaluate this tutor for this course.']);
        }
        $validated = app(EvaluationSchema::class)->validateAnswers($campaign->formVersion->schema, $answers);

        DB::transaction(function () use ($student, $campaign, $subjectId, $lecturerId, $validated) {
            // The database unique constraint makes concurrent duplicate attempts safe.
            $created = DB::table('tutor_evaluation_completions')->insertOrIgnore([
                'campaign_id' => $campaign->id,
                'student_id' => $student->id,
                'subject_id' => $subjectId,
                'lecturer_id' => $lecturerId,
                'completed_at' => now(),
            ]);
            if (! $created) {
                throw ValidationException::withMessages(['evaluation' => 'You have already submitted this evaluation.']);
            }
            // Critically: no student ID, user ID, completion ID or identifying metadata in the response table.
            DB::table('tutor_evaluation_responses')->insert([
                'campaign_id' => $campaign->id,
                'subject_id' => $subjectId,
                'lecturer_id' => $lecturerId,
                'answers' => json_encode($validated, JSON_THROW_ON_ERROR),
            ]);
        });
    }
}
