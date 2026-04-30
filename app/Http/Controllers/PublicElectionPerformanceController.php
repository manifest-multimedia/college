<?php

namespace App\Http\Controllers;

use App\Models\Election;
use App\Models\ElectionCandidate;
use App\Models\ElectionPosition;
use App\Models\ElectionVote;
use App\Models\ElectionVotingSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicElectionPerformanceController extends Controller
{
    public function show(Election $election): View
    {
        return view('public.elections.performance', [
            'election' => $election,
        ]);
    }

    public function data(Election $election): JsonResponse
    {
        $positions = $election->positions()
            ->with([
                'candidates' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('display_order')
                        ->withCount('votes');
                },
            ])
            ->orderBy('display_order')
            ->get();

        $totalVotes = ElectionVote::where('election_id', $election->id)->count();
        $totalVoters = ElectionVotingSession::where('election_id', $election->id)
            ->where('vote_submitted', true)
            ->count();
        $totalStudents = Student::count();

        return response()->json([
            'election' => [
                'id' => $election->id,
                'name' => $election->name,
                'description' => $election->description,
                'start_time' => optional($election->start_time)->toIso8601String(),
                'end_time' => optional($election->end_time)->toIso8601String(),
                'status' => $this->resolveElectionStatus($election),
            ],
            'summary' => [
                'total_positions' => $positions->count(),
                'total_votes' => $totalVotes,
                'total_voters' => $totalVoters,
                'voter_turnout_percent' => $totalStudents > 0 ? round(($totalVoters / $totalStudents) * 100, 1) : 0,
                'last_updated_at' => now()->toIso8601String(),
            ],
            'positions' => $positions
                ->map(fn (ElectionPosition $position) => $this->buildPositionPerformance($position))
                ->values(),
        ]);
    }

    protected function buildPositionPerformance(ElectionPosition $position): array
    {
        if ($position->hasSingleCandidate()) {
            $yesNo = $position->getYesNoVotes();

            if (! $yesNo) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'description' => $position->description,
                    'type' => 'single_candidate_yes_no',
                    'total_votes' => 0,
                    'candidate' => null,
                    'yes_votes' => 0,
                    'no_votes' => 0,
                    'yes_percent' => 0,
                    'no_percent' => 0,
                    'has_won' => false,
                ];
            }

            return [
                'id' => $position->id,
                'name' => $position->name,
                'description' => $position->description,
                'type' => 'single_candidate_yes_no',
                'total_votes' => $yesNo['total_votes'],
                'candidate' => $this->buildCandidateSummary($yesNo['candidate']),
                'yes_votes' => $yesNo['yes_votes'],
                'no_votes' => $yesNo['no_votes'],
                'yes_percent' => $yesNo['yes_percent'],
                'no_percent' => $yesNo['no_percent'],
                'has_won' => $yesNo['has_won'],
            ];
        }

        $totalPositionVotes = $position->votes()->count();
        $candidates = $position->candidates
            ->map(function (ElectionCandidate $candidate) use ($totalPositionVotes) {
                $votes = (int) ($candidate->votes_count ?? 0);

                return [
                    ...$this->buildCandidateSummary($candidate),
                    'votes' => $votes,
                    'vote_percent' => $totalPositionVotes > 0 ? round(($votes / $totalPositionVotes) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('votes')
            ->values();

        return [
            'id' => $position->id,
            'name' => $position->name,
            'description' => $position->description,
            'type' => 'multi_candidate',
            'total_votes' => $totalPositionVotes,
            'candidates' => $candidates,
        ];
    }

    protected function buildCandidateSummary(ElectionCandidate $candidate): array
    {
        $photoUrl = $candidate->photo_url;

        if (! empty($candidate->image_path) && Storage::disk('public')->exists($candidate->image_path)) {
            $photoUrl = Storage::url($candidate->image_path);
        }

        return [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'photo_url' => $photoUrl,
        ];
    }

    protected function resolveElectionStatus(Election $election): string
    {
        if ($election->isActive()) {
            return 'active';
        }

        if ($election->isUpcoming()) {
            return 'upcoming';
        }

        if ($election->hasEnded()) {
            return 'completed';
        }

        return 'inactive';
    }
}
