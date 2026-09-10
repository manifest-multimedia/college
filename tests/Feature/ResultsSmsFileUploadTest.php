<?php

namespace Tests\Feature;

use App\Livewire\Communication\ResultsSmsFileUpload;
use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Models\Student;
use App\Models\User;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultsSmsFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    public function test_component_renders_batch_and_opens_rectify_modal(): void
    {
        $student = Student::create([
            'student_id' => 'PNMTC/DA/RGN/23/24/081',
            'first_name' => 'Matilda',
            'last_name' => 'Awuni',
            'status' => 'Active',
            'mobile_number' => '59803677', // Invalid length
        ]);

        $batch = ResultsSmsUploadBatch::create([
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'results.xlsx',
            'stored_path' => 'secure/results.xlsx',
            'file_hash' => hash('sha256', 'test'),
            'file_extension' => 'xlsx',
            'status' => 'validated',
            'total_rows' => 1,
            'ready_rows' => 0,
            'skipped_rows' => 1,
            'missing_number_rows' => 1,
        ]);

        $row = ResultsSmsUploadRow::create([
            'batch_id' => $batch->id,
            'row_number' => 226,
            'student_record_id' => $student->id,
            'student_id' => $student->student_id,
            'student_id_hash' => hash('sha256', $student->student_id),
            'message' => 'Your result: Grade A',
            'message_hash' => hash('sha256', 'Your result: Grade A'),
            'status' => 'skipped',
            'safe_reason' => 'The active student has no valid mobile number.',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ResultsSmsFileUpload::class, ['batch' => $batch->public_id])
            ->assertSee('Matilda Awuni')
            ->assertSee('59803677')
            ->assertSee('The active student has no valid mobile number.')
            ->call('filterBy', 'missing_number')
            ->assertSet('rowFilter', 'missing_number')
            ->assertSee('Matilda Awuni')
            ->call('openRectifyModal', $row->id)
            ->assertSet('showRectifyModal', true)
            ->assertSet('rectifyingStudentDbId', $student->id)
            ->assertSet('rectifyingStudentName', 'Matilda Awuni')
            ->set('newMobileNumber', '0598036772')
            ->call('saveRectifiedContact')
            ->assertSet('showRectifyModal', false);

        // Verify student's mobile number was updated in DB
        $this->assertEquals('0598036772', $student->fresh()->mobile_number);

        // Verify row was re-evaluated to ready
        $this->assertEquals('ready', $row->fresh()->status);
        $this->assertEquals(1, $batch->fresh()->ready_rows);
        $this->assertEquals(0, $batch->fresh()->skipped_rows);
    }

    public function test_resume_sending_dispatches_only_queued_rows(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $batch = ResultsSmsUploadBatch::create([
            'uploaded_by' => $this->admin->id,
            'original_filename' => 'results.xlsx',
            'stored_path' => 'secure/results.xlsx',
            'file_hash' => hash('sha256', 'test'),
            'file_extension' => 'xlsx',
            'status' => 'processing',
            'total_rows' => 3,
            'ready_rows' => 0,
            'sent_rows' => 1,
            'skipped_rows' => 1,
        ]);

        $sentRow = ResultsSmsUploadRow::create([
            'batch_id' => $batch->id,
            'row_number' => 1,
            'student_id' => 'STU-1',
            'student_id_hash' => hash('sha256', 'STU-1'),
            'message' => 'Result 1',
            'message_hash' => hash('sha256', 'Result 1'),
            'status' => 'sent',
        ]);

        $queuedRow = ResultsSmsUploadRow::create([
            'batch_id' => $batch->id,
            'row_number' => 2,
            'student_id' => 'STU-2',
            'student_id_hash' => hash('sha256', 'STU-2'),
            'message' => 'Result 2',
            'message_hash' => hash('sha256', 'Result 2'),
            'status' => 'queued',
        ]);

        $skippedRow = ResultsSmsUploadRow::create([
            'batch_id' => $batch->id,
            'row_number' => 3,
            'student_id' => 'STU-3',
            'student_id_hash' => hash('sha256', 'STU-3'),
            'message' => 'Result 3',
            'message_hash' => hash('sha256', 'Result 3'),
            'status' => 'skipped',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ResultsSmsFileUpload::class, ['batch' => $batch->public_id])
            ->assertSee('Resume Sending (1 remaining)')
            ->call('resumeSending')
            ->assertSee('Resumed sending');

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendResultsSmsRow::class, function ($job) use ($queuedRow) {
            return $job->rowId === $queuedRow->id;
        });

        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SendResultsSmsRow::class, function ($job) use ($sentRow) {
            return $job->rowId === $sentRow->id;
        });

        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SendResultsSmsRow::class, function ($job) use ($skippedRow) {
            return $job->rowId === $skippedRow->id;
        });
    }
}
