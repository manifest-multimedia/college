<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionVotingSession;
use App\Models\ElectionVote;
use App\Models\ElectionAuditLog;
use App\Models\Student;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ElectionVoting extends Component
{
    public $election;
    public $votingSession;
    public $student;
    public $sessionId;
    public $timeRemaining;
    
    public $votes = [];
    public $confirmingSubmission = false;
    public $isSubmitting = false;
    public $voteSubmitted = false;
    public $errorMessage = '';
    
    protected $listeners = ['timeExpired' => 'handleTimeExpired'];
    
    public function mount(Election $election, $session)
    {
        $this->election = $election;
        $this->sessionId = $session;
        
        // Validate session
        $votingSession = ElectionVotingSession::where('session_id', $this->sessionId)
            ->where('election_id', $this->election->id)
            ->first();
            
        if (!$votingSession || $votingSession->vote_submitted || $votingSession->hasExpired()) {
            return redirect()->route('election.verification', ['election' => $this->election->id])
                ->with('error', 'Your voting session has expired or is invalid. Please verify again.');
        }
        
        $this->votingSession = $votingSession;
        $this->student = Student::where('student_id', $votingSession->student_id)->first();
        $this->timeRemaining = $votingSession->getRemainingTimeInSeconds();
        
        // Initialize votes array
        foreach ($this->election->positions as $position) {
            $this->votes[$position->id] = null;
        }
    }
    
    public function selectCandidate($positionId, $candidateId)
    {
        $this->votes[$positionId] = $candidateId;
    }
    
    public function confirmSubmit()
    {
        // Check if all positions have a vote
        $unvoted = [];
        foreach ($this->election->positions as $position) {
            if (!isset($this->votes[$position->id]) || is_null($this->votes[$position->id])) {
                $unvoted[] = $position->name;
            }
        }
        
        if (count($unvoted) > 0) {
            $this->errorMessage = 'Please vote for all positions: ' . implode(', ', $unvoted);
            return;
        }
        
        $this->confirmingSubmission = true;
    }
    
    public function cancelSubmit()
    {
        $this->confirmingSubmission = false;
    }
    
    public function submit()
    {
        // Prevent double submission
        if ($this->isSubmitting) {
            return;
        }
        
        $this->isSubmitting = true;
        $this->errorMessage = '';
        
        // Validate session again
        if ($this->votingSession->vote_submitted || $this->votingSession->hasExpired()) {
            $this->errorMessage = 'Your voting session has expired or your vote has already been submitted.';
            $this->isSubmitting = false;
            return;
        }
        
        DB::beginTransaction();
        try {
            $studentId = $this->votingSession->student_id;
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
            
            foreach ($this->votes as $positionId => $candidateId) {
                if (!is_null($candidateId)) {
                    ElectionVote::create([
                        'election_id' => $this->election->id,
                        'election_position_id' => $positionId,
                        'election_candidate_id' => $candidateId,
                        'student_id' => $studentId,
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                    ]);
                }
            }
            
            // Mark voting session as completed
            $this->votingSession->markAsCompleted();
            
            // Log the vote
            ElectionAuditLog::log(
                $this->election,
                'student',
                $studentId,
                'vote_submitted',
                'Student submitted their vote',
                [
                    'student_name' => $this->student->name ?? 'Unknown',
                    'session_id' => $this->sessionId
                ]
            );
            
            DB::commit();
            
            $this->voteSubmitted = true;
            $this->confirmingSubmission = false;
            
            // After short delay, redirect to thank you page
            $this->dispatch('voteSubmitted');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            $this->isSubmitting = false;
        }
    }
    
    public function handleTimeExpired()
    {
        $this->redirect(route('election.expired', ['election' => $this->election->id]));
    }
    
    public function render()
    {
        return view('livewire.election-voting', [
            'positions' => $this->election->positions()->with(['candidates' => function($query) {
                $query->where('is_active', true)->orderBy('display_order');
            }])->orderBy('display_order')->get()
        ])->layout('components.dashboard.default', ['title' => 'Vote in Election']);
    }
}