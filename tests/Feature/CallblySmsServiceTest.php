<?php

namespace Tests\Feature;

use App\Livewire\Settings\SmsProviderSettings;
use App\Models\User;
use App\Services\Communication\SMS\CallblySmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CallblySmsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.callbly.api_token' => null,
            'services.callbly.sender_name' => 'College360',
            'services.callbly.enabled' => true,
        ]);
    }

    public function test_api_token_resolves_from_database_when_encrypted(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'sms.callbly.api_token',
            'value' => Crypt::encryptString('db-secret-token-12345'),
            'type' => 'secret',
            'is_active' => true,
            'group' => 'sms_callbly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(CallblySmsService::class);

        Http::fake([
            'https://callbly.com/api/v1/sms/balance' => Http::response(['success' => true, 'data' => ['balance' => 500]], 200),
            'https://callbly.com/api/v1/wallet/balance' => Http::response(['success' => true, 'data' => ['balance' => 25.50, 'currency' => 'GHS']], 200),
        ]);

        $balances = $service->getBalances();

        $this->assertTrue($balances['success']);
        $this->assertEquals(500, $balances['sms_balance']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer db-secret-token-12345');
        });
    }

    public function test_api_token_falls_back_to_config_when_database_is_empty(): void
    {
        config(['services.callbly.api_token' => 'env-fallback-token-67890']);

        $service = app(CallblySmsService::class);

        Http::fake([
            'https://callbly.com/api/v1/sms/balance' => Http::response(['success' => true, 'data' => ['balance' => 120]], 200),
            'https://callbly.com/api/v1/wallet/balance' => Http::response(['success' => true, 'data' => ['balance' => 10.00, 'currency' => 'GHS']], 200),
        ]);

        $balances = $service->getBalances();

        $this->assertTrue($balances['success']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer env-fallback-token-67890');
        });
    }

    public function test_api_token_falls_back_to_config_when_database_encryption_is_invalid(): void
    {
        // Simulate a payload encrypted under an old/different APP_KEY
        $dummyPayload = base64_encode(json_encode([
            'iv' => base64_encode(random_bytes(16)),
            'value' => base64_encode('invalid-ciphertext'),
            'mac' => hash_hmac('sha256', 'dummy', 'wrong-key'),
        ]));

        DB::table('system_settings')->insert([
            'key' => 'sms.callbly.api_token',
            'value' => $dummyPayload,
            'type' => 'secret',
            'is_active' => true,
            'group' => 'sms_callbly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        config(['services.callbly.api_token' => 'resilient-env-token-999']);

        $service = app(CallblySmsService::class);

        Http::fake([
            'https://callbly.com/api/v1/sms/balance' => Http::response(['success' => true, 'data' => ['balance' => 88]], 200),
            'https://callbly.com/api/v1/wallet/balance' => Http::response(['success' => true, 'data' => ['balance' => 5.00, 'currency' => 'GHS']], 200),
        ]);

        $balances = $service->getBalances();

        $this->assertTrue($balances['success']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer resilient-env-token-999');
        });
    }

    public function test_api_token_sanitizes_bearer_prefix_quotes_and_whitespace(): void
    {
        config(['services.callbly.api_token' => "  \"Bearer callbly-clean-token-111\"  \n"]);

        $service = app(CallblySmsService::class);

        Http::fake([
            'https://callbly.com/api/v1/sms/balance' => Http::response(['success' => true, 'data' => ['balance' => 10]], 200),
            'https://callbly.com/api/v1/wallet/balance' => Http::response(['success' => true, 'data' => ['balance' => 2.00, 'currency' => 'GHS']], 200),
        ]);

        $service->getBalances();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer callbly-clean-token-111');
        });
    }

    public function test_get_balances_handles_401_unauthorized(): void
    {
        config(['services.callbly.api_token' => 'expired-token-401']);

        $service = app(CallblySmsService::class);

        Http::fake([
            'https://callbly.com/api/v1/sms/balance' => Http::response(['message' => 'Unauthenticated.'], 401),
            'https://callbly.com/api/v1/wallet/balance' => Http::response(['message' => 'Unauthenticated.'], 401),
        ]);

        $balances = $service->getBalances();

        $this->assertFalse($balances['success']);
        $this->assertStringContainsString('Callbly rejected the stored API token', $balances['message']);
    }

    public function test_sms_provider_settings_component_mount_detects_env_and_undecryptable_state(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        // Case 1: Configured in env
        config(['services.callbly.api_token' => 'env-token-livewire']);

        Livewire::actingAs($user)
            ->test(SmsProviderSettings::class)
            ->assertSet('hasApiToken', true)
            ->assertSet('isConfiguredInEnv', true)
            ->assertSet('tokenUndecryptable', false);

        // Case 2: Undecryptable in database
        $dummyPayload = base64_encode(json_encode([
            'iv' => base64_encode(random_bytes(16)),
            'value' => base64_encode('bad-cipher'),
            'mac' => 'bad-mac',
        ]));
        DB::table('system_settings')->updateOrInsert(['key' => 'sms.callbly.api_token'], [
            'value' => $dummyPayload,
            'is_active' => true,
            'type' => 'secret',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        config(['services.callbly.api_token' => null]);

        Livewire::actingAs($user)
            ->test(SmsProviderSettings::class)
            ->assertSet('tokenUndecryptable', true)
            ->assertSet('hasApiToken', false);
    }

    public function test_sms_provider_settings_saves_sanitized_token_and_resets_undecryptable_state(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        Livewire::actingAs($user)
            ->test(SmsProviderSettings::class)
            ->set('apiToken', '  Bearer new-callbly-token-saved-12345  ')
            ->set('senderIds', ['COLLEGE'])
            ->set('defaultSenderId', 'COLLEGE')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('hasApiToken', true)
            ->assertSet('tokenUndecryptable', false);

        $stored = DB::table('system_settings')->where('key', 'sms.callbly.api_token')->value('value');
        $this->assertNotEmpty($stored);
        $this->assertEquals('new-callbly-token-saved-12345', Crypt::decryptString($stored));
    }
}
