<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class StudentIdRegenerationHistory
{
    public function record(int $cohortId, ?int $actor, string $mode, ?string $sourcePattern, array $mapping): string
    {
        $id = (string) Str::uuid();
        DB::table('student_id_regeneration_batches')->insert([
            'id' => $id, 'cohort_id' => $cohortId, 'initiated_by' => $actor, 'mode' => $mode,
            'configuration' => json_encode(['branding' => config('branding.student_id'), 'source_pattern' => $sourcePattern], JSON_THROW_ON_ERROR),
            'mapping' => json_encode($mapping, JSON_THROW_ON_ERROR),
            'changed_count' => count(array_filter($mapping, fn ($r) => $r['old_id'] !== $r['new_id'])),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return $id;
    }

    public function restore(string $id, int $actor): void
    {
        DB::transaction(function () use ($id, $actor) {
            $batch = DB::table('student_id_regeneration_batches')->where('id', $id)->first();
            if (! $batch) { throw new RuntimeException('Batch not found.'); }
            Cohort::query()->lockForUpdate()->findOrFail($batch->cohort_id);
            $batch = DB::table('student_id_regeneration_batches')->where('id', $id)->lockForUpdate()->first();
            if ($batch->restored_at) { throw new RuntimeException('Batch already restored.'); }
            $mapping = json_decode($batch->mapping, true, 512, JSON_THROW_ON_ERROR);
            $reverse = [];
            foreach ($mapping as $row) {
                $student = Student::query()->lockForUpdate()->findOrFail($row['id']);
                if ((int) $student->cohort_id !== (int) $batch->cohort_id || $student->student_id !== $row['new_id']) {
                    throw new RuntimeException('Student records have changed since this batch. Restoration blocked.');
                }
                if ($row['old_id'] === $row['new_id']) { continue; }
                if (! filled($row['old_id'])) { throw new RuntimeException('Restoration to an unassigned ID requires manual review.'); }
                if (DB::table('students')->where('student_id', $row['old_id'])->where('id', '!=', $row['id'])->exists()) {
                    throw new RuntimeException('Original ID is already assigned. Restoration blocked.');
                }
                $reverse[] = ['id' => $row['id'], 'old_id' => $row['new_id'], 'new_id' => $row['old_id']];
            }
            app(StudentIdReferenceUpdater::class)->apply($reverse);
            foreach ($reverse as $row) {
                Student::query()->whereKey($row['id'])->update(['student_id' => $row['new_id']]);
                DB::table('student_id_changes')->where('student_id', $row['id'])->where('status', 'active')->update(['status' => 'reverted', 'updated_at' => now()]);
                DB::table('student_id_changes')->insert(['student_id' => $row['id'], 'old_student_id' => $row['old_id'], 'new_student_id' => $row['new_id'], 'changed_by' => $actor, 'status' => 'active', 'notes' => 'Restored regeneration batch '.$id, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('student_id_regeneration_batches')->where('id', $id)->update(['restored_at' => now(), 'restored_by' => $actor, 'updated_at' => now()]);
        });
    }
}
