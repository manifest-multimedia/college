<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\ElectionIpBlacklist;
use App\Models\ElectionIpWhitelist;
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

    public function test_blacklisted_forwarded_ip_is_blocked_from_public_verify_route(): void
    {
        $election = Election::create([
            'name' => 'Forwarded IP Block Test Election',
            'description' => 'Testing blacklist middleware with proxy headers',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        $response = $this
            ->withHeaders(['X-Forwarded-For' => '172.69.112.177, 10.0.0.1'])
            ->get(route('public.elections.verify', ['election' => $election->id]));

        $response->assertForbidden();
    }

    public function test_blacklisted_ipv6_mapped_ipv4_is_blocked(): void
    {
        $election = Election::create([
            'name' => 'IPv6 Mapped Block Test Election',
            'description' => 'Testing blacklist middleware with IPv6 mapped IPv4',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        ElectionIpBlacklist::query()->create([
            'ip_address' => '192.0.2.55',
            'reason' => 'Mapped IPv4 test',
            'is_active' => true,
        ]);

        $response = $this
            ->withHeaders(['X-Real-IP' => '::ffff:192.0.2.55'])
            ->get(route('public.elections.verify', ['election' => $election->id]));

        $response->assertForbidden();
    }

    public function test_whitelisted_ip_can_access_even_if_also_blacklisted(): void
    {
        $election = Election::create([
            'name' => 'Whitelist Priority Election',
            'description' => 'Testing whitelist precedence',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        ElectionIpBlacklist::query()->create([
            'ip_address' => '198.51.100.24',
            'reason' => 'Flagged IP',
            'is_active' => true,
        ]);

        ElectionIpWhitelist::query()->create([
            'ip_address' => '198.51.100.24',
            'reason' => 'Approved device',
            'is_active' => true,
        ]);

        $response = $this
            ->withHeaders(['X-Real-IP' => '198.51.100.24'])
            ->get(route('public.elections.verify', ['election' => $election->id]));

        $response->assertOk();
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

    public function test_admin_can_add_toggle_and_remove_whitelist_entries(): void
    {
        $role = Role::create(['name' => 'Administrator']);
        $admin = User::factory()->create([
            'role' => 'Administrator',
        ]);
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->post(route('admin.elections.ip-whitelist.store'), [
                'ip_address' => '203.0.113.18',
                'reason' => 'Trusted kiosk',
            ])
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $entry = ElectionIpWhitelist::where('ip_address', '203.0.113.18')->firstOrFail();

        $this->assertTrue($entry->is_active);

        $this->actingAs($admin)
            ->post(route('admin.elections.ip-whitelist.toggle', $entry))
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $this->assertFalse($entry->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.elections.ip-whitelist.destroy', $entry))
            ->assertRedirect(route('admin.elections.ip-blacklist.index'));

        $this->assertDatabaseMissing('election_ip_whitelists', [
            'id' => $entry->id,
        ]);
    }
}
