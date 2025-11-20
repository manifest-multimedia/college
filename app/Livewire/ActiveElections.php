<?php

namespace App\Livewire;

use App\Models\Election;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActiveElections extends Component
{
    public function render()
    {
        $now = now();

        // Get active elections (ongoing and upcoming)
        $activeElections = Election::where('end_time', '>', $now)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->with('positions')
            ->get();

        // Get past elections
        $pastElections = Election::where('end_time', '<=', $now)
            ->where('is_active', true)
            ->orderByDesc('end_time')
            ->limit(10) // Only show the 10 most recent past elections
            ->with('positions')
            ->get();

        return view('livewire.active-elections', [
            'activeElections' => $activeElections,
            'pastElections' => $pastElections,
            'userHasVoted' => function ($electionId) {
                $studentId = Auth::user()->student_id ?? null;

                // If not a student or no student ID, they can't vote
                if (! $studentId || ! Auth::user()->hasRole('student')) {
                    return false;
                }

                // Check if the student has voted in this election
                return \App\Models\ElectionVote::where('election_id', $electionId)
                    ->where('student_id', $studentId)
                    ->exists();
            },
        ])->layout('components.dashboard.default', ['title' => 'Elections']);
    }
}
