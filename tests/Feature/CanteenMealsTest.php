<?php

namespace Tests\Feature;

use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Models\Student;
use App\Services\Canteen\CanteenMealLanes;
use App\Services\Canteen\CanteenMealPolicy;
use App\Services\Canteen\CanteenMealRedemptions;
use App\Services\Canteen\CanteenPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CanteenMealsTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private array $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        app(CanteenPolicy::class)->setPeriod('2026-2027', 1);
        $this->student = Student::create(['student_id' => 'PNMTC/DA/RGN/24/25/001', 'first_name' => 'Example', 'last_name' => 'Student', 'status' => 'active']);
        $batch = CanteenQualificationImport::create(['institution_prefix' => 'PNMTC/DA', 'academic_year' => '2026-2027', 'semester' => 1,
            'file_name' => 'test.xlsx', 'file_sha256' => hash('sha256', 'fixture'), 'class_label' => 'RGN', 'imported_at' => now()]);
        CanteenQualification::create(['import_id' => $batch->id, 'student_id' => $this->student->id,
            'index_number' => $this->student->student_id, 'source_row' => 2, 'qualified' => true, 'source_record' => []]);
        // Fictional test windows; never used to configure a real institution.
        $this->configuration = ['enabled' => true, 'timezone' => 'Africa/Accra', 'offline_policy' => 'assigned_device', 'windows' => [
            ['meal' => 'breakfast', 'opens' => '06:00', 'closes' => '09:00'],
            ['meal' => 'lunch', 'opens' => '12:00', 'closes' => '14:00'],
            ['meal' => 'supper', 'opens' => '17:00', 'closes' => '20:00'],
        ]];
        app(CanteenMealPolicy::class)->save($this->configuration, 1);
        foreach (['device-a', 'device-b'] as $device) {
            DB::table('canteen_meal_devices')->insert(['device_id' => $device, 'label' => $device, 'key_hash' => hash('sha256', $device), 'registered_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
        $groups = app(CanteenMealLanes::class)->roster()['groups'];
        app(CanteenMealLanes::class)->assign(array_fill_keys(array_column($groups, 'key'), 'device-a'), [], 1);

        $this->travelTo(CarbonImmutable::parse('2026-11-03 07:00:00', 'UTC'));
    }

    private function request(?string $id = null, string $device = 'device-a'): array
    {
        return ['client_request_id' => $id ?? (string) Str::uuid(), 'device_id' => $device,
            'index_number' => $this->student->student_id, 'verification_method' => 'manual'];
    }

    public function test_first_qualified_scan_commits_breakfast_and_audit(): void
    {
        $data = $this->request();
        $result = app(CanteenMealRedemptions::class)->redeem(1, $data);
        $this->assertSame('redeemed', $result['redemption']['status']);
        $this->assertSame('breakfast', $result['redemption']['meal']);
        $this->assertNull($result['payment_percentage']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
        $this->assertDatabaseHas('canteen_meal_attempts', ['client_request_id' => $data['client_request_id'], 'status' => 'redeemed']);
    }

    public function test_a_second_device_cannot_redeem_the_same_students_breakfast(): void
    {
        $service = app(CanteenMealRedemptions::class);
        $first = $service->redeem(1, $this->request());
        $second = $service->redeem(2, $this->request(device: 'device-b'));
        $this->assertSame('already_redeemed', $second['redemption']['status']);
        $this->assertSame($first['redemption']['receipt_id'], $second['redemption']['receipt_id']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
        $this->assertDatabaseCount('canteen_meal_attempts', 2);
    }

    public function test_network_retry_returns_the_original_receipt_even_after_the_window_closes(): void
    {
        $data = $this->request();
        $service = app(CanteenMealRedemptions::class);
        $first = $service->redeem(1, $data);
        $this->travelTo(CarbonImmutable::parse('2026-11-03 10:00:00', 'UTC'));
        $replay = $service->redeem(1, $data);
        $this->assertTrue($replay['replay']);
        $this->assertEquals($first['redemption'], $replay['redemption']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
        $this->assertDatabaseCount('canteen_meal_attempts', 1);
    }

    public function test_same_student_can_take_lunch_and_next_days_breakfast(): void
    {
        $service = app(CanteenMealRedemptions::class);
        $service->redeem(1, $this->request());
        foreach (['2026-11-03 12:00:00', '2026-11-04 06:00:00'] as $time) {
            $this->travelTo(CarbonImmutable::parse($time, 'UTC'));
            $this->assertSame('redeemed', $service->redeem(1, $this->request())['redemption']['status']);
        }
        $this->assertDatabaseCount('canteen_meal_redemptions', 3);
    }

    public function test_changing_a_meal_window_does_not_reset_the_days_redemption(): void
    {
        $service = app(CanteenMealRedemptions::class);
        $service->redeem(1, $this->request());
        $changed = $this->configuration;
        $changed['windows'][0]['closes'] = '10:00';
        app(CanteenMealPolicy::class)->save($changed, 1);
        $this->travelTo(CarbonImmutable::parse('2026-11-03 09:30:00', 'UTC'));
        $this->assertSame('window_closed', $service->redeem(1, $this->request())['redemption']['status']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 1);
    }

    public function test_closing_time_is_exclusive_and_gaps_do_not_redeem_meals(): void
    {
        foreach (['05:59:59', '09:00:00', '11:59:59', '14:00:00', '20:00:00'] as $clock) {
            $this->travelTo(CarbonImmutable::parse('2026-11-03 '.$clock, 'UTC'));
            $this->assertSame('window_closed', app(CanteenMealRedemptions::class)->redeem(1, $this->request())['redemption']['status']);
        }
        $this->assertDatabaseCount('canteen_meal_redemptions', 0);
        $this->assertDatabaseCount('canteen_meal_attempts', 5);
    }

    public function test_nonqualified_missing_data_and_unknown_indexes_never_redeem(): void
    {
        CanteenQualification::query()->update(['qualified' => false]);
        $this->assertSame('not_qualified', app(CanteenMealRedemptions::class)->redeem(1, $this->request())['redemption']['status']);
        CanteenQualification::query()->delete();
        $this->assertSame('review_required', app(CanteenMealRedemptions::class)->redeem(1, $this->request())['redemption']['status']);
        $data = $this->request();
        $data['index_number'] = 'PNMTC/DA/RGN/24/25/999';
        $this->assertSame('not_found', app(CanteenMealRedemptions::class)->redeem(1, $data)['redemption']['status']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 0);
        $this->assertDatabaseCount('canteen_meal_attempts', 3);
    }

    public function test_reusing_a_request_id_from_another_attendant_is_rejected(): void
    {
        $data = $this->request();
        $service = app(CanteenMealRedemptions::class);
        $service->redeem(1, $data);
        $this->expectException(HttpException::class);
        $service->redeem(2, $data);
    }

    public function test_time_zone_controls_the_service_date_and_window(): void
    {
        $config = $this->configuration;
        $config['timezone'] = 'Asia/Tokyo';
        app(CanteenMealPolicy::class)->save($config, 1);
        $this->travelTo(CarbonImmutable::parse('2026-11-03 22:00:00', 'UTC'));
        $result = app(CanteenMealRedemptions::class)->redeem(1, $this->request());
        $this->assertSame('redeemed', $result['redemption']['status']);
        $this->assertSame('2026-11-04', $result['redemption']['service_date']);
    }

    public function test_unconfigured_institutions_are_disabled_and_do_not_inherit_windows(): void
    {
        config(['branding.student_id.institution_prefix' => 'OTHER/DA']);
        $this->assertFalse(app(CanteenMealPolicy::class)->get()['enabled']);
        $this->assertSame('not_configured', app(CanteenMealRedemptions::class)->redeem(1, $this->request())['redemption']['status']);
        $this->assertDatabaseCount('canteen_meal_redemptions', 0);
    }

    public function test_overlapping_and_overnight_windows_are_rejected(): void
    {
        foreach ([['opens' => '08:00', 'closes' => '10:00'], ['opens' => '23:00', 'closes' => '01:00']] as $times) {
            $config = $this->configuration;
            $config['windows'][1] = $times + ['meal' => 'lunch'];
            try {
                app(CanteenMealPolicy::class)->save($config, 1);
                $this->fail('Unsafe windows were accepted.');
            } catch (ValidationException) {
                $this->assertSame($this->configuration['windows'], app(CanteenMealPolicy::class)->get()['windows']);
            }
        }
    }
}
