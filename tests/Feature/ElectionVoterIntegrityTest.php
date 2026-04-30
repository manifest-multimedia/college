<?php

namespace Tests\Feature;

use App\Livewire\ElectionVoterVerification;
use App\Models\Election;
use App\Models\ElectionCandidate;
use App\Models\ElectionPosition;
use App\Models\ElectionVote;
use App\Models\ElectionVotingSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ElectionVoterIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_submitted_session_without_votes_does_not_block_verification(): void
    {
        $election = Election::create([
            'name' => 'Integrity Election',
            'description' => 'Test election',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        Student::create([
            'student_id' => 'STU1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        ElectionVotingSession::create([
            'election_id' => $election->id,
            'student_id' => 'STU1001',
            'started_at' => now()->subMinutes(20),
            'expires_at' => now()->addMinutes(10),
            'completed_at' => now()->subMinutes(10),
            'vote_submitted' => true,
            'ip_address' => '127.0.0.1',
            'session_id' => 'stale-session-1',
        ]);

        Livewire::test(ElectionVoterVerification::class, ['election' => $election])
            ->set('student_id', 'STU1001')
            ->call('verify')
            ->assertSet('verificationStep', 'security')
            ->assertSet('errorMessage', '');

        $this->assertDatabaseMissing('election_voting_sessions', [
            'election_id' => $election->id,
            'student_id' => 'STU1001',
            'session_id' => 'stale-session-1',
        ]);
    }

    public function test_failed_security_answer_does_not_create_voting_session(): void
    {
        $election = Election::create([
            'name' => 'Integrity Election 2',
            'description' => 'Test election',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        Student::create([
            'student_id' => 'STU2002',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
        ]);

        Livewire::test(ElectionVoterVerification::class, ['election' => $election])
            ->set('student_id', 'STU2002')
            ->call('verify')
            ->assertSet('verificationStep', 'security')
            ->set('securityAnswer', 'wrong-answer')
            ->call('verifySecurityQuestion')
            ->assertSet('errorMessage', 'The information provided does not match our records. Please try again.');

        $this->assertDatabaseMissing('election_voting_sessions', [
            'election_id' => $election->id,
            'student_id' => 'STU2002',
        ]);
    }

    public function test_admin_can_nullify_votes_and_allow_revote_for_student(): void
    {
        $adminRole = Role::create(['name' => 'Administrator']);

        $admin = User::factory()->create([
            'role' => 'Administrator',
        ]);
        $admin->assignRole($adminRole);

        $election = Election::create([
            'name' => 'Integrity Election 3',
            'description' => 'Test election',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        $position = ElectionPosition::create([
            'election_id' => $election->id,
            'name' => 'President',
            'max_selections' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $candidate = ElectionCandidate::create([
            'election_id' => $election->id,
            'election_position_id' => $position->id,
            'name' => 'Candidate A',
            'is_active' => true,
        ]);

        ElectionVote::create([
            'election_id' => $election->id,
            'election_position_id' => $position->id,
            'election_candidate_id' => $candidate->id,
            'student_id' => 'STU3003',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'vote_type' => 'candidate',
        ]);

        ElectionVotingSession::create([
            'election_id' => $election->id,
            'student_id' => 'STU3003',
            'started_at' => now()->subMinutes(10),
            'expires_at' => now()->addMinutes(20),
            'completed_at' => now()->subMinutes(5),
            'vote_submitted' => true,
            'ip_address' => '127.0.0.1',
            'session_id' => 'student-session-3003',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.election.voter-integrity.nullify', $election), [
                'student_id' => 'STU3003',
                'reason' => 'Vote challenged',
            ])->assertRedirect();

        $this->assertDatabaseMissing('election_votes', [
            'election_id' => $election->id,
            'student_id' => 'STU3003',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.election.voter-integrity.allow-revote', $election), [
                'student_id' => 'STU3003',
                'reason' => 'False duplicate detected',
                'nullify_existing_votes' => false,
            ])->assertRedirect();

        $this->assertDatabaseMissing('election_voting_sessions', [
            'election_id' => $election->id,
            'student_id' => 'STU3003',
        ]);
    }
}
