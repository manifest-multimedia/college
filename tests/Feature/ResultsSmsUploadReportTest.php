<?php

namespace Tests\Feature;

use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultsSmsUploadReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_report_includes_utf8_bom_student_name_and_clean_recipient(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $student = Student::create([
            'student_id' => 'PNMTC/DA/RM/22/23/016',
            'first_name' => 'Alice',
            'last_name' => 'Mensah',
            'status' => 'Active',
            'mobile_number' => '0241234567',
        ]);

        $batch = ResultsSmsUploadBatch::create([
            'uploaded_by' => $admin->id,
            'original_filename' => 'results.xlsx',
            'stored_path' => 'secure/results.xlsx',
            'file_hash' => hash('sha256', 'test'),
            'file_extension' => 'xlsx',
            'status' => 'completed',
        ]);

        ResultsSmsUploadRow::create([
            'batch_id' => $batch->id,
            'row_number' => 2,
            'student_record_id' => $student->id,
            'student_id' => $student->student_id,
            'student_id_hash' => hash('sha256', $student->student_id),
            'message' => 'Result message',
            'message_hash' => hash('sha256', 'Result message'),
            'status' => 'sent',
            'masked_recipient' => '•••••••••565',
            'processed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('communication.results-sms.report', $batch->public_id));

        $response->assertOk();
        $content = $response->streamedContent();

        // Starts with UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Header includes Student Name
        $this->assertStringContainsString('Row,"Student ID","Student Name",Status,Reason,Recipient,"Processed at"', $content);

        // Contains Alice Mensah
        $this->assertStringContainsString('Alice Mensah', $content);

        // Recipient is cleaned with asterisks, not mojibake bullets
        $this->assertStringContainsString('*********565', $content);
        $this->assertStringNotContainsString('â€¢', $content);
    }
}
