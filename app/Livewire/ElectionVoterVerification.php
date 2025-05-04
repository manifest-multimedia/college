<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionVotingSession;
use App\Models\Student;
use App\Models\ElectionAuditLog;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ElectionVoterVerification extends Component
{
    public $election;
    public $student_id = '';
    public $errorMessage = '';
    public $successMessage = '';
    
    protected $rules = [
        'student_id' => 'required|string|max:50',
    ];
    
    public function mount(Election $election)
    {
        $this->election = $election;
    }
    
    public function verify()
    {
        $this->validate();
        
        // Reset messages
        $this->errorMessage = '';
        $this->successMessage = '';
        
        // Check if election is active
        if (!$this->election->isActive()) {
            $this->errorMessage = 'This election is not currently active.';
            return;
        }
        
        DB::beginTransaction();
        try {
            // Check if student exists in the database
            $student = Student::where('student_id', $this->student_id)->first();
            if (!$student) {
                $this->errorMessage = 'Invalid Student ID. Please try again.';
                DB::commit();
                return;
            }
            
            // Check if student has already voted or has an active session
            $existingSession = ElectionVotingSession::where('student_id', $this->student_id)
                ->where('election_id', $this->election->id)
                ->first();
            
            if ($existingSession) {
                if ($existingSession->vote_submitted) {
                    $this->errorMessage = 'You have already voted in this election.';
                    
                    ElectionAuditLog::log(
                        $this->election,
                        'student',
                        $this->student_id,
                        'duplicate_vote_attempt',
                        'Student attempted to vote again',
                        ['student_name' => $student->name ?? 'Unknown']
                    );
                    
                    DB::commit();
                    return;
                }
                
                if (!$existingSession->hasExpired() && $existingSession->isValid()) {
                    // Existing valid session - redirect to voting
                    $this->redirectToVoting($existingSession);
                    DB::commit();
                    return;
                }
                
                // Session expired but not submitted - create a new one
                $existingSession->delete();
            }
            
            // Create a new voting session
            $sessionDuration = $this->election->voting_session_duration;
            $sessionId = Str::uuid()->toString();
            $now = now();
            
            $votingSession = ElectionVotingSession::create([
                'election_id' => $this->election->id,
                'student_id' => $this->student_id,
                'started_at' => $now,
                'expires_at' => $now->addMinutes($sessionDuration),
                'ip_address' => request()->ip(),
                'session_id' => $sessionId
            ]);
            
            ElectionAuditLog::log(
                $this->election,
                'student',
                $this->student_id,
                'voting_session_started',
                'Student started a voting session',
                [
                    'student_name' => $student->name ?? 'Unknown',
                    'session_id' => $sessionId,
                    'expires_at' => $votingSession->expires_at->format('Y-m-d H:i:s')
                ]
            );
            
            DB::commit();
            
            // Redirect to voting interface
            $this->redirectToVoting($votingSession);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
        }
    }
    
    protected function redirectToVoting($votingSession)
    {
        return redirect()->route('election.vote', [
            'election' => $this->election->id,
            'session' => $votingSession->session_id
        ]);
    }
    
    public function render()
    {
        return view('livewire.election-voter-verification')->layout('components.dashboard.default', ['title' => 'Verify Student ID']);
    }
}