<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditAcademicPeriodsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_detects_mismatches_in_dry_run_mode(): void
    {
        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025-test',
            'year' => '2024',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);
        $sem1 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'sem-1-2024-2025',
            'academic_year_id' => $year1->id,
            'sequence' => 1,
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026-test',
            'year' => '2025',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);

        $class = CollegeClass::create(['name' => 'Class A', 'slug' => 'class-a']);
        $student = Student::create([
            'student_id' => 'STU-100',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'college_class_id' => $class->id,
        ]);
        $feeType = FeeType::create(['name' => 'Tuition', 'code' => 'TUI']);

        // Create a mismatched bill: bill year is year2, but semester is sem1 (from year1)
        StudentFeeBill::create([
            'student_id' => $student->id,
            'academic_year_id' => $year2->id,
            'semester_id' => $sem1->id,
            'total_amount' => 1500.00,
            'amount_paid' => 500.00,
            'balance' => 1000.00,
            'billing_date' => now(),
            'bill_reference' => 'BILL-TEST-001',
        ]);

        $this->artisan('finance:audit-academic-periods')
            ->expectsOutputToContain('Student Fee Bills')
            ->expectsOutputToContain('DRY RUN COMPLETE: Found 1 mismatched record(s)')
            ->assertExitCode(1);
    }

    public function test_command_repairs_mismatches_and_preserves_financial_parity(): void
    {
        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025-repair',
            'year' => '2024',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);
        $sem1 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'sem-1-2024-2025-repair',
            'academic_year_id' => $year1->id,
            'sequence' => 1,
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026-repair',
            'year' => '2025',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);
        $sem2 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'sem-1-2025-2026-repair',
            'academic_year_id' => $year2->id,
            'sequence' => 1,
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
        ]);

        $class = CollegeClass::create(['name' => 'Class B', 'slug' => 'class-b']);
        $student = Student::create([
            'student_id' => 'STU-200',
            'first_name' => 'Alex',
            'last_name' => 'Smith',
            'college_class_id' => $class->id,
        ]);
        $feeType = FeeType::create(['name' => 'Hostel', 'code' => 'HST']);

        $bill = StudentFeeBill::create([
            'student_id' => $student->id,
            'academic_year_id' => $year2->id,
            'semester_id' => $sem1->id, // Mismatched!
            'total_amount' => 2000.00,
            'amount_paid' => 1200.00,
            'balance' => 800.00,
            'billing_date' => now(),
            'bill_reference' => 'BILL-TEST-002',
        ]);

        $this->artisan('finance:audit-academic-periods --repair --force')
            ->expectsOutputToContain('Repair completed successfully!')
            ->expectsOutputToContain('Financial parity check PASSED')
            ->assertExitCode(0);

        // Assert that the bill's semester was safely remapped to year2's semester
        $bill->refresh();
        $this->assertEquals($year2->id, $bill->academic_year_id);
        $this->assertEquals($sem2->id, $bill->semester_id);

        // Assert that financial values were untouched
        $this->assertEquals(2000.00, (float) $bill->total_amount);
        $this->assertEquals(1200.00, (float) $bill->amount_paid);
        $this->assertEquals(800.00, (float) $bill->balance);
    }
}
