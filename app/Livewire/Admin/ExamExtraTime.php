<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;  // Add DB facade import
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
    public $extraTimeMinutes = 20; // Default of 20 minutes
    public $applyToAll = false;
    public $includeCompletedSessions = false; // Control whether to include completed sessions
    
    // Store multiple found students
    public $foundStudents = [];
    public $foundUserIds = [];
    public $processingSearch = false;
    
    // Properties for tracking success/error messages
    public $successMessage = '';
    public $errorMessage = '';
    
    // For selections
    public $selectedSessions = [];
    public $perPage = 10;

    // Modal related properties
    public $showViewModal = false;
    public $viewingSession = null;
    
    // New properties for session restoration feature
    public $restoreSession = false;
    public $restoreMinutes = 30;
    public $restoreReason = '';
    
    // New properties for extra time management
    public $modifyExtraTime = false;
    public $newExtraTimeValue = 0;
    
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
    
    public function updatedSearch()
    {
        // Clear previous found students when search is updated
        if (strlen($this->search) >= 3) {
            $this->processMultipleStudentIds();
        } else {
            $this->foundStudents = [];
            $this->foundUserIds = [];
        }
    }
    
    public function processMultipleStudentIds()
    {
        $this->processingSearch = true;
        $this->foundStudents = [];
        $this->foundUserIds = [];
        
        // Split the search input by newlines and spaces
        $studentIdList = preg_split('/[\s,]+/', $this->search);
        $studentIdList = array_filter($studentIdList); // Remove empty entries
        
        if (count($studentIdList) > 0) {
            foreach ($studentIdList as $studentId) {
                $studentId = trim($studentId);
                if (strlen($studentId) >= 3) {
                    $this->findStudentByCollegeId($studentId);
                }
            }
        }
        
        $this->processingSearch = false;
    }
    
    public function resetSessions()
    {
        $this->selectedSessions = [];
        $this->search = '';
        $this->foundStudents = [];
        $this->foundUserIds = [];
        $this->resetPage();
    }
    
    public function getExamSessionsProperty()
    {
        if (!$this->exam_id) {
            return collect();
        }
        
        $query = ExamSession::where('exam_id', $this->exam_id);
        
        // Apply search filter if we have found user IDs
        if (!empty($this->foundUserIds)) {
            $query->whereIn('student_id', $this->foundUserIds);
        } elseif ($this->search && strlen($this->search) >= 3) {
            // Fallback to direct search in user accounts if no students explicitly found
            $query->whereHas('student', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
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
                
                // Filter by found user IDs if we have them
                if (!empty($this->foundUserIds)) {
                    $query->whereIn('student_id', $this->foundUserIds);
                }
                
                // Only filter by completed_at if we're not including completed sessions
                if (!$this->includeCompletedSessions) {
                    $query->whereNull('completed_at');
                }
                
                // Apply extra time to filtered sessions
                $sessionsUpdated = $query->update([
                    'extra_time_minutes' => DB::raw('extra_time_minutes + ' . $this->extraTimeMinutes),
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
                                       'extra_time_minutes' => DB::raw('extra_time_minutes + ' . $this->extraTimeMinutes),
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
                    'included_completed' => $this->includeCompletedSessions,
                    'student_count' => count($this->foundUserIds)
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
            
            // Ensure extra time minutes is an integer
            $extraTimeMinutes = (int) $this->extraTimeMinutes;
            
            // Update the session with extra time
            $updates = [
                'extra_time_minutes' => DB::raw('extra_time_minutes + ' . $extraTimeMinutes),
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
                $newEndTime = $now->copy()->addMinutes($extraTimeMinutes);
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
                
                $message = 'Successfully added ' . $extraTimeMinutes . ' extra minute(s) to the exam session.';
                
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
                    'minutes_added' => $extraTimeMinutes,
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
     * Toggle the restore session form
     */
    public function toggleRestoreForm()
    {
        $this->restoreSession = !$this->restoreSession;
        
        if (!$this->restoreSession) {
            $this->restoreMinutes = 30;
            $this->restoreReason = '';
        }
    }

    /**
     * Restore an expired exam session to allow student login
     */
    public function restoreExamSession()
    {
        if (!$this->viewingSession) {
            $this->errorMessage = 'Session details not found.';
            return;
        }

        $this->validate([
            'restoreMinutes' => 'required|integer|min:5|max:120',
            'restoreReason' => 'required|string|min:5'
        ], [
            'restoreMinutes.required' => 'Please specify restoration time in minutes',
            'restoreMinutes.min' => 'Restoration time must be at least 5 minutes',
            'restoreMinutes.max' => 'Restoration time cannot exceed 120 minutes',
            'restoreReason.required' => 'Please provide a reason for restoring this exam session',
            'restoreReason.min' => 'Reason must be at least 5 characters'
        ]);
        
        try {
            $now = Carbon::now();
            $sessionId = $this->viewingSession->id;
            
            // Ensure restore minutes is an integer
            $restoreMinutes = (int) $this->restoreMinutes;
            
            // Calculate new end time based on current time plus restore minutes
            $newEndTime = $now->copy()->addMinutes($restoreMinutes);
            
            // Update the session to allow student to continue
            $updates = [
                'completed_at' => $newEndTime, // Set to future time to make it active
                'extra_time_minutes' => DB::raw('extra_time_minutes + ' . $restoreMinutes),
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now,
                'auto_submitted' => false, // Reset auto-submission flag
            ];
            
            $updated = ExamSession::where('id', $sessionId)->update($updates);
            
            if ($updated) {
                // Log the restoration event with reason
                Log::info('Exam session restored', [
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'minutes_given' => $restoreMinutes,
                    'student_id' => $this->viewingSession->student_id ?? null,
                    'exam_id' => $this->viewingSession->exam_id ?? null,
                    'reason' => $this->restoreReason,
                    'new_end_time' => $newEndTime->toDateTimeString()
                ]);
                
                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student', 
                    'exam.course', 
                    'extraTimeAddedBy',
                    'responses.question.options'
                ])->find($sessionId);
                
                $this->successMessage = 'Session successfully restored. The student can now log in and continue the exam for the next ' . 
                    $restoreMinutes . ' minutes.';
                    
                // Reset form fields
                $this->restoreMinutes = 30;
                $this->restoreReason = '';
                $this->restoreSession = false;
            } else {
                $this->errorMessage = 'Failed to restore exam session.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            Log::error('Error restoring exam session', [
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
            // Find student by student_id (college ID)
            $student = Student::where('student_id', 'like', '%' . $studentCollegeId . '%')->first();
            
            if ($student) {
                // Find associated user account by email
                $user = User::where('email', $student->email)->first();
                
                // Add to found students collection (avoid duplicates)
                $studentData = [
                    'student' => $student,
                    'user' => $user,
                    'has_user_account' => !is_null($user)
                ];
                
                // Use student's ID as the key to avoid duplicates
                $this->foundStudents[$student->id] = $studentData;
                
                // If user account exists, add to the found user IDs for filtering
                if ($user) {
                    if (!in_array($user->id, $this->foundUserIds)) {
                        $this->foundUserIds[] = $user->id;
                    }
                } else {
                    // Log warning that student has no user account
                    Log::warning('Student found but has no associated user account', [
                        'student_id' => $student->id,
                        'student_college_id' => $student->student_id,
                        'email' => $student->email
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error finding student', [
                'error' => $e->getMessage(),
                'student_id' => $studentCollegeId
            ]);
        }
    }
    
    // Remove a student from the found list
    public function removeStudent($studentId)
    {
        if (isset($this->foundStudents[$studentId])) {
            $userId = $this->foundStudents[$studentId]['user']->id ?? null;
            
            // Remove from foundUserIds array if exists
            if ($userId && in_array($userId, $this->foundUserIds)) {
                $this->foundUserIds = array_diff($this->foundUserIds, [$userId]);
            }
            
            // Remove from foundStudents array
            unset($this->foundStudents[$studentId]);
        }
    }
    
    // Select all sessions for found students
    public function selectAllFoundSessions()
    {
        $this->selectedSessions = [];
        
        if (!empty($this->foundUserIds)) {
            $sessions = ExamSession::where('exam_id', $this->exam_id)
                ->whereIn('student_id', $this->foundUserIds)
                ->pluck('id')
                ->toArray();
                
            if (!empty($sessions)) {
                $this->selectedSessions = $sessions;
            }
        }
    }

    /**
     * Toggle the extra time modification form
     */
    public function toggleModifyExtraTimeForm()
    {
        $this->modifyExtraTime = !$this->modifyExtraTime;
        
        if ($this->modifyExtraTime && $this->viewingSession) {
            // Initialize with current extra time value for editing
            $this->newExtraTimeValue = $this->viewingSession->extra_time_minutes;
        }
    }
    
    /**
     * Update the extra time for a session (modify existing value)
     */
    public function updateExtraTime()
    {
        if (!$this->viewingSession) {
            $this->errorMessage = 'Session details not found.';
            return;
        }
        
        $this->validate([
            'newExtraTimeValue' => 'required|integer|min:0|max:120'
        ], [
            'newExtraTimeValue.required' => 'Please specify the new extra time in minutes',
            'newExtraTimeValue.min' => 'Extra time cannot be negative',
            'newExtraTimeValue.max' => 'Extra time cannot exceed 120 minutes'
        ]);
        
        try {
            $now = Carbon::now();
            $sessionId = $this->viewingSession->id;
            $currentExtraTime = $this->viewingSession->extra_time_minutes;
            
            // Calculate time difference
            $timeDiff = $this->newExtraTimeValue - $currentExtraTime;
            
            // Don't proceed if there's no change
            if ($timeDiff == 0) {
                $this->errorMessage = 'No changes were made to the extra time.';
                return;
            }
            
            // Prepare update data
            $updates = [
                'extra_time_minutes' => $this->newExtraTimeValue,
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now
            ];
            
            // For expired or completed sessions that are getting more time, reactivate them
            $isExpired = $this->viewingSession->completed_at && 
                        (Carbon::parse($this->viewingSession->completed_at)->isPast() || 
                        Carbon::now()->gt($this->viewingSession->adjustedCompletionTime));
            
            $isBeingIncreased = $timeDiff > 0;
            
            if ($isExpired && $isBeingIncreased) {
                // Calculate new end time based on current time plus the new extra time
                $newEndTime = $now->copy()->addMinutes($this->newExtraTimeValue);
                $updates['completed_at'] = $newEndTime;
                $updates['auto_submitted'] = false; // Reset auto-submission flag
            }
            
            // Update the session with the new extra time
            $updated = ExamSession::where('id', $sessionId)->update($updates);
            
            if ($updated) {
                // Create appropriate success message
                $action = $timeDiff > 0 ? 'increased by ' . $timeDiff : 'reduced by ' . abs($timeDiff);
                
                $this->successMessage = "Extra time successfully {$action} minutes.";
                
                if ($isExpired && $isBeingIncreased) {
                    $this->successMessage .= ' The session has been reactivated for the student to continue.';
                }
                
                // Log the action
                Log::info('Extra time modified', [
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'previous_time' => $currentExtraTime,
                    'new_time' => $this->newExtraTimeValue,
                    'time_difference' => $timeDiff,
                    'student_id' => $this->viewingSession->student_id ?? null,
                    'exam_id' => $this->viewingSession->exam_id ?? null,
                    'session_reactivated' => $isExpired && $isBeingIncreased
                ]);
                
                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student', 
                    'exam.course', 
                    'extraTimeAddedBy',
                    'responses.question.options'
                ])->find($sessionId);
                
                // Reset form
                $this->modifyExtraTime = false;
            } else {
                $this->errorMessage = 'Failed to update extra time.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            Log::error('Error updating extra time', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id()
            ]);
        }
    }
    
    /**
     * Remove all extra time from a session
     */
    public function removeExtraTime()
    {
        if (!$this->viewingSession) {
            $this->errorMessage = 'Session details not found.';
            return;
        }
        
        try {
            $now = Carbon::now();
            $sessionId = $this->viewingSession->id;
            $currentExtraTime = $this->viewingSession->extra_time_minutes;
            
            if ($currentExtraTime <= 0) {
                $this->errorMessage = 'This session has no extra time to remove.';
                return;
            }
            
            // Update the session to remove extra time
            $updated = ExamSession::where('id', $sessionId)->update([
                'extra_time_minutes' => 0,
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now
            ]);
            
            if ($updated) {
                // Log the action
                Log::info('Extra time removed', [
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'removed_minutes' => $currentExtraTime,
                    'student_id' => $this->viewingSession->student_id ?? null,
                    'exam_id' => $this->viewingSession->exam_id ?? null
                ]);
                
                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student', 
                    'exam.course', 
                    'extraTimeAddedBy',
                    'responses.question.options'
                ])->find($sessionId);
                
                $this->successMessage = "Successfully removed {$currentExtraTime} minutes of extra time.";
            } else {
                $this->errorMessage = 'Failed to remove extra time.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
            Log::error('Error removing extra time', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id()
            ]);
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
            'examSessions' => $this->examSessions,
            'foundStudentsCount' => count($this->foundStudents)
        ]);
    }
}
