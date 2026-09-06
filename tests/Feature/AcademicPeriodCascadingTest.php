<?php

namespace Tests\Feature;

use App\Livewire\Finance\FeeStructureManager;
use App\Livewire\Finance\StudentBillingManager;
use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicPeriodCascadingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Finance Manager']);
    }

    protected function createFinanceUser(): User
    {
        $user = User::factory()->create([
            'role' => 'Finance Manager',
        ]);
        $user->assignRole('Finance Manager');

        return $user;
    }

    public function test_billing_manager_resets_semester_when_academic_year_changes(): void
    {
        $this->actingAs($this->createFinanceUser());

        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025',
            'year' => '2024',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);
        $sem1 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'semester-1-2024',
            'academic_year_id' => $year1->id,
            'sequence' => 1,
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026',
            'year' => '2025',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);
        $sem2 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'semester-1-2025',
            'academic_year_id' => $year2->id,
            'sequence' => 1,
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
        ]);

        Livewire::test(StudentBillingManager::class)
            ->set('academicYearId', $year1->id)
            ->set('semesterId', $sem1->id)
            ->assertSet('semesterId', $sem1->id)
            // Change academic year
            ->set('academicYearId', $year2->id)
            // semesterId must be reset
            ->assertSet('semesterId', '');
    }

    public function test_new_bill_validation_rejects_mismatched_academic_year_and_semester(): void
    {
        $this->actingAs($this->createFinanceUser());

        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025-test-2',
            'year' => '2024',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);
        $sem1 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'sem-1-2024-2025-test-2',
            'academic_year_id' => $year1->id,
            'sequence' => 1,
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026-test-2',
            'year' => '2025',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);

        $class = CollegeClass::create(['name' => 'Class A', 'slug' => 'class-a']);
        $student = Student::create([
            'student_id' => 'STU-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'college_class_id' => $class->id,
        ]);

        Livewire::test(StudentBillingManager::class)
            ->set('newBillStudentId', $student->id)
            ->set('newBillAcademicYearId', $year2->id)
            ->set('newBillSemesterId', $sem1->id) // Belongs to year 1, not year 2!
            ->call('createNewBill')
            ->assertHasErrors(['newBillSemesterId']);
    }

    public function test_fee_structure_validation_rejects_mismatched_academic_year_and_semester(): void
    {
        $this->actingAs($this->createFinanceUser());

        $year1 = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025-test-3',
            'year' => '2024',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);
        $sem1 = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'sem-1-2024-2025-test-3',
            'academic_year_id' => $year1->id,
            'sequence' => 1,
            'start_date' => '2024-09-01',
            'end_date' => '2025-01-31',
        ]);

        $year2 = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026-test-3',
            'year' => '2025',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);

        $class = CollegeClass::create(['name' => 'Class A', 'slug' => 'class-a']);
        $feeType = FeeType::create(['name' => 'Tuition', 'code' => 'TUI']);

        Livewire::test(FeeStructureManager::class)
            ->set('fee_type_id', $feeType->id)
            ->set('college_class_id', $class->id)
            ->set('academic_year_id', $year2->id)
            ->set('semester_id', $sem1->id) // Belongs to year 1, not year 2!
            ->set('amount', 500)
            ->set('applicable_gender', 'all')
            ->call('saveFeeStructure')
            ->assertHasErrors(['semester_id']);
    }
}
