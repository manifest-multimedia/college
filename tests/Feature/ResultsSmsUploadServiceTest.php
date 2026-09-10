<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsSmsUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_active_students_returns_keyed_students_properly(): void
    {
        $active1 = Student::create([
            'student_id' => 'PNMTC/DA/RM/22/23/016',
            'first_name' => 'Alice',
            'last_name' => 'Test',
            'status' => 'Active',
            'mobile_number' => '0241234567',
        ]);

        $active2 = Student::create([
            'student_id' => 'PNMTC/DA/RM/22/23/039',
            'first_name' => 'Bob',
            'last_name' => 'Test',
            'status' => 'Active',
            'mobile_number' => '0247654321',
        ]);

        $inactive = Student::create([
            'student_id' => 'PNMTC/DA/RM/22/23/099',
            'first_name' => 'Charlie',
            'last_name' => 'Test',
            'status' => 'Suspended',
            'mobile_number' => '0249999999',
        ]);

        $service = app(ResultsSmsUploadService::class);

        $results = $service->findActiveStudents([
            'PNMTC/DA/RM/22/23/016',
            '  PNMTC/DA/RM/22/23/039  ',
            'PNMTC/DA/RM/22/23/099',
            'NONEXISTENT',
        ]);

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('PNMTC/DA/RM/22/23/016', $results);
        $this->assertArrayHasKey('PNMTC/DA/RM/22/23/039', $results);
        $this->assertArrayNotHasKey('PNMTC/DA/RM/22/23/099', $results);
        $this->assertEquals($active1->id, $results['PNMTC/DA/RM/22/23/016']->id);
        $this->assertEquals($active2->id, $results['PNMTC/DA/RM/22/23/039']->id);
    }
}
