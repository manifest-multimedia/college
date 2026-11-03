<?php

namespace Tests\Feature;

use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Models\Student;
use App\Models\User;
use App\Services\Canteen\CanteenMealLanes;
use App\Services\Canteen\CanteenMealPolicy;
use App\Services\Canteen\CanteenPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CanteenMealDevicesTest extends TestCase
{
    use RefreshDatabase;

    private const A = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

    private const B = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';

    private string $key;

    private User $staff;

    private string $token;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        $this->travelTo(CarbonImmutable::parse('2026-11-03 07:05:00', 'UTC'));
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        $this->key = str_repeat('a', 64);
        $this->staff = User::factory()->create();
        $this->token = $this->staff->createToken('test', ['canteen:access'], now()->addHours(12))->plainTextToken;
        app(CanteenPolicy::class)->setPeriod('2026-2027', 1);
        app(CanteenMealPolicy::class)->save(['enabled' => true, 'timezone' => 'Africa/Accra', 'offline_policy' => 'assigned_device', 'windows' => [
            ['meal' => 'breakfast', 'opens' => '07:00', 'closes' => '07:45'], ['meal' => 'lunch', 'opens' => '13:00', 'closes' => '13:45'], ['meal' => 'supper', 'opens' => '17:00', 'closes' => '17:45'],
        ]], $this->staff->id);
        $this->student = Student::create(['student_id' => 'PNMTC/DA/RGN/24/25/001', 'first_name' => 'Example', 'last_name' => 'Student', 'status' => 'active']);
        $batch = CanteenQualificationImport::create(['institution_prefix' => 'PNMTC/DA', 'academic_year' => '2026-2027', 'semester' => 1,
            'file_name' => 'test.xlsx', 'file_sha256' => hash('sha256', 'fixture'), 'class_label' => 'RGN9', 'imported_at' => now()]);
        CanteenQualification::create(['import_id' => $batch->id, 'student_id' => $this->student->id, 'index_number' => $this->student->student_id, 'source_row' => 2, 'qualified' => true, 'source_record' => []]);
        foreach ([self::A, self::B] as $device) {
            $this->withToken($this->token)->postJson('/api/v1/canteen/meals/register-device', ['device_id' => $device, 'device_key' => $this->key, 'label' => $device === self::A ? 'Lane A' : 'Lane B'])->assertOk();
        }
        $groups = app(CanteenMealLanes::class)->roster()['groups'];
        app(CanteenMealLanes::class)->assign(array_fill_keys(array_column($groups, 'key'), self::A), [], $this->staff->id);
    }

    private function snapshot(string $device = self::A): array
    {
        return $this->withToken($this->token)->withHeader('X-Canteen-Device-Key', $this->key)
            ->postJson('/api/v1/canteen/meals/prepare', ['device_id' => $device])->assertOk()->json();
    }

    private function event(array $snapshot): array
    {
        return ['client_request_id' => (string) Str::uuid(), 'device_id' => self::A, 'snapshot_id' => $snapshot['snapshot_id'],
            'index_number' => $this->student->student_id, 'verification_method' => 'manual', 'meal' => 'breakfast',
            'service_date' => '2026-11-03', 'captured_at' => '2026-11-03T07:10:00Z', 'offline_issued' => true];
    }

    public function test_registration_is_idempotent_but_a_device_id_is_not_its_credential(): void
    {
        $this->withToken($this->token)->postJson('/api/v1/canteen/meals/register-device', ['device_id' => self::A, 'device_key' => $this->key, 'label' => 'New name'])->assertOk()->assertJsonPath('label', 'Lane A');
        $this->postJson('/api/v1/canteen/meals/register-device', ['device_id' => self::A, 'device_key' => str_repeat('b', 64), 'label' => 'Forged'])->assertForbidden();
        $this->withHeader('X-Canteen-Device-Key', str_repeat('b', 64))->postJson('/api/v1/canteen/meals/prepare', ['device_id' => self::A])->assertForbidden();
    }

    public function test_preparation_downloads_only_its_lane_and_freezes_the_day(): void
    {
        $snapshot = $this->snapshot();
        $this->assertSame('Lane A', $snapshot['lane']);
        $this->assertSame('approved', $snapshot['students'][$this->student->student_id]['decision']);
        $this->assertSame('2026-11-03T07:45:00+00:00', $snapshot['windows'][0]['closes_at']);
        $this->assertDatabaseCount('canteen_meal_plans', 1);
        $groups = app(CanteenMealLanes::class)->roster()['groups'];
        app(CanteenMealLanes::class)->assign(array_fill_keys(array_column($groups, 'key'), self::B), [], $this->staff->id);
        $again = $this->snapshot();
        $this->assertSame('Lane A', $again['lane']);
        $this->withHeader('X-Canteen-Device-Key', $this->key)->postJson('/api/v1/canteen/meals/prepare', ['device_id' => self::B])->assertUnprocessable();
    }

    public function test_a_timezone_edit_cannot_replace_an_already_prepared_serving_day(): void
    {
        $this->snapshot();
        $policy = app(CanteenMealPolicy::class)->get();
        $policy['timezone'] = 'Pacific/Honolulu';
        app(CanteenMealPolicy::class)->save($policy, $this->staff->id);
        $this->assertSame('2026-11-03', $this->snapshot()['service_date']);
        $this->assertDatabaseCount('canteen_meal_plans', 1);
    }

    public function test_a_later_shift_can_sync_the_same_device_without_reassigning_the_meal(): void
    {
        $event = $this->event($this->snapshot());
        $second = User::factory()->create();
        $this->token = $second->createToken('new-shift', ['canteen:access'], now()->addHours(12))->plainTextToken;
        app('auth')->forgetGuards();
        $this->travelTo(CarbonImmutable::parse('2026-11-03T07:15:00Z'));
        $this->withToken($this->token)->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()->assertJsonPath('results.0.redemption.status', 'redeemed');
        $this->assertDatabaseHas('canteen_meal_attempts', ['client_request_id' => $event['client_request_id'], 'attendant_user_id' => $this->staff->id, 'synced_by' => $second->id]);
        $next = $this->snapshot();
        $this->assertCount(1, $next['redeemed']);
        $this->assertSame((string) $second->id, $next['attendant_user_id']);
    }

    public function test_an_unassigned_day_cannot_be_frozen_accidentally(): void
    {
        DB::table('canteen_meal_assignments')->delete();
        $this->withToken($this->token)->withHeader('X-Canteen-Device-Key', $this->key)->postJson('/api/v1/canteen/meals/prepare', ['device_id' => self::A])->assertUnprocessable();
        $this->assertDatabaseCount('canteen_meal_plans', 0);
    }

    public function test_online_redemption_and_retries_commit_exactly_one_meal(): void
    {
        $snapshot = $this->snapshot();
        $event = $this->event($snapshot);
        $this->travelTo(CarbonImmutable::parse($event['captured_at']));
        $this->postJson('/api/v1/canteen/meals/redeem', $event)->assertOk()->assertJsonPath('redemption.status', 'redeemed');
        $this->postJson('/api/v1/canteen/meals/redeem', $event)->assertOk()->assertJsonPath('replay', true);
        $this->postJson('/api/v1/canteen/meals/redeem', $this->event($snapshot))->assertOk()->assertJsonPath('redemption.status', 'already_redeemed');
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
    }

    public function test_a_prepared_offline_capture_can_sync_next_day_without_rechecking_it_as_a_new_meal(): void
    {
        $snapshot = $this->snapshot();
        $event = $this->event($snapshot);
        CanteenQualification::query()->delete();
        $this->travelTo(CarbonImmutable::parse('2026-11-04T12:00:00Z'));
        $this->token = $this->staff->createToken('fresh', ['canteen:access'], now()->addHours(12))->plainTextToken;
        app('auth')->forgetGuards();
        $this->withToken($this->token)->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()
            ->assertJsonPath('results.0.redemption.status', 'redeemed')->assertJsonPath('results.0.redemption.reconciled_decision', 'review_required');
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()->assertJsonPath('results.0.replay', true);
        $this->assertDatabaseHas('canteen_meal_redemptions', ['service_date' => '2026-11-03', 'meal' => 'breakfast', 'is_offline' => true]);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
    }

    public function test_a_lost_online_denial_and_actual_offline_issuance_are_both_preserved(): void
    {
        $snapshot = $this->snapshot();
        $event = $this->event($snapshot);
        $this->travelTo(CarbonImmutable::parse($event['captured_at']));
        CanteenQualification::query()->update(['qualified' => false]);
        $this->postJson('/api/v1/canteen/meals/redeem', $event)->assertOk()->assertJsonPath('redemption.status', 'not_qualified');
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()
            ->assertJsonPath('results.0.redemption.status', 'redeemed')->assertJsonPath('results.0.redemption.reconciled_decision', 'denied');
        $row = DB::table('canteen_meal_attempts')->where('client_request_id', $event['client_request_id'])->first();
        $this->assertSame('not_qualified', json_decode($row->original_response, true)['redemption']['status']);
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()->assertJsonPath('results.0.replay', true);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
    }

    public function test_an_uncertain_unissued_capture_does_not_create_a_redemption_during_sync(): void
    {
        $event = $this->event($this->snapshot());
        $event['offline_issued'] = false;
        $this->travelTo(CarbonImmutable::parse($event['captured_at']));
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$event]])->assertOk()->assertJsonPath('results.0.redemption.status', 'not_recorded');
        $this->assertDatabaseCount('canteen_meal_redemptions', 0);
    }

    public function test_one_bad_capture_does_not_block_other_queued_records(): void
    {
        $snapshot = $this->snapshot();
        $event = $this->event($snapshot);
        $bad = $event;
        $bad['client_request_id'] = (string) Str::uuid();
        $bad['snapshot_id'] = (string) Str::uuid();
        $this->travelTo(CarbonImmutable::parse('2026-11-03T07:15:00Z'));
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::A, 'events' => [$bad, $event]])->assertOk()
            ->assertJsonPath('results.0.sync_rejected', true)->assertJsonPath('results.1.redemption.status', 'redeemed');
        $this->assertDatabaseCount('canteen_meal_sync_rejections', 1);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
    }

    public function test_another_device_cannot_use_an_offline_grant_and_old_apks_must_upgrade(): void
    {
        $snapshot = $this->snapshot();
        $event = $this->event($snapshot);
        $event['device_id'] = self::B;
        $this->postJson('/api/v1/canteen/meals/sync', ['device_id' => self::B, 'events' => [$event]])->assertOk()->assertJsonPath('results.0.sync_rejected', true);
        $this->postJson('/api/v1/canteen/verify', [])->assertStatus(426);
        $this->postJson('/api/v1/canteen/verifications/sync', [])->assertStatus(426);
        $this->assertDatabaseCount('canteen_meal_redemptions', 0);
    }
}
