<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CanteenAuthenticationProviderTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = 'https://authcentral.example/api/v1/college/verify-credentials';

    protected function setUp(): void
    {
        parent::setUp();
        config(['authentication.method' => 'authcentral', 'authentication.authcentral.credential_verify_url' => $this->endpoint, 'authentication.authcentral.service_key' => str_repeat('k', 64), 'authentication.authcentral.ca_file' => null]);
        Http::preventStrayRequests();
    }

    private function login(User $user, string $password = 'central-password')
    {
        return $this->postJson('/api/v1/canteen/login', ['username' => $user->email, 'password' => $password]);
    }

    public function test_authcentral_credentials_work_even_when_local_password_differs(): void
    {
        $user = User::factory()->create();
        $original = $user->password;
        Http::fake([$this->endpoint => Http::response(['authenticated' => true, 'user' => ['id' => 900, 'email' => $user->email, 'name' => 'Identity Name', 'roles' => ['Super Admin']]])]);
        $this->login($user)->assertOk()->assertJsonPath('user.id', $user->id)->assertJsonPath('access', 'canteen_attendant');
        $this->assertSame($original, $user->fresh()->password);
        $this->assertCount(0, $user->fresh()->roles);
        Http::assertSent(fn ($r) => $r->url() === $this->endpoint && $r->hasHeader('X-College-Service-Key', str_repeat('k', 64)) && $r['password'] === 'central-password');
    }

    public function test_central_rejection_never_falls_back_to_a_valid_local_password(): void
    {
        $user = User::factory()->create();
        $this->assertTrue(Hash::check('password', $user->password));
        Http::fake([$this->endpoint => Http::response([], 401)]);
        $this->login($user, 'password')->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_other_colleges_can_use_regular_auth_without_contacting_authcentral(): void
    {
        config(['authentication.method' => 'regular']);
        $user = User::factory()->create();
        $this->login($user, 'password')->assertOk();
        $this->login($user, 'wrong')->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_unavailable_provider_is_a_service_error_not_invalid_credentials(): void
    {
        $user = User::factory()->create();
        Http::fake([$this->endpoint => Http::response([], 500)]);
        $this->login($user)->assertStatus(503);
        Http::fake([$this->endpoint => Http::failedConnection()]);
        $this->login($user)->assertStatus(503);
    }

    public function test_remote_identity_must_map_to_an_existing_college_account(): void
    {
        $user = User::factory()->create();
        Http::fake([$this->endpoint => Http::response(['authenticated' => true, 'user' => ['email' => 'unregistered@example.test']])]);
        $this->login($user)->assertUnauthorized();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_malformed_redirected_and_two_factor_responses_fail_closed(): void
    {
        $user = User::factory()->create();
        Http::fake([$this->endpoint => Http::sequence()
            ->push(['authenticated' => true, 'user' => []], 200)
            ->push([], 302, ['Location' => 'https://attacker.example'])
            ->push([], 403)]);
        $this->login($user)->assertStatus(503);
        $this->login($user)->assertStatus(503);
        $this->login($user)->assertForbidden();
    }

    public function test_insecure_or_missing_provider_configuration_does_not_send_passwords(): void
    {
        $user = User::factory()->create();
        config(['authentication.authcentral.credential_verify_url' => 'http://authcentral.example/verify']);
        $this->login($user)->assertStatus(503);
        config(['authentication.authcentral.credential_verify_url' => $this->endpoint, 'authentication.authcentral.service_key' => null]);
        $this->login($user)->assertStatus(503);
        Http::assertNothingSent();
    }
}
