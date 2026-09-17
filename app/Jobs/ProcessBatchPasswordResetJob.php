<?php

namespace App\Jobs;

use App\Services\PasswordResetManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBatchPasswordResetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $targetType;
    public mixed $targetData;
    public array $options;
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(string $targetType, mixed $targetData, array $options = [])
    {
        $this->targetType = $targetType;
        $this->targetData = $targetData;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(PasswordResetManagementService $service): void
    {
        Log::info("Starting batch password reset job: {$this->targetType}", [
            'initiated_by' => $this->options['initiated_by'] ?? null,
            'target_type' => $this->targetType,
        ]);

        $summary = match ($this->targetType) {
            'students_bulk' => $service->resetStudentsBulk((array) $this->targetData, $this->options),
            'cohort' => $service->resetCohort((int) $this->targetData, $this->options),
            'all_students' => $service->resetAllStudents($this->options),
            'staff_bulk' => $service->resetStaffBulk((array) $this->targetData, $this->options),
            'all_staff' => $service->resetAllStaff($this->options),
            default => ['error' => 'Unknown target type'],
        };

        Log::info("Completed batch password reset job: {$this->targetType}", [
            'summary' => $summary,
        ]);
    }
}
