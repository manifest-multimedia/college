<?php

namespace Tests\Feature;

use App\Models\Election;
use App\Models\ElectionCandidate;
use App\Models\ElectionPosition;
use App\Models\ElectionVote;
use App\Models\ElectionVotingSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicElectionPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_performance_page_is_accessible_without_authentication(): void
    {
        $election = Election::create([
            'name' => 'Student Representative Election',
            'description' => 'Public election for student representatives.',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => false,
            'voting_duration_minutes' => 30,
        ]);

        $response = $this->get(route('public.elections.performance', $election));

        $response->assertOk();
        $response->assertSee('Real-time candidate and position performance');
    }

    public function test_public_performance_data_endpoint_returns_candidate_metrics(): void
    {
        $election = Election::create([
            'name' => 'Main Election',
            'description' => 'Election with live performance metrics.',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'is_active' => true,
            'requires_verification' => true,
            'voting_duration_minutes' => 30,
        ]);

        $position = ElectionPosition::create([
            'election_id' => $election->id,
            'name' => 'President',
            'description' => 'President position',
            'max_selections' => 1,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $candidateOne = ElectionCandidate::create([
            'election_id' => $election->id,
            'election_position_id' => $position->id,
            'name' => 'Alice Candidate',
            'is_approved' => true,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $candidateTwo = ElectionCandidate::create([
            'election_id' => $election->id,
            'election_position_id' => $position->id,
            'name' => 'Bob Candidate',
            'is_approved' => true,
            'is_active' => true,
            'display_order' => 2,
        ]);

        foreach (['S001', 'S002', 'S003'] as $studentId) {
            ElectionVote::create([
                'election_id' => $election->id,
                'election_position_id' => $position->id,
                'election_candidate_id' => $candidateOne->id,
                'student_id' => $studentId,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'vote_type' => 'candidate',
            ]);
        }

        ElectionVote::create([
            'election_id' => $election->id,
            'election_position_id' => $position->id,
            'election_candidate_id' => $candidateTwo->id,
            'student_id' => 'S004',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'vote_type' => 'candidate',
        ]);

        foreach (['S001', 'S002', 'S003', 'S004'] as $index => $studentId) {
            ElectionVotingSession::create([
                'election_id' => $election->id,
                'student_id' => $studentId,
                'started_at' => now()->subMinutes(15 - $index),
                'expires_at' => now()->addMinutes(15),
                'completed_at' => now()->subMinutes(5),
                'vote_submitted' => true,
                'ip_address' => '127.0.0.1',
                'session_id' => 'session-'.$studentId,
            ]);
        }

        $response = $this->getJson(route('public.elections.performance.data', $election));

        $response->assertOk();
        $response->assertJsonPath('election.id', $election->id);
        $response->assertJsonPath('summary.total_positions', 1);
        $response->assertJsonPath('summary.total_votes', 4);
        $response->assertJsonPath('summary.total_voters', 4);
        $response->assertJsonPath('positions.0.name', 'President');
        $response->assertJsonPath('positions.0.type', 'multi_candidate');
        $response->assertJsonPath('positions.0.total_votes', 4);
        $response->assertJsonPath('positions.0.candidates.0.name', 'Alice Candidate');
        $response->assertJsonPath('positions.0.candidates.0.votes', 3);
        $response->assertJsonPath('positions.0.candidates.1.name', 'Bob Candidate');
        $response->assertJsonPath('positions.0.candidates.1.votes', 1);
    }
}
