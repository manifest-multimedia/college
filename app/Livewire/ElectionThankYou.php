<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionVotingSession;
use Livewire\Component;

class ElectionThankYou extends Component
{
    public $election;
    public $sessionId;
    public $votingSession;
    public $studentName;
    
    public function mount(Election $election, $sessionId = null)
    {
        $this->election = $election;
        $this->sessionId = $sessionId;
        
        // If we have a session ID, get the session details for display
        if ($sessionId) {
            $this->votingSession = ElectionVotingSession::where('session_id', $sessionId)
                ->where('election_id', $election->id)
                ->first();
            
            if ($this->votingSession && $this->votingSession->student) {
                $this->studentName = $this->votingSession->student->name ?? 'Student';
            }
        }
    }
    
    public function render()
    {
        return view('livewire.election-thank-you')
            ->layout('components.default.layout');
    }
}