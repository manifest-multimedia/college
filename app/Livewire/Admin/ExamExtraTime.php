<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamExtraTime extends Component
{
    use WithPagination, AuthorizesRequests;
    
    protected $paginationTheme = 'bootstrap';
    
    // Properties for filtering and selection
    public $exam_id = null;
    public $search = '';
    public $extraTimeMinutes = 20; // Default of 5 minutes
    public $applyToAll = false;
    public $includeCompletedSessions = false; // Control whether to include completed sessions
    
    // Store student details when found
    public $foundStudent = null;
    public $foundUser = null;
    public $studentFound = false;
    
    // Properties for tracking success/error messages
    public $successMessage = '';
    public $errorMessage = '';
    
    // For selections
    public $selectedSessions = [];
    public $perPage = 10;

    // Modal related properties
    public $showViewModal = false;
    public $viewingSession = null;
    
    // Rules for validation
    protected $rules = [
        'extraTimeMinutes' => 'required|integer|min:1|max:60',
        'selectedSessions' => 'required_if:applyToAll,false',
        'exam_id' => 'required',
    ];
    
    protected $messages = [
        'selectedSessions.required_if' => 'Please select at least one exam session',
        'extraTimeMinutes.min' => 'Extra time must be at least 1 minute',
        'extraTimeMinutes.max' => 'Extra time cannot exceed 60 minutes',
    ];
    
    public function mount()
    {
        // Ensure only system users can access this component
        if (!Auth::user() || !Auth::user()->hasRole('System')) {
            Log::warning('Unauthorized access attempt to ExamExtraTime component', [
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ]);
            
            abort(403, 'You do not have permission to access this page.');
        }
    }
    
    public function updated($field)
    {
        $this->validateOnly($field);
        
        // Clear messages when fields change
        $this->successMessage = '';
        $this->errorMessage = '';
        
        if ($field === 'exam_id') {
            // Reset sessions data when exam changes
            $this->resetSessions();
        }
        
        if ($field === 'applyToAll') {
            // Reset selected sessions when apply to all changes
            $this->selectedSessions = [];
        }
    }
    
    public function resetSessions()
    {
        $this->selectedSessions = [];
        $this->search = '';
        $this->resetPage();
    }
    
    public function getExamSessionsProperty()
    {
        if (!$this->exam_id) {
            return collect();
        }
        
        $query = ExamSession::where('exam_id', $this->exam_id);
        
        // Apply search filter if provided
        if ($this->search) {
            // First try to find students matching the search criteria
            $foundUserIds = [];
            
            if (strlen($this->search) >= 3) {
                // Try to find students by their college ID or name
                $students = Student::where('student_id', 'like', '%' . $this->search . '%')
                    ->orWhere('first_name', 'like', '%' . $this->search . '%')
                    ->get();
                
                // Get associated user accounts by email
                if ($students->isNotEmpty()) {
                    $emails = $students->pluck('email')->toArray();
                    $users = User::whereIn('email', $emails)->get();
                    $foundUserIds = $users->pluck('id')->toArray();
                }
            }
            
            // Apply filter using found user IDs or direct user search
            if (!empty($foundUserIds)) {
                $query->whereIn('student_id', $foundUserIds);
            } else {
                // Fallback to direct search in user accounts
                $query->whereHas('student', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            }
        }
        
        // Get sessions with student and exam info
        return $query->with(['student', 'exam.course'])
                    ->orderBy('started_at', 'desc')
                    ->paginate($this->perPage);
    }
    
    public function addExtraTime()
    {
        $this->validate();
        
        try {
            $sessionsUpdated = 0;
            $now = Carbon::now();
            
            if ($this->applyToAll) {
                // Build query for all sessions for this exam
                $query = ExamSession::where('exam_id', $this->exam_id);
                
                // Only filter by completed_at if we're not including completed sessions
                if (!$this->includeCompletedSessions) {
                    $query->whereNull('completed_at');
                }
                
                // Apply extra time to filtered sessions
                $sessionsUpdated = $query->update([
                    'extra_time_minutes' => \DB::raw('extra_time_minutes + ' . $this->extraTimeMinutes),
                    'extra_time_added_by' => Auth::id(),
                    'extra_time_added_at' => $now,
                ]);
            } else {
                // Apply extra time to selected sessions only
                if (empty($this->selectedSessions)) {
                    $this->errorMessage = 'Please select at least one exam session';
                    return;
                }
                
                $sessionsUpdated = ExamSession::whereIn('id', $this->selectedSessions)
                                   ->update([
                                       'extra_time_minutes' => \DB::raw('extra_time_minutes + ' . $this->extraTimeMinutes),
                                       'extra_time_added_by' => Auth::id(),
                                       'extra_time_added_at' => $now,
                                   ]);
            }
            
            if ($sessionsUpdated > 0) {
                $this->successMessage = $sessionsUpdated . ' exam session(s) were granted ' . $this->extraTimeMinutes . ' extra minute(s)';
                $this->selectedSessions = [];
                $this->extraTimeMinutes = 20; // Reset to default
                
                // Log the action
                Log::info('Extra time added', [
                    'user_id' => Auth::id(),
                    'exam_id' => $this->exam_id,
                    'minutes_added' => $this->extraTimeMinutes,
                    'sessions_affected' => $sessionsUpdated,
                    'applied_to_all' => $this->applyToAll,
                    'included_completed' => $this->includeCompletedSessions
                ]);
            } else {
                $this->errorMessage = 'No sessions were updated. Please check your selections.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            Log::error('Error adding extra time', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'exam_id' => $this->exam_id
            ]);
        }
    }
    
    public function toggleSessionSelection($sessionId)
    {
        if (in_array($sessionId, $this->selectedSessions)) {
            $this->selectedSessions = array_diff($this->selectedSessions, [$sessionId]);
        } else {
            $this->selectedSessions[] = $sessionId;
        }
    }

    /**
     * Open the view session modal
     */
    public function viewSession($sessionId)
    {
        try {
            $this->viewingSession = ExamSession::with([
                'student', 
                'exam.course', 
                'extraTimeAddedBy',
                'responses.question.options'
            ])->findOrFail($sessionId);
            
            $this->showViewModal = true;
            
            Log::info('Viewing exam session details', [
                'user_id' => Auth::id(),
                'session_id' => $sessionId
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading session details: ' . $e->getMessage();
            Log::error('Error viewing exam session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'user_id' => Auth::id()
            ]);
        }
    }
    
    /**
     * Close the view session modal
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingSession = null;
    }

    /**
     * Add extra time from the modal view
     */
    public function addExtraTimeFromModal()
    {
        if (!$this->viewingSession) {
            $this->errorMessage = 'Session details not found.';
            return;
        }
        
        try {
            $now = Carbon::now();
            $sessionId = $this->viewingSession->id;
            
            // Validate the extra time value
            if ($this->extraTimeMinutes < 1 || $this->extraTimeMinutes > 60) {
                $this->errorMessage = 'Extra time must be between 1 and 60 minutes.';
                return;
            }
            
            // Update the session with extra time
            $updates = [
                'extra_time_minutes' => \DB::raw('extra_time_minutes + ' . $this->extraTimeMinutes),
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now,
            ];
            
            // If adding time to a completed session, we need to set it to allow continuation
            $isCompleted = $this->viewingSession->completed_at && !Carbon::parse($this->viewingSession->completed_at)->isFuture();
            
            // Include reactivation info in log for completed sessions
            $isReactivation = false;

            // For completed or expired sessions, update the completion time to allow continuation
            if ($isCompleted || now()->gt($this->viewingSession->adjustedCompletionTime)) {
                // Set the new end time based on current time plus extra time
                $newEndTime = $now->copy()->addMinutes($this->extraTimeMinutes);
                $updates['completed_at'] = $newEndTime;
                $isReactivation = true;
            }
            
            // Update the session
            $updated = ExamSession::where('id', $sessionId)->update($updates);
            
            if ($updated) {
                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student', 
                    'exam.course', 
                    'extraTimeAddedBy',
                    'responses.question.options'
                ])->find($sessionId);
                
                $message = 'Successfully added ' . $this->extraTimeMinutes . ' extra minute(s) to the exam session.';
                
                if ($isReactivation) {
                    $message .= ' The session has been reactivated for the student to continue.';
                }
                
                $this->successMessage = $message;
                
                // Reset extra time minutes to default
                $this->extraTimeMinutes = 20;
                
                // Log the action with additional reactivation info
                Log::info('Extra time added from modal', [
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'minutes_added' => $this->extraTimeMinutes,
                    'student_id' => $this->viewingSession->student_id ?? null,
                    'exam_id' => $this->viewingSession->exam_id ?? null,
                    'reactivated' => $isReactivation
                ]);
            } else {
                $this->errorMessage = 'No changes were made.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            Log::error('Error adding extra time from modal', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id()
            ]);
        }
    }

    /**
     * Find a student by their college ID and get their associated user account
     */
    public function findStudentByCollegeId($studentCollegeId)
    {
        try {
            // Reset student-related properties
            $this->foundStudent = null;
            $this->foundUser = null;
            $this->studentFound = false;
            
            // Find student by student_id (college ID)
            $student = Student::where('student_id', 'like', '%' . $studentCollegeId . '%')->first();
            
            if ($student) {
                $this->foundStudent = $student;
                $this->studentFound = true;
                
                // Find associated user account by email
                $user = User::where('email', $student->email)->first();
                if ($user) {
                    $this->foundUser = $user;
                    return $user->id; // Return the user ID for use in queries
                } else {
                    // Log warning that student has no user account
                    Log::warning('Student found but has no associated user account', [
                        'student_id' => $student->id,
                        'student_college_id' => $student->student_id,
                        'email' => $student->email
                    ]);
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error finding student', [
                'error' => $e->getMessage(),
                'student_id' => $studentCollegeId
            ]);
            return null;
        }
    }
    
    public function render()
    {
        $exams = Exam::where('status', 'active')
                   ->orWhere('status', 'upcoming')
                   ->with('course')
                   ->orderBy('created_at', 'desc')
                   ->get();
                   
        return view('livewire.admin.exam-extra-time', [
            'exams' => $exams,
            'examSessions' => $this->examSessions
        ]);
    }
}
