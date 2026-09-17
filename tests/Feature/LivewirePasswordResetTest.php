<?php

namespace Tests\Feature;

use App\Livewire\Settings\UserManagement;
use App\Livewire\StudentsTableWidget;
use App\Models\Cohort;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LivewirePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'System', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Lecturer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    }

    public function test_non_system_user_cannot_open_student_password_reset(): void
    {
        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');
        $this->actingAs($lecturer);

        Livewire::test(StudentsTableWidget::class)
            ->call('openStudentPasswordReset', 'all')
            ->assertStatus(403);
    }

    public function test_system_user_can_open_and_execute_student_password_reset(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $student = Student::create([
            'student_id' => 'STU999',
            'first_name' => 'Samuel',
            'last_name' => 'Kojo',
            'email' => 'samuel@example.com',
            'mobile_number' => '0240000000',
            'status' => 'active',
        ]);

        Livewire::test(StudentsTableWidget::class)
            ->call('openStudentPasswordReset', 'individual', $student->id)
            ->assertSet('showPasswordResetModal', true)
            ->assertSet('resetTargetStudentId', $student->id)
            ->set('resetChannel', 'none')
            ->call('executeStudentPasswordReset')
            ->assertSee('reset successfully');

        $user = User::where('email', 'samuel@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->force_password_change);
    }

    public function test_non_system_user_cannot_open_staff_password_reset(): void
    {
        $lecturer = User::factory()->create();
        $lecturer->assignRole('Lecturer');
        $this->actingAs($lecturer);

        Livewire::test(UserManagement::class)
            ->call('openStaffPasswordReset', 'all')
            ->assertStatus(403);
    }

    public function test_system_user_can_open_and_execute_staff_password_reset(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System');
        $this->actingAs($admin);

        $staffUser = User::factory()->create([
            'email' => 'staff_member@example.com',
            'force_password_change' => false,
        ]);
        $staffUser->assignRole('Staff');

        Livewire::test(UserManagement::class)
            ->call('openStaffPasswordReset', 'individual', $staffUser->id)
            ->assertSet('showPasswordResetModal', true)
            ->assertSet('resetTargetUserId', $staffUser->id)
            ->set('resetChannel', 'none')
            ->call('executeStaffPasswordReset')
            ->assertSee('reset successfully');

        $staffUser->refresh();
        $this->assertTrue($staffUser->force_password_change);
    }
}
