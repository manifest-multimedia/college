<?php

namespace Tests\Feature;

use App\Livewire\Finance\CourseRegistrationManager;
use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\CourseRegistration;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseRegistrationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Year $year1;

    protected Year $year2;

    protected CollegeClass $program;

    protected Student $student;

    protected StudentFeeBill $feeBill;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'System']);
        $this->user = User::factory()->create();
        $this->user->assignRole('System');

        $this->academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'year' => 2025,
            'slug' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
            'is_current' => true,
            'is_deleted' => false,
        ]);

        $this->semester = Semester::create([
            'name' => 'First Semester',
            'slug' => 'first-semester',
            'academic_year_id' => $this->academicYear->id,
            'is_current' => true,
        ]);

        $this->year1 = Year::create(['name' => 'Year 1', 'slug' => 'year-1']);
        $this->year2 = Year::create(['name' => 'Year 2', 'slug' => 'year-2']);

        $this->program = CollegeClass::create([
            'name' => 'Test Program',
            'short_name' => 'TP',
            'slug' => 'test-program',
        ]);

        $this->student = Student::create([
            'student_id' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'college_class_id' => $this->program->id,
        ]);

        $this->feeBill = StudentFeeBill::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'total_amount' => 1000.00,
            'amount_paid' => 700.00,
            'balance' => 300.00,
            'payment_percentage' => 70.00,
            'status' => 'partially_paid',
            'billing_date' => Carbon::now(),
            'bill_reference' => 'BILL-'.strtoupper(\Illuminate\Support\Str::random(8)),
        ]);
    }

    public function test_course_registration_page_loads_for_authorized_user(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('finance.course.registration'));

        $response->assertOk();
        $response->assertSee('Course Registration Management', false);
        $response->assertSee('Year of study', false);
    }

    public function test_component_renders_with_filters(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CourseRegistrationManager::class)
            ->assertOk()
            ->assertSee('Course Registration Management', false)
            ->assertSee('Academic Year', false)
            ->assertSee('Semester', false)
            ->assertSee('Year of study', false)
            ->assertSee('Select Year', false);
    }

    public function test_available_courses_empty_without_year_selected(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->set('yearId', null);

        $component->assertSet('student.id', $this->student->id);
        $this->assertCount(0, $component->get('availableCourses'));
    }

    public function test_available_courses_filtered_by_program_semester_and_year(): void
    {
        $subjectYear1 = Subject::create([
            'name' => 'Subject Year 1',
            'course_code' => 'SY1',
            'semester_id' => $this->semester->id,
            'year_id' => $this->year1->id,
            'college_class_id' => $this->program->id,
            'credit_hours' => 3,
        ]);

        $subjectYear2 = Subject::create([
            'name' => 'Subject Year 2',
            'course_code' => 'SY2',
            'semester_id' => $this->semester->id,
            'year_id' => $this->year2->id,
            'college_class_id' => $this->program->id,
            'credit_hours' => 3,
        ]);

        $this->actingAs($this->user);

        $component = Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->set('yearId', $this->year1->id);

        $this->assertCount(1, $component->get('availableCourses'));
        $component->assertSee('Subject Year 1');
        $component->assertSee('SY1');
        $component->assertDontSee('Subject Year 2');

        $component->set('yearId', $this->year2->id);
        $this->assertCount(1, $component->get('availableCourses'));
        $component->assertSee('Subject Year 2');
        $component->assertDontSee('Subject Year 1');
    }

    public function test_register_courses_creates_registrations_and_flashes_success(): void
    {
        $subject = Subject::create([
            'name' => 'Test Subject',
            'course_code' => 'TS101',
            'semester_id' => $this->semester->id,
            'year_id' => $this->year1->id,
            'college_class_id' => $this->program->id,
            'credit_hours' => 3,
        ]);

        $this->actingAs($this->user);

        Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->set('yearId', $this->year1->id)
            ->set('selectedCourses', [$subject->id])
            ->call('registerCourses')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('course_registrations', 1);
        $registration = CourseRegistration::first();
        $this->assertSame((int) $this->student->id, (int) $registration->student_id);
        $this->assertSame((int) $subject->id, (int) $registration->subject_id);
        $this->assertSame((int) $this->academicYear->id, (int) $registration->academic_year_id);
        $this->assertSame((int) $this->semester->id, (int) $registration->semester_id);
        $this->assertFalse($registration->is_approved);
    }

    public function test_register_courses_requires_year_and_selected_courses(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->set('yearId', null)
            ->set('selectedCourses', [])
            ->call('registerCourses')
            ->assertHasErrors(['yearId', 'selectedCourses']);
    }

    public function test_fee_eligibility_message_when_bill_exists_and_paid_60_percent(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->assertSet('registrationAllowed', true)
            ->assertSee('eligible for course registration', false);
    }

    public function test_fee_eligibility_message_when_under_60_percent(): void
    {
        $this->feeBill->update(['amount_paid' => 100, 'balance' => 900, 'payment_percentage' => 10]);

        $this->actingAs($this->user);

        Livewire::test(CourseRegistrationManager::class)
            ->set('studentId', $this->student->id)
            ->set('academicYearId', $this->academicYear->id)
            ->set('semesterId', $this->semester->id)
            ->assertSet('registrationAllowed', false)
            ->assertSee('At least 60% payment is required', false);
    }
}
