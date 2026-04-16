<?php

namespace Tests\Feature;

use App\Exports\PaymentSummaryExport;
use App\Livewire\Finance\FinancialReportsManager;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\FeePayment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class FinancialReportsManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_summary_excel_export_downloads_successfully(): void
    {
        [$academicYear, $semester, $program, $cohort, $user] = $this->seedFinancialReportData();
        Carbon::setTestNow(Carbon::parse('2026-04-16 10:30:45'));

        Excel::fake();
        $this->actingAs($user);

        Livewire::test(FinancialReportsManager::class)
            ->set('reportType', 'payment_summary')
            ->set('academicYearId', $academicYear->id)
            ->set('semesterId', $semester->id)
            ->set('collegeClassId', $program->id)
            ->set('cohortId', $cohort->id)
            ->set('startDate', now()->subDay()->format('Y-m-d'))
            ->set('endDate', now()->addDay()->format('Y-m-d'))
            ->set('exportFormat', 'excel')
            ->call('generateReport');

        Excel::assertDownloaded('payment_summary_20260416103045.xlsx', function ($export) {
            return $export instanceof PaymentSummaryExport;
        });

        Carbon::setTestNow();
    }

    public function test_payment_summary_is_filtered_by_cohort(): void
    {
        [$academicYear, $semester, $program, $cohortA, $user, $cohortB] = $this->seedFinancialReportData();

        $this->actingAs($user);

        $summary = Livewire::test(FinancialReportsManager::class)
            ->set('reportType', 'payment_summary')
            ->set('academicYearId', $academicYear->id)
            ->set('semesterId', $semester->id)
            ->set('collegeClassId', $program->id)
            ->set('cohortId', $cohortA->id)
            ->set('startDate', now()->subDay()->format('Y-m-d'))
            ->set('endDate', now()->addDay()->format('Y-m-d'))
            ->get('paymentSummary');

        $this->assertCount(1, $summary);
        $this->assertEquals(1, (int) $summary->first()->payment_count);
        $this->assertEquals(100.00, (float) $summary->first()->total_amount);

        $summaryForOtherCohort = Livewire::test(FinancialReportsManager::class)
            ->set('reportType', 'payment_summary')
            ->set('academicYearId', $academicYear->id)
            ->set('semesterId', $semester->id)
            ->set('collegeClassId', $program->id)
            ->set('cohortId', $cohortB->id)
            ->set('startDate', now()->subDay()->format('Y-m-d'))
            ->set('endDate', now()->addDay()->format('Y-m-d'))
            ->get('paymentSummary');

        $this->assertCount(1, $summaryForOtherCohort);
        $this->assertEquals(1, (int) $summaryForOtherCohort->first()->payment_count);
        $this->assertEquals(250.00, (float) $summaryForOtherCohort->first()->total_amount);
    }

    private function seedFinancialReportData(): array
    {
        $user = User::factory()->create();

        $academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $semester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'is_current' => true,
        ]);

        $program = CollegeClass::factory()->create();
        $cohortA = Cohort::factory()->create(['name' => 'Cohort A']);
        $cohortB = Cohort::factory()->create(['name' => 'Cohort B']);

        $studentA = Student::create([
            'student_id' => 'FIN-001',
            'first_name' => 'Alice',
            'last_name' => 'A',
            'college_class_id' => $program->id,
            'cohort_id' => $cohortA->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $studentB = Student::create([
            'student_id' => 'FIN-002',
            'first_name' => 'Bob',
            'last_name' => 'B',
            'college_class_id' => $program->id,
            'cohort_id' => $cohortB->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $billA = StudentFeeBill::create([
            'student_id' => $studentA->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'total_amount' => 1000,
            'amount_paid' => 100,
            'balance' => 900,
            'payment_percentage' => 10,
            'status' => 'partially_paid',
            'billing_date' => now(),
            'bill_reference' => 'BILL-A-001',
        ]);

        $billB = StudentFeeBill::create([
            'student_id' => $studentB->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'total_amount' => 1000,
            'amount_paid' => 250,
            'balance' => 750,
            'payment_percentage' => 25,
            'status' => 'partially_paid',
            'billing_date' => now(),
            'bill_reference' => 'BILL-B-001',
        ]);

        FeePayment::create([
            'student_fee_bill_id' => $billA->id,
            'student_id' => $studentA->id,
            'amount' => 100,
            'payment_method' => 'cash',
            'reference_number' => 'REF-A-001',
            'receipt_number' => 'RCP-A-001',
            'recorded_by' => $user->id,
            'payment_date' => now(),
        ]);

        FeePayment::create([
            'student_fee_bill_id' => $billB->id,
            'student_id' => $studentB->id,
            'amount' => 250,
            'payment_method' => 'cash',
            'reference_number' => 'REF-B-001',
            'receipt_number' => 'RCP-B-001',
            'recorded_by' => $user->id,
            'payment_date' => now(),
        ]);

        return [$academicYear, $semester, $program, $cohortA, $user, $cohortB];
    }
}
