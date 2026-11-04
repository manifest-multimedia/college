<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairFallbackStudentIds extends Command
{
    protected $signature = 'students:repair-fallback-ids
        {--student=* : Explicit database student IDs to repair}
        {--academic-year= : Academic year used for the replacement identifiers}
        {--apply : Commit the changes; otherwise preview and roll back}';

    protected $description = 'Repair explicitly selected fallback IDs using the configured custom pattern and audit each change';

    public function handle(StudentIdGenerationService $generator): int
    {
        $ids = array_values(array_unique($this->option('student')));
        if ($ids === [] || array_filter($ids, fn ($id) => ! ctype_digit((string) $id) || (int) $id < 1)) {
            $this->error('Provide explicit positive database student IDs with --student.');

            return self::FAILURE;
        }
        $year = AcademicYear::find($this->option('academic-year'));
        if (! $year || config('branding.student_id.format') !== 'custom' || ! preg_match('/\{SEQUENCE_[34]\}/', (string) config('branding.student_id.custom_pattern'))) {
            $this->error('Select a valid academic year and configure a custom ID pattern with a sequence placeholder.');

            return self::FAILURE;
        }

        $fallbackPattern = '/^'.preg_quote((string) config('branding.student_id.institution_simple', 'STU'), '/').'[0-9]{8}$/D';
        $changes = [];
        DB::beginTransaction();
        try {
            $students = Student::whereIn('id', $ids)->orderBy('last_name')->orderBy('first_name')->orderBy('id')->lockForUpdate()->get();
            if ($students->count() !== count($ids)) {
                throw new \RuntimeException('Some selected students no longer exist. No changes committed.');
            }
            // Validate every target before generating any replacement IDs.
            foreach ($students as $student) {
                if (! preg_match($fallbackPattern, $student->student_id)) {
                    throw new \RuntimeException("Student {$student->id} does not have the expected fallback ID. No changes committed.");
                }
            }
            foreach ($students as $student) {
                $old = $student->student_id;
                $new = $generator->generateStudentId($student->first_name, $student->last_name, $student->college_class_id, $year->id);
                if ($new === $old || preg_match($fallbackPattern, $new)) {
                    throw new \RuntimeException('ID generation returned another fallback; no changes committed.');
                }
                $student->update(['student_id' => $new]);
                if ($this->option('apply')) {
                    DB::table('student_id_changes')->insert([
                        'student_id' => $student->id, 'old_student_id' => $old, 'new_student_id' => $new,
                        'changed_by' => null, 'status' => 'active',
                        'notes' => 'Targeted fallback ID repair using configured custom pattern; existing account and password preserved.',
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $changes[] = [$student->id, $old, $new];
            }
            if ($this->option('apply')) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->table(['Student record', 'Old ID', 'New ID'], $changes);
        $this->info($this->option('apply') ? count($changes).' IDs repaired. Accounts and passwords unchanged.' : 'Preview only. No changes committed.');

        return self::SUCCESS;
    }
}
