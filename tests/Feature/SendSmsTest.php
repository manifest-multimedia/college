<?php

namespace Tests\Feature;

use App\Livewire\Communication\SendSms;
use App\Models\User;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SendSmsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Http::fake([
            'https://callbly.com/api/v1/sms/balance' => \Illuminate\Support\Facades\Http::response(['success' => true, 'data' => ['balance' => 500]], 200),
            'https://callbly.com/api/v1/wallet/balance' => \Illuminate\Support\Facades\Http::response(['success' => true, 'data' => ['balance' => 25.50, 'currency' => 'GHS']], 200),
        ]);

        Role::firstOrCreate(['name' => 'System', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('System');
        $this->actingAs($this->user);

        DB::table('system_settings')->insert([
            'key' => 'sms.callbly.sender_name',
            'value' => 'College360',
            'type' => 'string',
            'is_active' => true,
            'group' => 'sms_callbly',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_message_can_exceed_160_characters_without_validation_error(): void
    {
        $longMessage = str_repeat('A', 350); // 350 characters, across 3 SMS pages

        $smsMock = $this->createMock(SmsServiceInterface::class);
        $smsMock->expects($this->once())
            ->method('sendSingle')
            ->with('233540000000', $longMessage, $this->anything())
            ->willReturn(['success' => true, 'message' => 'SMS sent successfully']);

        $this->app->instance(SmsServiceInterface::class, $smsMock);

        $test = Livewire::test(SendSms::class)
            ->set('sendType', 'single')
            ->set('recipient', '233540000000')
            ->set('selectedSenderId', 'College360')
            ->set('message', $longMessage)
            ->call('sendSms');

        $test->assertHasNoErrors()
            ->assertSet('message', '')
            ->assertSee('SMS sent successfully');
    }

    public function test_page_count_and_character_count_calculations(): void
    {
        $component = Livewire::test(SendSms::class);

        // 160 chars -> 1 page
        $component->set('message', str_repeat('a', 160));
        $this->assertEquals(160, $component->get('characterCount'));
        $this->assertEquals(1, $component->get('pageCount'));
        $this->assertEquals(0, $component->get('remainingCharactersInPage'));

        // 161 chars -> 2 pages
        $component->set('message', str_repeat('a', 161));
        $this->assertEquals(161, $component->get('characterCount'));
        $this->assertEquals(2, $component->get('pageCount'));
        $this->assertEquals(159, $component->get('remainingCharactersInPage'));

        // 320 chars -> 2 pages
        $component->set('message', str_repeat('a', 320));
        $this->assertEquals(320, $component->get('characterCount'));
        $this->assertEquals(2, $component->get('pageCount'));
        $this->assertEquals(0, $component->get('remainingCharactersInPage'));

        // 321 chars -> 3 pages
        $component->set('message', str_repeat('a', 321));
        $this->assertEquals(321, $component->get('characterCount'));
        $this->assertEquals(3, $component->get('pageCount'));
        $this->assertEquals(159, $component->get('remainingCharactersInPage'));
    }

    public function test_estimated_credits_calculation(): void
    {
        $component = Livewire::test(SendSms::class);

        // Single recipient with 2-page message (200 characters)
        $component->set('sendType', 'single')
            ->set('recipient', '0540000000')
            ->set('message', str_repeat('x', 200));

        $this->assertEquals(1, $component->get('estimatedRecipientsCount'));
        $this->assertEquals(2, $component->get('pageCount'));
        $this->assertEquals(2, $component->get('estimatedCredits'));

        // Bulk with 3 recipients and 3-page message (350 characters)
        $component->set('sendType', 'bulk')
            ->set('recipients', ['0540000001', '0540000002', '0540000003'])
            ->set('message', str_repeat('x', 350));

        $this->assertEquals(3, $component->get('estimatedRecipientsCount'));
        $this->assertEquals(3, $component->get('pageCount'));
        $this->assertEquals(9, $component->get('estimatedCredits')); // 3 recipients * 3 pages = 9 credits
    }
}
