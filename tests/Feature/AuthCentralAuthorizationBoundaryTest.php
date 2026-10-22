<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthCentralAuthorizationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authcentral_login_does_not_replace_college360_roles(): void
    {
        Role::create(['name' => 'System', 'guard_name' => 'web']);
        Role::create(['name' => 'Staff', 'guard_name' => 'web']);

        $user = User::factory()->create(['email' => 'roles@example.test']);
        $user->assignRole('System');

        app(AuthenticationService::class)->createOrUpdateAuthCentralUser([
            'name' => 'Updated by SSO',
            'email' => $user->email,
        ], ['Staff']);

        $this->assertTrue($user->fresh()->hasRole('System'));
        $this->assertFalse($user->fresh()->hasRole('Staff'));
    }

    public function test_first_time_authcentral_login_uses_college360_default_role_not_remote_role(): void
    {
        Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        Role::create(['name' => 'System', 'guard_name' => 'web']);

        $user = app(AuthenticationService::class)->createOrUpdateAuthCentralUser([
            'name' => 'New SSO User',
            'email' => 'new-sso-user@example.test',
        ], ['System']);

        $this->assertTrue($user->hasRole('Staff'));
        $this->assertFalse($user->hasRole('System'));
    }
}
