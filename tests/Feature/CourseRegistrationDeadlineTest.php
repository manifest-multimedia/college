<?php

namespace Tests\Feature;

use App\Livewire\StudentCourseRegistration;
use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\CourseRegistration;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseRegistrationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;

    protected Student $student;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected CollegeClass $collegeClass;

    protected Subject $subject;

    protected StudentFeeBill $feeBill;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Student']);
        Role::firstOrCreate(['name' => 'System']);

        $this->academicYear = AcademicYear::create([
            'name' => '2026-2027',
            'year' => 2026,
            'slug' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
            'is_deleted' => false,
        ]);

        $this->semester = Semester::create([
            'name' => 'First Semester',
            'slug' => 'first-semester',
            'academic_year_id' => $this->academicYear->id,
            'sequence' => 1,
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
            'is_current' => true,
        ]);

        $this->collegeClass = CollegeClass::create([
            'name' => 'Nursing Diploma',
            'short_name' => 'ND',
            'slug' => 'nursing-diploma',
        ]);

        $year = \App\Models\Year::create(['name' => 'Year 1', 'slug' => 'year-1']);

        $this->studentUser = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@college.test',
        ]);
        $this->studentUser->assignRole('Student');

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'student_id' => 'NUR2026001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@college.test',
            'college_class_id' => $this->collegeClass->id,
            'status' => 'active',
        ]);

        $this->subject = Subject::create([
            'name' => 'Anatomy and Physiology',
            'course_code' => 'NUR101',
            'slug' => 'nur101',
            'credit_hours' => 3,
            'semester_id' => $this->semester->id,
            'college_class_id' => $this->collegeClass->id,
            'year_id' => $year->id,
        ]);

        // Create active fee bill with 100% payment (eligible)
        $this->feeBill = StudentFeeBill::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'total_amount' => 1000.00,
            'amount_paid' => 1000.00,
            'balance' => 0.00,
            'payment_percentage' => 100.00,
            'status' => 'paid',
            'billing_date' => Carbon::now(),
            'bill_reference' => 'BILL-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'is_active' => true,
        ]);
    }

    public function test_semester_model_deadline_helper_methods(): void
    {
        // Case 1: No deadline
        $this->assertFalse($this->semester->hasRegistrationDeadline());
        $this->assertTrue($this->semester->isRegistrationStarted());
        $this->assertFalse($this->semester->isRegistrationExpired());
        $this->assertTrue($this->semester->isRegistrationOpen());
        $this->assertEquals('no_deadline', $this->semester->registration_status);

        // Case 2: Deadline in future
        $this->semester->update([
            'registration_starts_at' => now()->subDays(2),
            'registration_deadline' => now()->addDays(5),
        ]);
        $this->semester->refresh();

        $this->assertTrue($this->semester->hasRegistrationDeadline());
        $this->assertTrue($this->semester->isRegistrationStarted());
        $this->assertFalse($this->semester->isRegistrationExpired());
        $this->assertTrue($this->semester->isRegistrationOpen());
        $this->assertEquals('open', $this->semester->registration_status);

        // Case 3: Deadline in past
        $this->semester->update([
            'registration_starts_at' => now()->subDays(10),
            'registration_deadline' => now()->subHour(),
        ]);
        $this->semester->refresh();

        $this->assertTrue($this->semester->hasRegistrationDeadline());
        $this->assertTrue($this->semester->isRegistrationStarted());
        $this->assertTrue($this->semester->isRegistrationExpired());
        $this->assertFalse($this->semester->isRegistrationOpen());
        $this->assertEquals('closed', $this->semester->registration_status);

        // Case 4: Registration not yet started
        $this->semester->update([
            'registration_starts_at' => now()->addDays(2),
            'registration_deadline' => now()->addDays(10),
        ]);
        $this->semester->refresh();

        $this->assertFalse($this->semester->isRegistrationStarted());
        $this->assertFalse($this->semester->isRegistrationOpen());
        $this->assertEquals('not_started', $this->semester->registration_status);
    }

    public function test_student_can_register_when_registration_is_open(): void
    {
        $this->semester->update([
            'registration_deadline' => now()->addDays(5),
        ]);

        $this->actingAs($this->studentUser);

        Livewire::test(StudentCourseRegistration::class)
            ->assertSet('registrationAllowed', true)
            ->assertSet('deadlineExpired', false)
            ->call('toggleSubject', $this->subject->id)
            ->call('submitRegistration')
            ->assertSee('Course registration submitted successfully');

        $this->assertDatabaseHas('course_registrations', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
        ]);
    }

    public function test_student_is_blocked_when_registration_deadline_has_passed(): void
    {
        $this->semester->update([
            'registration_deadline' => now()->subMinutes(10),
        ]);

        $this->actingAs($this->studentUser);

        Livewire::test(StudentCourseRegistration::class)
            ->assertSet('registrationAllowed', false)
            ->assertSet('deadlineExpired', true)
            ->call('toggleSubject', $this->subject->id)
            ->call('submitRegistration')
            ->assertSee('Registration is closed');

        $this->assertDatabaseMissing('course_registrations', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_student_is_blocked_when_registration_window_has_not_opened(): void
    {
        $this->semester->update([
            'registration_starts_at' => now()->addDays(3),
            'registration_deadline' => now()->addDays(10),
        ]);

        $this->actingAs($this->studentUser);

        Livewire::test(StudentCourseRegistration::class)
            ->assertSet('registrationAllowed', false)
            ->call('toggleSubject', $this->subject->id)
            ->call('submitRegistration')
            ->assertSee('has not opened yet');

        $this->assertDatabaseMissing('course_registrations', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    public function test_existing_registrations_are_still_visible_after_deadline_passes(): void
    {
        // First, create a registration
        CourseRegistration::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'registered_at' => now()->subDays(5),
            'payment_percentage_at_registration' => 100,
            'is_approved' => true,
        ]);

        // Now set the deadline in the past
        $this->semester->update([
            'registration_deadline' => now()->subDay(),
        ]);

        $this->actingAs($this->studentUser);

        $component = Livewire::test(StudentCourseRegistration::class)
            ->assertSet('registrationAllowed', false)
            ->assertSet('deadlineExpired', true);

        // Verify existing registration was loaded
        $this->assertCount(1, $component->get('existingRegistrations'));
        $this->assertEquals($this->subject->id, $component->get('existingRegistrations')->first()->subject_id);
    }

    public function test_student_model_is_registration_window_open_helper(): void
    {
        $this->assertTrue($this->student->isRegistrationWindowOpen($this->semester->id));

        $this->semester->update(['registration_deadline' => now()->subDay()]);
        $this->assertFalse($this->student->isRegistrationWindowOpen($this->semester->id));

        $this->semester->update(['registration_deadline' => now()->addDays(3)]);
        $this->assertTrue($this->student->isRegistrationWindowOpen($this->semester->id));
    }

    public function test_semester_controller_stores_and_updates_registration_deadlines(): void
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('System');

        $startsAt = now()->addDays(1)->format('Y-m-d\TH:i');
        $deadline = now()->addDays(20)->format('Y-m-d\TH:i');

        $response = $this->actingAs($adminUser)->post(route('academics.semesters.store'), [
            'name' => 'Second Semester',
            'academic_year_id' => $this->academicYear->id,
            'sequence' => 2,
            'start_date' => '2027-02-01',
            'end_date' => '2027-06-30',
            'registration_starts_at' => $startsAt,
            'registration_deadline' => $deadline,
        ]);

        $response->assertRedirect(route('academics.semesters.index'));

        $this->assertDatabaseHas('semesters', [
            'name' => 'Second Semester',
            'academic_year_id' => $this->academicYear->id,
        ]);

        $created = Semester::where('name', 'Second Semester')->first();
        $this->assertNotNull($created->registration_starts_at);
        $this->assertNotNull($created->registration_deadline);

        // Test update
        $newDeadline = now()->addDays(25)->format('Y-m-d\TH:i');
        $updateResponse = $this->actingAs($adminUser)->put(route('academics.semesters.update', $created), [
            'name' => 'Second Semester Updated',
            'academic_year_id' => $this->academicYear->id,
            'sequence' => 2,
            'start_date' => '2027-02-01',
            'end_date' => '2027-06-30',
            'registration_starts_at' => $startsAt,
            'registration_deadline' => $newDeadline,
        ]);

        $updateResponse->assertRedirect(route('academics.semesters.index'));

        $created->refresh();
        $this->assertEquals('Second Semester Updated', $created->name);
    }

    public function test_student_dashboard_displays_registration_closed_when_deadline_passed(): void
    {
        $this->semester->update([
            'registration_deadline' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('Registration closed on', false);
        $response->assertSee('Closed', false);
    }
}
