<?php

namespace App\Console\Commands;

use App\Models\AssessmentScore;
use App\Models\Subject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateAssessmentScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment:cleanup-duplicates
        {--course= : Filter by course name (partial match)}
        {--cohort= : Filter by cohort name (partial match)}
        {--dry-run : Run in simulation mode without making changes}
        {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Investigate and remove duplicate assessment score entries where a student has multiple scores for the same course/semester/cohort';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $courseFilter = $this->option('course');
        $cohortFilter = $this->option('cohort');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made.');
        }

        $this->newLine();
        $this->info('=== Assessment Score Duplicate Investigation ===');
        $this->newLine();

        // Step 1: Find duplicate entries (same student + course + semester + cohort)
        $this->info('Step 1: Scanning for duplicate entries...');

        $duplicatesQuery = DB::table('assessment_scores')
            ->select(
                'student_id',
                'course_id',
                'semester_id',
                'cohort_id',
                'academic_year_id',
                DB::raw('COUNT(*) as count'),
                DB::raw('GROUP_CONCAT(id ORDER BY created_at DESC) as score_ids')
            )
            ->groupBy('student_id', 'course_id', 'semester_id', 'cohort_id', 'academic_year_id')
            ->having('count', '>', 1);

        $duplicates = $duplicatesQuery->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate entries found based on student+course+semester+cohort+academic_year.');
            $this->newLine();
        } else {
            $this->warn("Found {$duplicates->count()} duplicate groups.");
            $this->showDuplicateDetails($duplicates);
        }

        // Step 2: Find courses with the same name assigned to different classes (potential misconfiguration)
        $this->newLine();
        $this->info('Step 2: Checking for same-named courses across different classes...');

        $sameNameQuery = DB::table('subjects')
            ->select('name', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id) as subject_ids'))
            ->groupBy('name')
            ->having('count', '>', 1);

        if ($courseFilter) {
            $sameNameQuery->where('name', 'LIKE', "%{$courseFilter}%");
        }

        $sameNameCourses = $sameNameQuery->get();

        if ($sameNameCourses->isNotEmpty()) {
            $this->warn("Found {$sameNameCourses->count()} course names that appear in multiple records:");
            foreach ($sameNameCourses as $course) {
                $subjects = Subject::whereIn('id', explode(',', $course->subject_ids))
                    ->with('collegeClass', 'semester')
                    ->get();

                $this->newLine();
                $this->line("  Course: <fg=yellow>{$course->name}</> (appears {$course->count} times)");
                foreach ($subjects as $subject) {
                    $scoreCount = AssessmentScore::where('course_id', $subject->id)->count();
                    $publishedCount = AssessmentScore::where('course_id', $subject->id)->where('is_published', true)->count();
                    $unpublishedCount = AssessmentScore::where('course_id', $subject->id)->where('is_published', false)->count();

                    $this->line("    - ID: {$subject->id} | Class: " . ($subject->collegeClass->name ?? 'N/A')
                        . " | Semester: " . ($subject->semester->name ?? 'N/A')
                        . " | Credit Hours: {$subject->credit_hours}"
                        . " | Scores: {$scoreCount} (Published: {$publishedCount}, Unpublished: {$unpublishedCount})");
                }
            }
        } else {
            $this->info('No same-named courses found across different records.');
        }

        // Step 3: Find students with duplicate scores for the SAME course name (even if different course_id)
        $this->newLine();
        $this->info('Step 3: Checking students with scores under same-named courses (different course IDs)...');

        $crossCourseQuery = DB::table('assessment_scores AS a1')
            ->join('assessment_scores AS a2', function ($join) {
                $join->on('a1.student_id', '=', 'a2.student_id')
                    ->on('a1.semester_id', '=', 'a2.semester_id')
                    ->on('a1.id', '<', 'a2.id')
                    ->whereColumn('a1.course_id', '!=', 'a2.course_id');
            })
            ->join('subjects AS s1', 'a1.course_id', '=', 's1.id')
            ->join('subjects AS s2', 'a2.course_id', '=', 's2.id')
            ->whereColumn('s1.name', '=', 's2.name')
            ->select(
                'a1.student_id',
                's1.name as course_name',
                'a1.id as score_1_id',
                'a1.course_id as course_1_id',
                'a1.is_published as score_1_published',
                'a2.id as score_2_id',
                'a2.course_id as course_2_id',
                'a2.is_published as score_2_published',
                'a1.semester_id'
            );

        if ($courseFilter) {
            $crossCourseQuery->where('s1.name', 'LIKE', "%{$courseFilter}%");
        }

        $crossCourseDuplicates = $crossCourseQuery->get();

        if ($crossCourseDuplicates->isNotEmpty()) {
            $this->warn("Found {$crossCourseDuplicates->count()} cross-course duplicate entries!");
            $this->newLine();

            // Group by course name for summary
            $grouped = $crossCourseDuplicates->groupBy('course_name');
            foreach ($grouped as $courseName => $entries) {
                $this->line("  Course: <fg=yellow>{$courseName}</> - {$entries->count()} students affected");

                // Show first 5 sample entries
                $sample = $entries->take(5);
                foreach ($sample as $entry) {
                    $student = DB::table('students')->where('id', $entry->student_id)->first();
                    $studentName = $student ? ($student->first_name . ' ' . $student->last_name . " ({$student->student_id})") : "Student #{$entry->student_id}";

                    $this->line("    - {$studentName}");
                    $this->line("      Score #{$entry->score_1_id} (course_id: {$entry->course_1_id}, published: " . ($entry->score_1_published ? 'YES' : 'NO') . ")");
                    $this->line("      Score #{$entry->score_2_id} (course_id: {$entry->course_2_id}, published: " . ($entry->score_2_published ? 'YES' : 'NO') . ")");
                }

                if ($entries->count() > 5) {
                    $this->line("    ... and " . ($entries->count() - 5) . " more students");
                }
            }
        } else {
            $this->info('No cross-course duplicates found.');
        }

        // Step 4: Show all unpublished assessment scores summary
        $this->newLine();
        $this->info('Step 4: Unpublished scores summary...');

        $unpublishedQuery = DB::table('assessment_scores')
            ->join('subjects', 'assessment_scores.course_id', '=', 'subjects.id')
            ->leftJoin('cohorts', 'assessment_scores.cohort_id', '=', 'cohorts.id')
            ->where('assessment_scores.is_published', false)
            ->select(
                'subjects.name as course_name',
                'subjects.id as course_id',
                'cohorts.name as cohort_name',
                DB::raw('COUNT(*) as score_count')
            )
            ->groupBy('subjects.id', 'subjects.name', 'cohorts.name');

        if ($courseFilter) {
            $unpublishedQuery->where('subjects.name', 'LIKE', "%{$courseFilter}%");
        }
        if ($cohortFilter) {
            $unpublishedQuery->where('cohorts.name', 'LIKE', "%{$cohortFilter}%");
        }

        $unpublished = $unpublishedQuery->get();

        if ($unpublished->isNotEmpty()) {
            $this->table(
                ['Course', 'Course ID', 'Cohort', 'Unpublished Scores'],
                $unpublished->map(fn ($u) => [$u->course_name, $u->course_id, $u->cohort_name ?? 'N/A', $u->score_count])
            );
        } else {
            $this->info('No unpublished scores found.');
        }

        // Step 5: Offer to delete duplicate/unpublished scores
        if (!$dryRun && ($crossCourseDuplicates->isNotEmpty() || $duplicates->isNotEmpty() || $unpublished->isNotEmpty())) {
            $this->newLine();
            $this->info('=== Cleanup Options ===');

            // Option A: Remove unpublished duplicate entries where students have the same course name
            if ($crossCourseDuplicates->isNotEmpty()) {
                $unpublishedDupes = $crossCourseDuplicates->filter(fn ($d) => !$d->score_1_published || !$d->score_2_published);

                if ($unpublishedDupes->isNotEmpty()) {
                    $idsToRemove = collect();

                    foreach ($unpublishedDupes as $dupe) {
                        // Remove the unpublished one; if both unpublished, remove the newer one
                        if (!$dupe->score_1_published && $dupe->score_2_published) {
                            $idsToRemove->push($dupe->score_1_id);
                        } elseif ($dupe->score_1_published && !$dupe->score_2_published) {
                            $idsToRemove->push($dupe->score_2_id);
                        } else {
                            // Both unpublished — remove the newer (higher ID)
                            $idsToRemove->push($dupe->score_2_id);
                        }
                    }

                    $idsToRemove = $idsToRemove->unique();

                    $this->warn("Option A: Remove {$idsToRemove->count()} unpublished duplicate cross-course scores.");

                    if ($force || $this->confirm("Proceed with removing {$idsToRemove->count()} unpublished duplicate scores?")) {
                        DB::beginTransaction();
                        try {
                            $deleted = AssessmentScore::whereIn('id', $idsToRemove->toArray())->delete();
                            DB::commit();
                            $this->info("✅ Successfully removed {$deleted} duplicate assessment scores.");
                            Log::info('Duplicate assessment scores cleaned up', [
                                'deleted_count' => $deleted,
                                'deleted_ids' => $idsToRemove->toArray(),
                                'command' => 'assessment:cleanup-duplicates',
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            $this->error("❌ Error removing scores: {$e->getMessage()}");
                            Log::error('Failed to cleanup duplicate assessment scores', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            // Option B: Remove all unpublished scores for a specific course
            if ($unpublished->isNotEmpty()) {
                $this->newLine();
                $this->warn('Option B: Remove all unpublished scores for a specific course.');
                $courseChoices = $unpublished->map(fn ($u) => "{$u->course_name} (ID: {$u->course_id}, Cohort: " . ($u->cohort_name ?? 'N/A') . ", Count: {$u->score_count})")->toArray();

                $selectedCourse = $this->choice(
                    'Which course unpublished scores should be removed? (or select "Skip")',
                    array_merge(['Skip'], $courseChoices),
                    0
                );

                if ($selectedCourse !== 'Skip') {
                    // Extract course_id from the selection
                    preg_match('/ID: (\d+)/', $selectedCourse, $matches);
                    $courseId = $matches[1] ?? null;

                    if ($courseId) {
                        $toDelete = AssessmentScore::where('course_id', $courseId)
                            ->where('is_published', false);

                        $count = $toDelete->count();

                        if ($force || $this->confirm("Delete {$count} unpublished scores for course ID {$courseId}?")) {
                            DB::beginTransaction();
                            try {
                                $deleted = $toDelete->delete();
                                DB::commit();
                                $this->info("✅ Successfully removed {$deleted} unpublished assessment scores for course ID {$courseId}.");
                                Log::info('Unpublished assessment scores removed for course', [
                                    'course_id' => $courseId,
                                    'deleted_count' => $deleted,
                                    'command' => 'assessment:cleanup-duplicates',
                                ]);
                            } catch (\Exception $e) {
                                DB::rollBack();
                                $this->error("❌ Error: {$e->getMessage()}");
                            }
                        }
                    }
                }
            }
        }

        $this->newLine();
        $this->info('=== Investigation Complete ===');

        if ($dryRun) {
            $this->comment('Re-run without --dry-run to perform cleanup actions.');
        }

        return Command::SUCCESS;
    }

    /**
     * Show details of exact duplicate groups.
     */
    private function showDuplicateDetails($duplicates)
    {
        foreach ($duplicates->take(10) as $dup) {
            $student = DB::table('students')->where('id', $dup->student_id)->first();
            $course = DB::table('subjects')->where('id', $dup->course_id)->first();
            $cohort = DB::table('cohorts')->where('id', $dup->cohort_id)->first();

            $studentName = $student ? "{$student->first_name} {$student->last_name} ({$student->student_id})" : "ID: {$dup->student_id}";
            $courseName = $course ? $course->name : "ID: {$dup->course_id}";
            $cohortName = $cohort ? $cohort->name : "ID: {$dup->cohort_id}";

            $this->line("  Student: {$studentName} | Course: {$courseName} | Cohort: {$cohortName} | Duplicates: {$dup->count}");

            $scoreIds = explode(',', $dup->score_ids);
            $scores = AssessmentScore::whereIn('id', $scoreIds)->get();
            foreach ($scores as $score) {
                $this->line("    - Score ID: {$score->id} | Total: {$score->total_score} | Published: " . ($score->is_published ? 'YES' : 'NO') . " | Created: {$score->created_at}");
            }
        }

        if ($duplicates->count() > 10) {
            $this->line("  ... and " . ($duplicates->count() - 10) . " more duplicate groups");
        }
    }
}
