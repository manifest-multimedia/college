<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'System', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
    }

    public function test_user_with_force_password_change_is_redirected_to_change_page(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary_password'),
            'force_password_change' => true,
        ]);
        $user->assignRole('Staff');

        $response = $this->actingAs($user)->get('/portal');

        $response->assertRedirect(route('password.force-change'));
    }

    public function test_user_can_view_force_password_change_page(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary_password'),
            'force_password_change' => true,
        ]);
        $user->assignRole('Staff');

        $response = $this->actingAs($user)->get(route('password.force-change'));

        $response->assertStatus(200);
        $response->assertSee('Temporary Credentials Detected');
        $response->assertSee('New Password');
    }

    public function test_user_without_force_change_flag_is_redirected_away_from_change_page(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('normal_password'),
            'force_password_change' => false,
        ]);
        $user->assignRole('Staff');

        $response = $this->actingAs($user)->get(route('password.force-change'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_successfully_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary_password'),
            'force_password_change' => true,
        ]);
        $user->assignRole('Staff');

        $response = $this->actingAs($user)->post(route('password.force-change.update'), [
            'password' => 'MyNewSecretPassword123!',
            'password_confirmation' => 'MyNewSecretPassword123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->force_password_change);
        $this->assertTrue(Hash::check('MyNewSecretPassword123!', $user->password));

        // Subsequent visit to protected route is now allowed
        $portalResponse = $this->actingAs($user)->get('/portal');
        $portalResponse->assertStatus(200);
    }

    public function test_password_update_requires_confirmation_and_minimum_length(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary_password'),
            'force_password_change' => true,
        ]);
        $user->assignRole('Staff');

        // Mismatched confirmation
        $response = $this->actingAs($user)->post(route('password.force-change.update'), [
            'password' => 'Password12345',
            'password_confirmation' => 'DifferentPassword123',
        ]);

        $response->assertSessionHasErrors('password');
        $user->refresh();
        $this->assertTrue($user->force_password_change);

        // Too short (< 8 chars)
        $responseShort = $this->actingAs($user)->post(route('password.force-change.update'), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $responseShort->assertSessionHasErrors('password');
    }

    public function test_user_can_logout_while_force_password_change_is_active(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temporary_password'),
            'force_password_change' => true,
        ]);
        $user->assignRole('Staff');

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
