<?php

namespace App\Livewire;

use App\Exports\ElectionResultsExport;
use App\Models\Election;
use App\Models\ElectionVote;
use App\Models\ElectionVotingSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ElectionResultsDashboard extends Component
{
    public $election;

    public $refreshInterval = 10; // seconds

    public $downloadType = 'excel';

    public $showingExportOptions = false;

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(Election $election)
    {
        $this->election = $election;
    }

    public function showExportOptions()
    {
        $this->showingExportOptions = true;
    }

    public function hideExportOptions()
    {
        $this->showingExportOptions = false;
    }

    public function setRefreshInterval($seconds)
    {
        $this->refreshInterval = (int) $seconds;
    }

    public function exportResults()
    {
        $fileName = 'election_results_'.$this->election->id.'_'.Carbon::now()->format('Y-m-d_H-i-s');

        if ($this->downloadType === 'excel') {
            return Excel::download(
                new ElectionResultsExport($this->election->id),
                $fileName.'.xlsx'
            );
        } else {
            // PDF Export
            $positions = $this->getPositionsWithResults();
            $totalVotes = $this->getTotalVotes();
            $totalVoters = $this->getTotalVoters();

            $pdf = PDF::loadView('exports.election-results-pdf', [
                'election' => $this->election,
                'positions' => $positions,
                'totalVotes' => $totalVotes,
                'totalVoters' => $totalVoters,
                'exportDate' => Carbon::now()->format('F j, Y g:i A'),
            ]);

            // Set paper size to A4 and landscape orientation for better readability
            $pdf->setPaper('a4', 'portrait');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName.'.pdf');
        }
    }

    protected function getPositionsWithResults()
    {
        return $this->election->positions()
            ->with(['candidates' => function ($query) {
                $query->withCount('votes');
                $query->orderByDesc('votes_count');
            }])
            ->orderBy('display_order')
            ->get();
    }

    protected function getTotalVotes()
    {
        return ElectionVote::where('election_id', $this->election->id)->count();
    }

    protected function getTotalVoters()
    {
        return ElectionVotingSession::where('election_id', $this->election->id)
            ->where('vote_submitted', true)
            ->count();
    }

    public function getVoterTurnout()
    {
        // Calculate percentage of eligible voters who have voted
        $totalStudents = \App\Models\Student::count();
        $totalVoters = $this->getTotalVoters();

        if ($totalStudents === 0) {
            return 0;
        }

        return round(($totalVoters / $totalStudents) * 100, 1);
    }

    public function render()
    {
        return view('livewire.election-results-dashboard', [
            'positions' => $this->getPositionsWithResults(),
            'totalVotes' => $this->getTotalVotes(),
            'totalVoters' => $this->getTotalVoters(),
            'voterTurnout' => $this->getVoterTurnout(),
        ])->layout('components.dashboard.default', ['title' => 'Election Results']);
    }
}
