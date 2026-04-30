<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionAuditLog;
use App\Models\ElectionVote;
use App\Models\ElectionVotingSession;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ElectionVoterIntegrityController extends Controller
{
    public function index(Request $request, Election $election): View
    {
        $studentId = trim((string) $request->string('student_id')->value());

        $student = null;
        $votes = collect();
        $sessions = collect();
        $auditLogs = collect();
        $sharedIpActivity = collect();

        if ($studentId !== '') {
            $student = Student::query()->where('student_id', $studentId)->first();

            $votes = ElectionVote::query()
                ->where('election_id', $election->id)
                ->where('student_id', $studentId)
                ->with(['position:id,name', 'candidate:id,name,image_path,photo'])
                ->orderByDesc('created_at')
                ->get();

            $sessions = ElectionVotingSession::query()
                ->where('election_id', $election->id)
                ->where('student_id', $studentId)
                ->orderByDesc('created_at')
                ->get();

            $auditLogs = ElectionAuditLog::query()
                ->where('election_id', $election->id)
                ->where('user_id', $studentId)
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();

            $ipAddresses = $votes->pluck('ip_address')
                ->merge($sessions->pluck('ip_address'))
                ->filter()
                ->unique()
                ->values();

            if ($ipAddresses->isNotEmpty()) {
                $sharedIpActivity = ElectionVote::query()
                    ->where('election_id', $election->id)
                    ->whereIn('ip_address', $ipAddresses)
                    ->where('student_id', '!=', $studentId)
                    ->select('student_id', 'ip_address', DB::raw('COUNT(*) as votes_count'), DB::raw('MAX(created_at) as last_vote_at'))
                    ->groupBy('student_id', 'ip_address')
                    ->orderByDesc('last_vote_at')
                    ->get();
            }
        }

        return view('admin.elections.voter-integrity', [
            'election' => $election,
            'studentId' => $studentId,
            'student' => $student,
            'votes' => $votes,
            'sessions' => $sessions,
            'auditLogs' => $auditLogs,
            'sharedIpActivity' => $sharedIpActivity,
        ]);
    }

    public function nullifyVotes(Request $request, Election $election): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $studentId = trim($validated['student_id']);
        $reason = $validated['reason'] ?? null;

        $deletedVotes = 0;

        DB::transaction(function () use ($election, $studentId, $reason, &$deletedVotes): void {
            $votesQuery = ElectionVote::query()
                ->where('election_id', $election->id)
                ->where('student_id', $studentId);

            $deletedVotes = (clone $votesQuery)->count();
            $votesQuery->delete();

            ElectionAuditLog::log(
                $election,
                'admin',
                (string) auth()->id(),
                'votes_nullified_by_admin',
                'Admin nullified student votes',
                [
                    'target_student_id' => $studentId,
                    'nullified_votes_count' => $deletedVotes,
                    'reason' => $reason,
                ]
            );
        });

        return redirect()
            ->route('admin.election.voter-integrity', ['election' => $election->id, 'student_id' => $studentId])
            ->with('success', "Nullified {$deletedVotes} vote(s) for student ID {$studentId}.");
    }

    public function allowRevote(Request $request, Election $election): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:500'],
            'nullify_existing_votes' => ['nullable', 'boolean'],
        ]);

        $studentId = trim($validated['student_id']);
        $reason = $validated['reason'] ?? null;
        $nullifyExistingVotes = (bool) ($validated['nullify_existing_votes'] ?? false);

        $deletedVotes = 0;
        $deletedSessions = 0;

        DB::transaction(function () use ($election, $studentId, $reason, $nullifyExistingVotes, &$deletedVotes, &$deletedSessions): void {
            if ($nullifyExistingVotes) {
                $votesQuery = ElectionVote::query()
                    ->where('election_id', $election->id)
                    ->where('student_id', $studentId);

                $deletedVotes = (clone $votesQuery)->count();
                $votesQuery->delete();
            }

            $sessionsQuery = ElectionVotingSession::query()
                ->where('election_id', $election->id)
                ->where('student_id', $studentId);

            $deletedSessions = (clone $sessionsQuery)->count();
            $sessionsQuery->delete();

            ElectionAuditLog::log(
                $election,
                'admin',
                (string) auth()->id(),
                'revote_enabled_by_admin',
                'Admin enabled revote for student',
                [
                    'target_student_id' => $studentId,
                    'deleted_sessions_count' => $deletedSessions,
                    'nullify_existing_votes' => $nullifyExistingVotes,
                    'nullified_votes_count' => $deletedVotes,
                    'reason' => $reason,
                ]
            );
        });

        return redirect()
            ->route('admin.election.voter-integrity', ['election' => $election->id, 'student_id' => $studentId])
            ->with('success', "Re-vote enabled for {$studentId}. Removed {$deletedSessions} session(s) and {$deletedVotes} vote(s).");
    }
}
