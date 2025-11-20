<?php

namespace App\Jobs;

use App\Models\Cohort;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCohortStudentIds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $cohortId,
        public ?int $initiatedByUserId = null
    ) {}

    /**
     * Maximum seconds the job can run before timing out.
     */
    public int $timeout = 900; // 15 minutes

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function handle(StudentIdGenerationService $idService): void
    {
        $cohort = Cohort::find($this->cohortId);
        if (! $cohort) {
            Log::warning('GenerateCohortStudentIds: Cohort not found', [
                'cohort_id' => $this->cohortId,
            ]);

            return;
        }

        $query = Student::query()
            ->where('cohort_id', $this->cohortId)
            ->where(function ($q) {
                $q->whereNull('student_id')
                    ->orWhere('student_id', '=', '')
                    ->orWhere('student_id', 'LIKE', 'TEMP_%');
            })
            ->orderBy('id');

        $processed = 0;
        $generated = 0;
        $errors = 0;

        $query->chunk(200, function ($students) use ($idService, &$processed, &$generated, &$errors) {
            foreach ($students as $student) {
                try {
                    $processed++;
                    $newId = $idService->generateStudentId(
                        (string) $student->first_name,
                        (string) $student->last_name,
                        $student->college_class_id,
                        $student->academic_year_id
                    );
                    $student->student_id = $newId;
                    $student->save();
                    $generated++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error('GenerateCohortStudentIds: error generating ID', [
                        'student_id' => $student->id,
                        'cohort_id' => $this->cohortId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        Log::info('GenerateCohortStudentIds: completed', [
            'cohort_id' => $this->cohortId,
            'initiated_by' => $this->initiatedByUserId,
            'processed' => $processed,
            'generated' => $generated,
            'errors' => $errors,
        ]);
    }
}
