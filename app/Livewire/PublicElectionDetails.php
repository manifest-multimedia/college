<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Election;
use App\Models\ElectionPosition;
use Illuminate\Support\Facades\Log;

class PublicElectionDetails extends Component
{
    public $election;
    public $positions;
    public $candidateCounts;
    
    public function mount(Election $election)
    {
        try {
            $this->election = $election;

            // Get all positions for this election
            $this->positions = ElectionPosition::where('election_id', $election->id)
                ->orderBy('display_order')
                ->get();
                
            // Load candidate counts for each position
            $this->candidateCounts = [];
            foreach ($this->positions as $position) {
                $this->candidateCounts[$position->id] = $position->candidates()->count();
            }
            
            Log::info('Public election details page loaded', [
                'election_id' => $election->id,
                'election_title' => $election->title,
                'positions_count' => $this->positions->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading public election details', [
                'election_id' => $election->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function startVoting()
    {
        return redirect()->route('public.elections.verify', $this->election);
    }
    
    public function render()
    {
        return view('livewire.public-election-details')
            ->layout('components.public.layout', ['title' => $this->election->title ?? 'Election Details']);
    }
}
