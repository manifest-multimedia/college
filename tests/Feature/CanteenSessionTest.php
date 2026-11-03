<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CanteenSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['authentication.method' => 'regular']);
    }

    public function test_college_credentials_issue_scoped_session_and_logout_revokes_only_that_token(): void
    {
        $user = User::factory()->create();
        $other = $user->createToken('other', ['canteen:access'], now()->addHours(1));
        $login = $this->postJson('/api/v1/canteen/login', ['username' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('access', 'canteen_attendant');
        $token = $login->json('token');
        $this->assertSame(['canteen:access'], $user->tokens()->where('name', 'canteen-app')->first()->abilities);
        $this->withToken($token)->getJson('/api/v1/canteen/session')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withToken($token)->postJson('/api/v1/canteen/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $other->accessToken->id]);
        // Clear Sanctum's cached guard between simulated requests.
        app('auth')->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/canteen/session')->assertUnauthorized();
    }

    public function test_other_tokens_and_cookie_sessions_cannot_access_canteen(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('unrelated', ['other:access'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/canteen/session')->assertForbidden();
        app('auth')->forgetGuards();
        $this->actingAs($user, 'web')->withHeader('Authorization', '')->getJson('/api/v1/canteen/session')->assertForbidden();
        $this->withToken('invalid-token')->getJson('/api/v1/canteen/session')->assertForbidden();
    }

    public function test_email_linked_students_are_rejected_even_without_user_id(): void
    {
        $user = User::factory()->create();
        Student::create(['student_id' => 'PNMTC/DA/RGN/25/26/099', 'first_name' => 'Fictional', 'last_name' => 'Student', 'status' => 'active', 'email' => $user->email]);
        $this->postJson('/api/v1/canteen/login', ['username' => $user->email, 'password' => 'password'])->assertForbidden();
    }

    public function test_password_change_and_two_factor_accounts_require_college_review(): void
    {
        foreach (['force_password_change' => true, 'two_factor_confirmed_at' => now()] as $field => $value) {
            $user = User::factory()->create();
            $user->forceFill([$field => $value])->save();
            $this->postJson('/api/v1/canteen/login', ['username' => $user->email, 'password' => 'password'])->assertForbidden();
        }
    }

    public function test_eligibility_is_rechecked_after_token_issue(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('canteen-app', ['canteen:access'], now()->addHours(1))->plainTextToken;
        $user->forceFill(['force_password_change' => true])->save();
        $this->withToken($token)->getJson('/api/v1/canteen/session')->assertForbidden();
    }

    public function test_expired_tokens_are_rejected(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('canteen-app', ['canteen:access'], now()->subMinute())->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/canteen/session')->assertUnauthorized();
    }

    public function test_request_id_replay_is_scoped_to_attendant_device_index_and_method(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('canteen-app', ['canteen:access'], now()->addHours(1))->plainTextToken;
        $payload = ['index_number' => 'PNMTC/DA/RGN/25/26/999', 'verification_method' => 'manual', 'device_id' => 'test-device', 'client_request_id' => (string) Str::uuid()];
        $this->withToken($token)->postJson('/api/v1/canteen/verify', $payload)->assertOk()->assertJsonPath('replay', false);
        $this->withToken($token)->postJson('/api/v1/canteen/verify', $payload)->assertOk()->assertJsonPath('replay', true);
        foreach (['device_id' => 'other', 'index_number' => 'PNMTC/DA/RGN/25/26/888', 'verification_method' => 'ocr'] as $key => $value) {
            $this->withToken($token)->postJson('/api/v1/canteen/verify', array_replace($payload, [$key => $value]))->assertStatus(409);
        }
        app('auth')->forgetGuards();
        $other = User::factory()->create()->createToken('canteen-app', ['canteen:access'], now()->addHours(1))->plainTextToken;
        $this->withToken($other)->postJson('/api/v1/canteen/verify', $payload)->assertStatus(409);
        $this->assertDatabaseCount('canteen_verifications', 1);
    }
}
