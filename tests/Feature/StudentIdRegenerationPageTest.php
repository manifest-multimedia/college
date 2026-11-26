<?php

namespace Tests\Feature;

use App\Livewire\StudentIdRegeneration;
use App\Livewire\StudentsTableWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentIdRegenerationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_privileged_roles_can_mount_regeneration_component(): void
    {
        foreach (['System', 'Super Admin', 'Administrator', 'Academic Officer', 'Finance Manager'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user);
            $component = Livewire::test(StudentIdRegeneration::class);
            if (in_array($role, ['System', 'Super Admin'])) {
                $component->assertStatus(200)->assertSee('Regenerate cohort IDs')->call('openRegenerationHistory')->assertSet('showRegenerationHistory', true);
            } else {
                $component->assertStatus(403);
            }
        }
    }

    public function test_general_student_component_no_longer_exposes_regeneration(): void
    {
        $this->assertFalse(method_exists(StudentsTableWidget::class, 'regenerateIds'));
        $this->assertFalse(method_exists(StudentsTableWidget::class, 'restoreRegenerationBatch'));
        $this->assertFalse(method_exists(StudentsTableWidget::class, 'previewRegeneration'));
        $this->assertContains('role:System|Super Admin', app('router')->getRoutes()->getByName('students.id-regeneration')->gatherMiddleware());
    }
}
