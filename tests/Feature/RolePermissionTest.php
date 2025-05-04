<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'view finance']);
        Permission::create(['name' => 'create invoices']);
        Permission::create(['name' => 'view students']);

        // Create roles
        $adminRole = Role::create(['name' => 'Administrator']);
        $financeRole = Role::create(['name' => 'Finance Manager']);
        $studentRole = Role::create(['name' => 'Student']);
        
        // Assign permissions to roles
        $adminRole->givePermissionTo(['view dashboard', 'view finance', 'view students']);
        $financeRole->givePermissionTo(['view dashboard', 'view finance', 'create invoices']);
        $studentRole->givePermissionTo(['view dashboard']);
    }
    
    /**
     * Test role assignment during user creation.
     */
    public function test_user_role_assignment(): void
    {
        // Create a user with a role
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'Administrator'
        ]);
        
        // Mimic the behavior of AuthController/SyncUserRoles middleware
        $role = Role::where('name', $user->role)->first();
        $user->assignRole($role);
        
        // Assert that the user has the Administrator role
        $this->assertTrue($user->hasRole('Administrator'));
    }
    
    /**
     * Test that assigned roles have the correct permissions.
     */
    public function test_role_has_correct_permissions(): void
    {
        // Create a user with a Finance Manager role
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => 'Finance Manager'
        ]);
        
        // Assign the role
        $role = Role::where('name', $user->role)->first();
        $user->assignRole($role);
        
        // Assert that the user has the correct permissions
        $this->assertTrue($user->can('view finance'));
        $this->assertTrue($user->can('create invoices'));
        $this->assertFalse($user->can('view students'));
    }
    
    /**
     * Test route access based on roles and permissions.
     */
    public function test_route_access_by_role(): void
    {
        // Create an admin user
        $admin = User::factory()->create(['role' => 'Administrator']);
        $adminRole = Role::where('name', 'Administrator')->first();
        $admin->assignRole($adminRole);
        
        // Create a student user
        $student = User::factory()->create(['role' => 'Student']);
        $studentRole = Role::where('name', 'Student')->first();
        $student->assignRole($studentRole);
        
        // Test admin access to protected route
        $response = $this->actingAs($admin)->get(route('students'));
        $response->assertStatus(200);
        
        // Test student attempted access to protected route (should redirect)
        $response = $this->actingAs($student)->get(route('students'));
        $response->assertStatus(403);
    }
}
