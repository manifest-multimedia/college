<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMailable;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use App\Models\User;
use App\Services\Communication\SMS\SmsServiceInterface;
use App\Services\PasswordResetManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordResetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'System', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    }

    public function test_system_user_can_reset_student_password_with_existing_user(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $studentUser = User::factory()->create([
            'email' => 'student1@example.com',
            'password' => Hash::make('old_password'),
            'force_password_change' => false,
        ]);
        $studentUser->assignRole('Student');

        $student = Student::create([
            'student_id' => 'STU001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'student1@example.com',
            'mobile_number' => '0241234567',
            'status' => 'active',
            'user_id' => $studentUser->id,
        ]);

        $service = app(PasswordResetManagementService::class);
        $result = $service->resetStudent($student, [
            'channel' => 'email',
            'require_change' => true,
            'custom_password' => 'NewTempPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('NewTempPass123!', $result['temporary_password']);

        // Check user updated in database
        $studentUser->refresh();
        $this->assertTrue(Hash::check('NewTempPass123!', $studentUser->password));
        $this->assertTrue($studentUser->force_password_change);

        // Check email queued
        Mail::assertQueued(AccountCredentialsMailable::class, function ($mail) use ($studentUser) {
            return $mail->hasTo($studentUser->email) &&
                   $mail->credentialsData['password'] === 'NewTempPass123!' &&
                   $mail->credentialsData['force_change'] === true;
        });
    }

    public function test_reset_student_password_provisions_user_if_missing(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $student = Student::create([
            'student_id' => 'STU002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'janesmith@example.com',
            'mobile_number' => '0249876543',
            'status' => 'active',
        ]);

        $this->assertNull($student->user_id);

        $service = app(PasswordResetManagementService::class);
        $result = $service->resetStudent($student, [
            'channel' => 'email',
            'require_change' => true,
        ]);

        $this->assertTrue($result['success']);
        $student->refresh();
        $this->assertNotNull($student->user_id);

        $createdUser = User::find($student->user_id);
        $this->assertNotNull($createdUser);
        $this->assertEquals('janesmith@example.com', $createdUser->email);
        $this->assertTrue($createdUser->hasRole('Student'));
        $this->assertTrue($createdUser->force_password_change);
        $this->assertTrue(Hash::check($result['temporary_password'], $createdUser->password));
    }

    public function test_system_user_can_reset_staff_password(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $staff = User::factory()->create([
            'email' => 'lecturer@example.com',
            'password' => Hash::make('old_pass'),
            'force_password_change' => false,
        ]);
        $staff->assignRole('Staff');

        $service = app(PasswordResetManagementService::class);
        $result = $service->resetStaff($staff, [
            'channel' => 'email',
            'require_change' => true,
            'custom_password' => 'StaffPass@2026',
        ]);

        $this->assertTrue($result['success']);
        $staff->refresh();
        $this->assertTrue(Hash::check('StaffPass@2026', $staff->password));
        $this->assertTrue($staff->force_password_change);

        Mail::assertQueued(AccountCredentialsMailable::class, function ($mail) use ($staff) {
            return $mail->hasTo($staff->email) &&
                   $mail->credentialsData['password'] === 'StaffPass@2026';
        });
    }

    public function test_cohort_bulk_reset(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $cohort = Cohort::create([
            'name' => 'Cohort 2026',
            'slug' => 'cohort-2026',
        ]);

        $student1 = Student::create([
            'student_id' => 'STU101',
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'email' => 'alice@example.com',
            'cohort_id' => $cohort->id,
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'student_id' => 'STU102',
            'first_name' => 'Bob',
            'last_name' => 'Green',
            'email' => 'bob@example.com',
            'cohort_id' => $cohort->id,
            'status' => 'active',
        ]);

        $service = app(PasswordResetManagementService::class);
        $summary = $service->resetCohort($cohort->id, [
            'channel' => 'none',
            'require_change' => true,
        ]);

        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(2, $summary['processed']);
        $this->assertEquals(0, $summary['failed']);

        $u1 = User::where('email', 'alice@example.com')->first();
        $u2 = User::where('email', 'bob@example.com')->first();

        $this->assertNotNull($u1);
        $this->assertNotNull($u2);
        $this->assertTrue($u1->force_password_change);
        $this->assertTrue($u2->force_password_change);
    }
}
