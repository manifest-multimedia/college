<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentIdGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CohortIdRegenerationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_regeneration_records_original_ids_and_actor(): void
    {
        [$cohort, $first, $second] = $this->students();
        $actor = User::factory()->create();
        config(['branding.student_id.format' => 'custom', 'branding.student_id.custom_pattern' => 'NEW/{SEQUENCE_3}', 'branding.student_id.sequence_start' => 10]);
        $result = app(StudentIdGenerationService::class)->regenerateStudentIdsForCohort($cohort->id, $actor->id);
        $this->assertSame(2, $result['success']);
        $this->assertSame('NEW/010', $first->fresh()->student_id);
        $this->assertSame('NEW/011', $second->fresh()->student_id);
        $this->assertDatabaseHas('student_id_changes', ['student_id' => $first->id, 'old_student_id' => 'OLD/001', 'new_student_id' => 'NEW/010', 'changed_by' => $actor->id]);
    }

    public function test_collision_rolls_back_entire_cohort_and_history(): void
    {
        [$cohort, $first, $second] = $this->students();
        config(['branding.student_id.format' => 'custom', 'branding.student_id.custom_pattern' => 'SAME-ID']);
        $result = app(StudentIdGenerationService::class)->regenerateStudentIdsForCohort($cohort->id);
        $this->assertSame(0, $result['success']);
        $this->assertSame(2, $result['errors']);
        $this->assertSame([], $result['updated_students']);
        $this->assertSame('OLD/001', $first->fresh()->student_id);
        $this->assertSame('OLD/002', $second->fresh()->student_id);
        $this->assertDatabaseCount('student_id_changes', 0);
    }

    public function test_preview_rolls_back_and_stale_confirmation_is_rejected(): void
    {
        [$cohort, $first, $second] = $this->students();
        config(['branding.student_id.format' => 'custom', 'branding.student_id.custom_pattern' => 'NEW/{SEQUENCE_3}']);
        $service = app(StudentIdGenerationService::class);
        $preview = $service->regenerateStudentIdsForCohort($cohort->id, preview: true);
        $this->assertSame(2, $preview['success']);
        $this->assertSame('OLD/001', $first->fresh()->student_id);
        $this->assertDatabaseCount('student_id_changes', 0);
        config(['branding.student_id.custom_pattern' => 'OTHER/{SEQUENCE_3}']);
        $result = $service->regenerateStudentIdsForCohort($cohort->id, expectedHash: $preview['hash']);
        $this->assertSame(0, $result['success']);
        $this->assertSame('OLD/002', $second->fresh()->student_id);
        $this->assertDatabaseCount('student_id_changes', 0);
    }

    public function test_regeneration_preserves_user_credentials_and_blocks_reused_ids(): void
    {
        [$cohort, $first, $second] = $this->students();
        $user = User::factory()->create(['email' => $first->email]);
        $first->user_id = $user->id;
        $first->saveQuietly();
        $password = $user->password;
        config(['branding.student_id.format' => 'custom', 'branding.student_id.custom_pattern' => 'OLD/{SEQUENCE_3}', 'branding.student_id.sequence_start' => 2]);
        $service = app(StudentIdGenerationService::class);
        $result = $service->regenerateStudentIdsForCohort($cohort->id);
        $this->assertSame(0, $result['success']);
        $this->assertSame('OLD/001', $first->fresh()->student_id);
        config(['branding.student_id.custom_pattern' => 'NEW/{SEQUENCE_3}']);
        $result = $service->regenerateStudentIdsForCohort($cohort->id);
        $this->assertSame(2, $result['success']);
        $this->assertSame($user->id, $first->fresh()->user_id);
        $this->assertSame($password, $user->fresh()->password);
    }

    public function test_batch_history_and_restoration(): void
    {
        [$cohort, $first, $second] = $this->students();
        $actor = User::factory()->create();
        config(['branding.student_id.format' => 'custom', 'branding.student_id.custom_pattern' => 'NEW/{SEQUENCE_3}']);
        $result = app(StudentIdGenerationService::class)->regenerateStudentIdsForCohort($cohort->id, $actor->id);
        $this->assertSame(2, $result['success']);
        $this->assertDatabaseHas('student_id_regeneration_batches', ['id' => $result['batch_id'], 'initiated_by' => $actor->id, 'changed_count' => 2]);
        app(\App\Services\StudentIdRegenerationHistory::class)->restore($result['batch_id'], $actor->id);
        $this->assertSame('OLD/001', $first->fresh()->student_id);
        $this->assertSame('OLD/002', $second->fresh()->student_id);
        $this->assertDatabaseHas('student_id_regeneration_batches', ['id' => $result['batch_id'], 'restored_by' => $actor->id]);
    }

    private function students(): array
    {
        $cohort = Cohort::create(['name' => 'Integrity cohort', 'slug' => 'integrity-cohort', 'academic_year' => '2026/2027']);
        $first = Student::create(['first_name' => 'Ama', 'last_name' => 'Adams', 'email' => 'ama@example.test', 'cohort_id' => $cohort->id, 'student_id' => 'OLD/001']);
        $second = Student::create(['first_name' => 'Esi', 'last_name' => 'Brown', 'email' => 'esi@example.test', 'cohort_id' => $cohort->id, 'student_id' => 'OLD/002']);
        return [$cohort, $first, $second];
    }
}
