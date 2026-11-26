<?php

namespace App\Services;

use App\Jobs\GenerateCohortStudentIds;
use App\Models\Cohort;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class StudentIdRegenerationOperations
{
    public function enqueue(int $cohort, int $actor, string $mode, ?string $pattern, string $hash, int $total, ?array $selectedIds = null): string
    {
        $id = DB::transaction(function () use ($cohort, $actor, $total) {
            Cohort::query()->lockForUpdate()->findOrFail($cohort);
            if (DB::table('student_id_regeneration_operations')->where('active_cohort_id', $cohort)->exists()) {
                throw new RuntimeException('A regeneration operation is already active for this cohort.');
            }
            $id = (string) Str::uuid();
            DB::table('student_id_regeneration_operations')->insert(['id' => $id, 'cohort_id' => $cohort, 'active_cohort_id' => $cohort, 'initiated_by' => $actor, 'status' => 'queued', 'total' => $total, 'created_at' => now(), 'updated_at' => now()]);
            return $id;
        });
        try {
            GenerateCohortStudentIds::dispatch($cohort, $actor, true, $mode, $pattern, $hash, $id, $selectedIds);
        } catch (\Throwable $e) {
            $this->fail($id, 'Unable to dispatch regeneration.');
            throw $e;
        }
        return $id;
    }

    public function claim(string $id): bool
    {
        return DB::table('student_id_regeneration_operations')->where('id', $id)->where('status', 'queued')->update(['status' => 'processing', 'updated_at' => now()]) === 1;
    }

    // Called inside the same transaction as the ID changes and audit batch.
    public function complete(string $id, string $batch, int $count): void
    {
        if (DB::table('student_id_regeneration_operations')->where('id', $id)->where('status', 'processing')->update(['status' => 'completed', 'active_cohort_id' => null, 'batch_id' => $batch, 'processed' => $count, 'updated_at' => now()]) !== 1) {
            throw new RuntimeException('Regeneration operation is no longer active.');
        }
    }

    public function fail(string $id, string $message): void
    {
        if (DB::table('student_id_regeneration_operations')->where('id', $id)->whereIn('status', ['queued', 'processing'])->update(['status' => 'failed', 'active_cohort_id' => null, 'message' => $message, 'updated_at' => now()])) {
            $this->notify($id);
        }
    }

    public function notify(string $id): void
    {
        try {
            $operation = DB::table('student_id_regeneration_operations')->where('id', $id)->first();
            if (! $operation || ! in_array($operation->status, ['completed', 'failed'], true)) { return; }
            User::find($operation->initiated_by)?->notify(new SystemNotification('Student ID regeneration '.$operation->status, $operation->status === 'completed' ? 'The cohort regeneration has completed. Review ID History for the mapping.' : 'The operation failed. Review ID History for details.', null, $operation->status === 'completed' ? 'success' : 'error'));
        } catch (\Throwable $e) {
            Log::warning('Regeneration notification failed', ['operation' => $id, 'error' => $e->getMessage()]);
        }
    }
}
