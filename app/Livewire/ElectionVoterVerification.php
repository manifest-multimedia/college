<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionVotingSession;
use App\Models\Student;
use App\Models\ElectionAuditLog;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::info('ElectionVoterVerification mounted', ['election_id' => $election->id]);
        $this->election = $election;
    }
    
    public function verify()
    {
        Log::info('Starting student verification process', ['student_id' => $this->student_id, 'election_id' => $this->election->id]);
        
        $this->validate();
        
        // Reset messages
        $this->errorMessage = '';
        $this->successMessage = '';
        
        // Check if election is active
        if (!$this->election->isActive()) {
            Log::warning('Verification failed: Election not active', [
                'election_id' => $this->election->id,
                'student_id' => $this->student_id,
                'election_status' => $this->election->is_active ? 'is_active=true' : 'is_active=false',
                'start_time' => $this->election->start_time->format('Y-m-d H:i:s'),
                'end_time' => $this->election->end_time->format('Y-m-d H:i:s'),
                'current_time' => now()->format('Y-m-d H:i:s')
            ]);
            
            $this->errorMessage = 'This election is not currently active.';
            return;
        }
        
        DB::beginTransaction();
        try {
            // Check if student exists in the database
            $student = Student::where('student_id', $this->student_id)->first();
            if (!$student) {
                Log::warning('Verification failed: Invalid student ID', [
                    'student_id' => $this->student_id,
                    'election_id' => $this->election->id
                ]);
                
                $this->errorMessage = 'Invalid Student ID. Please try again.';
                DB::commit();
                return;
            }
            
            Log::info('Student found', [
                'student_id' => $student->student_id,
                'student_name' => $student->name,
                'election_id' => $this->election->id
            ]);
            
            // Check if student has already voted or has an active session
            $existingSession = ElectionVotingSession::where('student_id', $this->student_id)
                ->where('election_id', $this->election->id)
                ->first();
            
            if ($existingSession) {
                if ($existingSession->vote_submitted) {
                    Log::warning('Verification failed: Student already voted', [
                        'student_id' => $this->student_id,
                        'election_id' => $this->election->id,
                        'session_id' => $existingSession->session_id
                    ]);
                    
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
                    Log::info('Using existing session', [
                        'student_id' => $this->student_id,
                        'election_id' => $this->election->id,
                        'session_id' => $existingSession->session_id,
                        'expires_at' => $existingSession->expires_at->format('Y-m-d H:i:s')
                    ]);
                    
                    // Existing valid session - redirect to voting
                    DB::commit();
                    $this->redirectToVoting($existingSession);
                    return;
                }
                
                // Session expired but not submitted - create a new one
                Log::info('Existing session expired, creating new session', [
                    'student_id' => $this->student_id,
                    'election_id' => $this->election->id,
                    'old_session_id' => $existingSession->session_id
                ]);
                
                $existingSession->delete();
            }
            
            // Create a new voting session
            // Fix: Get the correct field name from the Election model
            $sessionDuration = $this->election->voting_duration_minutes ?? 30; // Default to 30 minutes if not set
            Log::info('Session duration value', ['duration' => $sessionDuration]);
            
            $sessionId = Str::uuid()->toString();
            $now = now();
            
            Log::info('Creating new voting session', [
                'student_id' => $this->student_id,
                'election_id' => $this->election->id,
                'session_id' => $sessionId,
                'duration_minutes' => $sessionDuration
            ]);
            
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
            Log::info('Redirecting to voting interface', [
                'student_id' => $this->student_id,
                'election_id' => $this->election->id,
                'session_id' => $sessionId,
                'redirect_to' => route('election.vote', [
                    'election' => $this->election->id,
                    'sessionId' => $votingSession->session_id
                ])
            ]);
            
            // Use native browser redirect instead of Livewire redirect
            return $this->redirectToVoting($votingSession);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error during verification process', [
                'student_id' => $this->student_id,
                'election_id' => $this->election->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
        }
    }
    
    protected function redirectToVoting($votingSession)
    {
        $url = route('election.vote', [
            'election' => $this->election->id,
            'sessionId' => $votingSession->session_id
        ]);
        
        Log::info('Attempting to redirect to voting page', [
            'election_id' => $this->election->id,
            'session_id' => $votingSession->session_id,
            'route' => 'election.vote',
            'url' => $url
        ]);

        // Use the simpler direct redirect for better reliability
        return redirect()->to($url);
    }
    
    public function render()
    {
        return view('livewire.election-voter-verification')->layout('components.dashboard.default', ['title' => 'Verify Student ID']);
    }
}