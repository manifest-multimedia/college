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
    public $yesNoVotes = [];
    public $confirmingSubmission = false;
    public $isSubmitting = false;
    public $voteSubmitted = false;
    public $errorMessage = '';
    
    protected $listeners = ['timeExpired' => 'handleTimeExpired'];
    
    public function mount(Election $election, $sessionId = null)
    {
        $this->election = $election;
        $this->sessionId = $sessionId;
        
        // Validate session
        $votingSession = ElectionVotingSession::where('session_id', $this->sessionId)
            ->where('election_id', $this->election->id)
            ->first();
            
        if (!$votingSession || $votingSession->vote_submitted || $votingSession->hasExpired()) {
            return redirect()->route('election.verify', ['election' => $this->election->id])
                ->with('error', 'Your voting session has expired or is invalid. Please verify again.');
        }
        
        $this->votingSession = $votingSession;
        $this->student = Student::where('student_id', $votingSession->student_id)->first();
        $this->timeRemaining = $votingSession->getRemainingTimeInSeconds();
        
        // Initialize votes array
        foreach ($this->election->positions as $position) {
            $this->votes[$position->id] = null;
            $this->yesNoVotes[$position->id] = null;
        }
    }
    
    public function selectCandidate($positionId, $candidateId)
    {
        $this->votes[$positionId] = $candidateId;
        // Reset yes/no vote since a candidate was selected
        $this->yesNoVotes[$positionId] = null;
    }

    public function selectYesNo($positionId, $value)
    {
        $this->yesNoVotes[$positionId] = $value;
        // Keep the candidate ID for reference (single candidate case)
        // but we'll use yesNoVotes to determine the vote type
    }
    
    public function confirmSubmit()
    {
        // Check if all positions have a vote
        $unvoted = [];
        foreach ($this->election->positions as $position) {
            $hasSingleCandidate = $position->candidates->where('is_active', true)->count() === 1;
            
            if ($hasSingleCandidate) {
                // For positions with a single candidate, check if yes/no vote was made
                if (!isset($this->yesNoVotes[$position->id]) || is_null($this->yesNoVotes[$position->id])) {
                    $unvoted[] = $position->name;
                }
            } else {
                // For regular positions, check if a candidate was selected
                if (!isset($this->votes[$position->id]) || is_null($this->votes[$position->id])) {
                    $unvoted[] = $position->name;
                }
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
            
            foreach ($this->election->positions as $position) {
                $hasSingleCandidate = $position->candidates->where('is_active', true)->count() === 1;
                
                if ($hasSingleCandidate) {
                    // Handle YES/NO vote for single candidate
                    $candidate = $position->candidates->where('is_active', true)->first();
                    $voteType = $this->yesNoVotes[$position->id];
                    
                    ElectionVote::create([
                        'election_id' => $this->election->id,
                        'election_position_id' => $position->id,
                        'election_candidate_id' => $candidate->id,
                        'student_id' => $studentId,
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'vote_type' => $voteType, // 'yes' or 'no'
                    ]);
                } else {
                    // Handle regular candidate vote
                    if (!is_null($this->votes[$position->id])) {
                        ElectionVote::create([
                            'election_id' => $this->election->id,
                            'election_position_id' => $position->id,
                            'election_candidate_id' => $this->votes[$position->id],
                            'student_id' => $studentId,
                            'ip_address' => $ipAddress,
                            'user_agent' => $userAgent,
                            'vote_type' => 'candidate', // Regular candidate vote
                        ]);
                    }
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
            
            // Show thank you message briefly, then redirect
            $this->dispatch('voteSubmitted');
            
            // Queue a redirect after a short delay to allow the thank you message to be displayed
            return $this->redirect(route('election.thank-you', ['election' => $this->election->id, 'sessionId' => $this->sessionId]), navigate: true);
            
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
        return view('livewire.election-voting')
            ->layout('components.public.layout', ['title' => 'Vote - ' . ($this->election->title ?? 'Election')]);
    }
}