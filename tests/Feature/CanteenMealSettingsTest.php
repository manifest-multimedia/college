<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Canteen\CanteenMealPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CanteenMealSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::findOrCreate('Administrator', 'web'));
        $this->actingAs($this->admin);
    }

    private function schedule(): array
    {
        return ['enabled' => '1', 'timezone' => 'Africa/Accra', 'version' => '', 'windows' => [
            ['meal' => 'breakfast', 'opens' => '07:00', 'closes' => '07:45'],
            ['meal' => 'lunch', 'opens' => '13:00', 'closes' => '13:45'],
            ['meal' => 'supper', 'opens' => '17:00', 'closes' => '17:45'],
        ]];
    }

    public function test_admin_sees_the_confirmed_pnmtc_times_without_enabling_redemption(): void
    {
        $this->get(route('canteen.meals.settings'))->assertOk()->assertSee('07:00')->assertSee('07:45')
            ->assertSee('13:00')->assertSee('13:45')->assertSee('17:00')->assertSee('17:45')->assertSee('Africa/Accra');
        $this->assertDatabaseCount('canteen_meal_policies', 0);
        $this->assertFalse(app(CanteenMealPolicy::class)->get()['enabled']);
    }

    public function test_saving_a_schedule_records_the_actor_and_all_three_windows(): void
    {
        $this->post(route('canteen.meals.update'), $this->schedule())->assertSessionHasNoErrors()->assertRedirect(route('canteen.meals.settings'));
        $policy = app(CanteenMealPolicy::class)->get();
        $this->assertTrue($policy['enabled']);
        $this->assertSame($this->schedule()['windows'], $policy['windows']);
        $this->assertSame('Africa/Accra', $policy['timezone']);
        $this->assertDatabaseHas('canteen_meal_policies', ['institution_prefix' => 'PNMTC/DA', 'updated_by' => $this->admin->id]);
        $this->assertDatabaseHas('canteen_meal_policy_changes', ['changed_by' => $this->admin->id, 'version' => $policy['version']]);
    }

    public function test_changes_keep_prior_configuration_and_prevent_lost_updates(): void
    {
        $this->post(route('canteen.meals.update'), $this->schedule())->assertSessionHasNoErrors();
        $first = app(CanteenMealPolicy::class)->get();
        $data = $this->schedule();
        $data['version'] = $first['version'];
        $data['windows'][0]['closes'] = '08:00';
        $this->post(route('canteen.meals.update'), $data)->assertSessionHasNoErrors();
        $changed = app(CanteenMealPolicy::class)->get();
        $this->assertNotSame($first['version'], $changed['version']);
        $audit = DB::table('canteen_meal_policy_changes')->where('version', $changed['version'])->first();
        $this->assertSame('07:45', json_decode($audit->previous_configuration, true)['windows'][0]['closes']);
        $this->post(route('canteen.meals.update'), $data)->assertSessionHasErrors('version');
        $this->assertSame($changed['version'], app(CanteenMealPolicy::class)->get()['version']);
        $this->assertDatabaseCount('canteen_meal_policy_changes', 2);
    }

    public function test_invalid_or_overlapping_times_leave_settings_unchanged(): void
    {
        foreach (['25:00', '07:00', '18:00'] as $closes) {
            $data = $this->schedule();
            $data['windows'][0]['closes'] = $closes;
            $this->post(route('canteen.meals.update'), $data)->assertSessionHasErrors();
            $this->assertDatabaseCount('canteen_meal_policies', 0);
            $this->assertDatabaseCount('canteen_meal_policy_changes', 0);
        }
    }

    public function test_each_institution_has_its_own_times(): void
    {
        $this->post(route('canteen.meals.update'), $this->schedule())->assertSessionHasNoErrors();
        config(['branding.student_id.institution_prefix' => 'OTHER/DA']);
        $this->get(route('canteen.meals.settings'))->assertOk()->assertDontSee('value="07:00"', false);
        $this->assertFalse(app(CanteenMealPolicy::class)->get()['enabled']);
        $this->assertSame([], app(CanteenMealPolicy::class)->get()['windows']);
    }

    public function test_ordinary_attendants_cannot_read_or_change_meal_settings(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole(Role::findOrCreate('Staff', 'web'));
        $this->actingAs($staff);
        $this->get(route('canteen.meals.settings'))->assertForbidden();
        $this->post(route('canteen.meals.update'), $this->schedule())->assertForbidden();
        $this->assertDatabaseCount('canteen_meal_policies', 0);
    }

    public function test_explicit_permission_can_delegate_schedule_management(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole(Role::findOrCreate('Staff', 'web'));
        $permission = Permission::findOrCreate('canteen.manage meals', 'web');
        $staff->givePermissionTo($permission);
        $this->actingAs($staff->fresh());
        $this->get(route('canteen.meals.settings'))->assertOk();
        $this->post(route('canteen.meals.update'), $this->schedule())->assertSessionHasNoErrors();
        $staff->revokePermissionTo($permission);
        $this->actingAs($staff->fresh())->get(route('canteen.meals.settings'))->assertForbidden();
    }

    public function test_confirmed_pnmtc_cutoffs_are_enforced_in_ghana_time(): void
    {
        $this->post(route('canteen.meals.update'), $this->schedule())->assertSessionHasNoErrors();
        $policy = app(CanteenMealPolicy::class);
        $settings = $policy->get();
        foreach (['06:59:59' => null, '07:00:00' => 'breakfast', '07:44:59' => 'breakfast', '07:45:00' => null,
            '13:00:00' => 'lunch', '13:45:00' => null, '17:00:00' => 'supper', '17:45:00' => null] as $clock => $meal) {
            $window = $policy->window($settings, CarbonImmutable::parse('2026-11-03 '.$clock, 'Africa/Accra'));
            $this->assertSame($meal, $window['meal'] ?? null);
        }
    }

    public function test_disabling_service_retains_the_saved_times(): void
    {
        $data = $this->schedule();
        $data['enabled'] = '0';
        $this->post(route('canteen.meals.update'), $data)->assertSessionHasNoErrors();
        $policy = app(CanteenMealPolicy::class)->get();
        $this->assertFalse($policy['enabled']);
        $this->assertSame($data['windows'], $policy['windows']);
    }
}
