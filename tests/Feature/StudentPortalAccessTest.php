<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_student_is_denied_student_portal_access(): void
    {
        $user = $this->studentUserWithStatus('suspended');

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertForbidden();
    }

    public function test_active_student_can_access_student_portal(): void
    {
        $user = $this->studentUserWithStatus('Active');

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertOk();
    }

    private function studentUserWithStatus(string $status): User
    {
        $user = User::factory()->create(['role' => 'Student']);
        $user->assignRole(Role::firstOrCreate(['name' => 'Student']));

        Student::create([
            'student_id' => 'STU-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $user->email,
            'status' => $status,
        ]);

        return $user;
    }
}
