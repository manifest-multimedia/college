<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\ElectionIpBlacklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ElectionIpBlacklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_blacklisted_ip_is_blocked_from_voting_verify_route(): void
    {
        $election = Election::create([
            'name' => 'IP Block Test Election',
            'description' => 'Testing blacklist middleware',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '172.70.91.157'])
            ->get(route('election.verify', ['election' => $election->id]));

        $response->assertForbidden();
    }

    public function test_admin_can_add_toggle_and_remove_blacklist_entries(): void
    {
        $role = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create([
            'role' => 'Administrator',
        ]);
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->post(route('admin.elections.ip-blacklist.store'), [
                'ip_address' => '172.69.112.177',
                'reason' => 'Automated suspicious activity',
            ])
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $entry = ElectionIpBlacklist::where('ip_address', '172.69.112.177')->firstOrFail();

        $this->assertTrue($entry->is_active);

        $this->actingAs($admin)
            ->post(route('admin.elections.ip-blacklist.toggle', $entry))
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $this->assertFalse($entry->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.elections.ip-blacklist.destroy', $entry))
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $this->assertDatabaseMissing('election_ip_blacklists', [
            'id' => $entry->id,
        ]);
    }
}
