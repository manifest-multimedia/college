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
    
    // Security question fields
    public $verificationStep = 'id';  // 'id' -> 'security' -> 'complete'
    public $securityQuestion = '';
    public $securityQuestionField = '';
    public $securityAnswer = '';
    public $validatedStudent = null;
    
    protected $rules = [
        'student_id' => 'required|string|max:50',
        'securityAnswer' => 'required|string',
    ];
    
    public function mount(Election $election)
    {
        Log::info('ElectionVoterVerification mounted', ['election_id' => $election->id]);
        $this->election = $election;
    }
    
    public function verify()
    {
        Log::info('Starting student verification process', ['student_id' => $this->student_id, 'election_id' => $this->election->id]);
        
        $this->validate([
            'student_id' => 'required|string|max:50',
        ]);
        
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
            $this->dispatch('verification-failed'); // Emit event for sound
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
                $this->dispatch('verification-failed'); // Emit event for sound
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
                    $this->dispatch('verification-failed'); // Emit event for sound
                    
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
            
            // Store the student for security question verification
            $this->validatedStudent = $student;
            $this->verificationStep = 'security';
            
            // Generate a security question
            $this->generateSecurityQuestion();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error during verification process', [
                'student_id' => $this->student_id,
                'election_id' => $this->election->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            $this->dispatch('verification-failed'); // Emit event for sound
        }
    }
    
    public function generateSecurityQuestion()
    {
        // Define the available security question fields
        $securityFields = [
            'email' => 'Email Address',
            'date_of_birth' => 'Date of Birth',
            'home_region' => 'Home Region',
            'mobile_number' => 'Mobile Number',
            'religion' => 'Religion'
        ];
        
        // Randomly select one field for verification
        $this->securityQuestionField = array_rand($securityFields);
        $this->securityQuestion = $securityFields[$this->securityQuestionField];
        
        Log::info('Generated security question', [
            'student_id' => $this->student_id,
            'question_field' => $this->securityQuestionField,
            'question_text' => $this->securityQuestion
        ]);
    }
    
    public function verifySecurityQuestion()
    {
        $this->validate([
            'securityAnswer' => 'required|string',
        ]);
        
        if (!$this->validatedStudent) {
            $this->errorMessage = 'Session expired. Please start over.';
            $this->verificationStep = 'id';
            $this->dispatch('verification-failed'); // Emit event for sound
            return;
        }
        
        Log::info('Verifying security question', [
            'student_id' => $this->student_id,
            'question_field' => $this->securityQuestionField,
            'provided_answer' => $this->securityAnswer,
        ]);
        
        // Get the actual value from the student record
        $expectedAnswer = $this->validatedStudent->{$this->securityQuestionField};
        
        // Normalize both the expected answer and provided answer for comparison
        $normalizedExpected = $this->normalizeAnswer($expectedAnswer);
        $normalizedProvided = $this->normalizeAnswer($this->securityAnswer);
        
        if ($normalizedProvided === $normalizedExpected) {
            Log::info('Security question verified successfully', [
                'student_id' => $this->student_id,
                'question_field' => $this->securityQuestionField
            ]);
            
            // Security verification successful
            $this->createVotingSession();
        } else {
            Log::warning('Security verification failed', [
                'student_id' => $this->student_id,
                'question_field' => $this->securityQuestionField,
                'expected' => $normalizedExpected,
                'provided' => $normalizedProvided,
            ]);
            
            $this->errorMessage = 'The information provided does not match our records. Please try again.';
            $this->dispatch('verification-failed'); // Emit event for sound
            
            // Log the failed verification
            ElectionAuditLog::log(
                $this->election,
                'student',
                $this->student_id,
                'security_verification_failed',
                'Failed to verify student identity',
                ['question_field' => $this->securityQuestionField]
            );
        }
    }
    
    protected function normalizeAnswer($answer)
    {
        // Convert to lowercase and trim
        $normalized = strtolower(trim($answer));
        
        // Special handling for date fields
        if ($this->securityQuestionField === 'date_of_birth') {
            // Allow different date formats by extracting and reformatting
            $dateObj = \DateTime::createFromFormat('Y-m-d', $normalized);
            if (!$dateObj) {
                $dateObj = \DateTime::createFromFormat('d-m-Y', $normalized);
            }
            if (!$dateObj) {
                $dateObj = \DateTime::createFromFormat('m/d/Y', $normalized);
            }
            if (!$dateObj) {
                $dateObj = \DateTime::createFromFormat('d/m/Y', $normalized);
            }
            
            if ($dateObj) {
                // Standardize date format
                return $dateObj->format('Y-m-d');
            }
        }
        
        // For phone numbers, remove common formatting
        if ($this->securityQuestionField === 'mobile_number') {
            // Remove spaces, dashes, parentheses, and plus signs
            $normalized = preg_replace('/[\s\-\(\)\+]/', '', $normalized);
        }
        
        return $normalized;
    }
    
    public function createVotingSession()
    {
        DB::beginTransaction();
        try {
            $student = $this->validatedStudent;
            
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
            
            Log::error('Error during voting session creation', [
                'student_id' => $this->student_id,
                'election_id' => $this->election->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            $this->dispatch('verification-failed'); // Emit event for sound
            $this->verificationStep = 'id'; // Return to the first step
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
    
    public function resetVerification()
    {
        // Reset the verification process
        $this->verificationStep = 'id';
        $this->securityQuestion = '';
        $this->securityQuestionField = '';
        $this->securityAnswer = '';
        $this->validatedStudent = null;
        $this->errorMessage = '';
    }
    
    public function render()
    {
        return view('livewire.election-voter-verification')
            ->layout('components.public.layout', ['title' => 'Verify Student ID']);
    }
}