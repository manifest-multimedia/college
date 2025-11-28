<?php

namespace App\Livewire\Admin;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // Add DB facade import
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ExamExtraTime extends Component
{
    use AuthorizesRequests, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties for filtering and selection
    public $exam_id = null;

    public $search = '';

    public $extraTimeMinutes = 20; // Default of 20 minutes

    public $applyToAll = false;

    public $includeCompletedSessions = false; // Control whether to include completed sessions

    public $sessionStateFilter = 'all'; // 'active', 'completed', 'expired', 'all'

    // Resume session properties
    public $showBulkResumeModal = false;
    
    public $bulkResumeMinutes = 30;
    
    public $bulkResumeReason = '';
    
    // Individual resume modal properties
    public $showIndividualResumeModal = false;
    
    public $resumingSessionId = null;
    
    public $individualResumeMinutes = 30;
    
    public $individualResumeReason = '';

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
        'bulkResumeMinutes' => 'required|integer|min:5|max:120',
        'bulkResumeReason' => 'required|string|min:5',
        'individualResumeMinutes' => 'required|integer|min:5|max:120',
        'individualResumeReason' => 'required|string|min:5',
    ];

    protected $messages = [
        'selectedSessions.required_if' => 'Please select at least one exam session',
        'extraTimeMinutes.min' => 'Extra time must be at least 1 minute',
        'extraTimeMinutes.max' => 'Extra time cannot exceed 60 minutes',
        'bulkResumeMinutes.required' => 'Please specify resume time in minutes',
        'bulkResumeMinutes.min' => 'Resume time must be at least 5 minutes',
        'bulkResumeMinutes.max' => 'Resume time cannot exceed 120 minutes',
        'bulkResumeReason.required' => 'Please provide a reason for resuming sessions',
        'bulkResumeReason.min' => 'Reason must be at least 5 characters',
        'individualResumeMinutes.required' => 'Please specify resume time in minutes',
        'individualResumeMinutes.min' => 'Resume time must be at least 5 minutes',
        'individualResumeMinutes.max' => 'Resume time cannot exceed 120 minutes',
        'individualResumeReason.required' => 'Please provide a reason for resuming this session',
        'individualResumeReason.min' => 'Reason must be at least 5 characters',
    ];

    public function mount()
    {
        // Ensure only authorized users can access this component
        $allowedRoles = ['System', 'Administrator', 'Super Admin', 'Lecturer'];

        if (! Auth::user() || ! Auth::user()->hasAnyRole($allowedRoles)) {
            Log::warning('Unauthorized access attempt to ExamExtraTime component', [
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
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
        
        if ($field === 'sessionStateFilter') {
            // Reset selected sessions when filter changes
            $this->selectedSessions = [];
            $this->resetPage();
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
        if (! $this->exam_id) {
            return collect();
        }

        // Get the exam to access its duration
        $exam = Exam::find($this->exam_id);
        if (!$exam) {
            return collect();
        }

        $query = ExamSession::where('exam_id', $this->exam_id);

        // Apply search filter if we have found user IDs
        if (! empty($this->foundUserIds)) {
            $query->whereIn('student_id', $this->foundUserIds);
        } elseif ($this->search && strlen($this->search) >= 3) {
            // Fallback to direct search in user accounts if no students explicitly found
            $query->whereHas('student', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        // Get sessions with student and exam info
        $sessions = $query->with(['student', 'exam.course'])
            ->orderBy('started_at', 'desc')
            ->get();

        // Apply session state filtering after fetching
        // This is more reliable than complex raw SQL queries
        $now = now();
        $filteredSessions = $sessions->filter(function($session) use ($now, $exam) {
            $status = $this->getSessionStatus($session);
            
            switch ($this->sessionStateFilter) {
                case 'active':
                    return $status === 'active';
                case 'completed':
                    return $status === 'completed';
                case 'expired':
                    return $status === 'expired';
                case 'all':
                default:
                    return true;
            }
        });

        // Manually paginate the filtered collection
        $page = request()->get('page', 1);
        $perPage = $this->perPage;
        $offset = ($page - 1) * $perPage;
        
        $paginatedItems = $filteredSessions->slice($offset, $perPage)->values();
        
        return new LengthAwarePaginator(
            $paginatedItems,
            $filteredSessions->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function addExtraTime()
    {
        // Clear previous messages
        $this->successMessage = '';
        $this->errorMessage = '';

        // Validate exam_id is selected
        if (!$this->exam_id) {
            $this->errorMessage = 'Please select an exam first.';
            return;
        }

        // Validate extra time minutes
        if ($this->extraTimeMinutes < 1 || $this->extraTimeMinutes > 60) {
            $this->errorMessage = 'Extra time must be between 1 and 60 minutes.';
            return;
        }

        // If not applying to all, validate that sessions are selected
        if (!$this->applyToAll && empty($this->selectedSessions)) {
            $this->errorMessage = 'Please select at least one exam session or enable "Apply to all active sessions".';
            return;
        }

        try {
            $sessionsUpdated = 0;
            $now = Carbon::now();

            if ($this->applyToAll) {
                // Build query for all sessions for this exam
                $query = ExamSession::where('exam_id', $this->exam_id);

                // Filter by found user IDs if we have them
                if (! empty($this->foundUserIds)) {
                    $query->whereIn('student_id', $this->foundUserIds);
                }

                // Only filter by completed_at if we're not including completed sessions
                if (! $this->includeCompletedSessions) {
                    $query->whereNull('completed_at');
                }

                // Apply extra time to filtered sessions
                $sessionsUpdated = $query->update([
                    'extra_time_minutes' => DB::raw('extra_time_minutes + '.$this->extraTimeMinutes),
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
                        'extra_time_minutes' => DB::raw('extra_time_minutes + '.$this->extraTimeMinutes),
                        'extra_time_added_by' => Auth::id(),
                        'extra_time_added_at' => $now,
                    ]);
            }

            if ($sessionsUpdated > 0) {
                $this->successMessage = $sessionsUpdated.' exam session(s) were granted '.$this->extraTimeMinutes.' extra minute(s)';
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
                    'student_count' => count($this->foundUserIds),
                ]);
            } else {
                $this->errorMessage = 'No sessions were updated. Please check your selections.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: '.$e->getMessage();
            Log::error('Error adding extra time', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'exam_id' => $this->exam_id,
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
                'responses.question.options',
            ])->findOrFail($sessionId);

            $this->showViewModal = true;

            Log::info('Viewing exam session details', [
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading session details: '.$e->getMessage();
            Log::error('Error viewing exam session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
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
        if (! $this->viewingSession) {
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
                'extra_time_minutes' => DB::raw('extra_time_minutes + '.$extraTimeMinutes),
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now,
            ];

            // If adding time to a completed session, we need to set it to allow continuation
            $isCompleted = $this->viewingSession->completed_at && ! Carbon::parse($this->viewingSession->completed_at)->isFuture();

            // Include reactivation info in log for completed sessions
            $isReactivation = false;

            // For completed or expired sessions, update the completion time to allow continuation
            $adjustedTime = $this->viewingSession->adjustedCompletionTime;
            if ($isCompleted || ($adjustedTime && now()->gt($adjustedTime))) {
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
                    'responses.question.options',
                ])->find($sessionId);

                $message = 'Successfully added '.$extraTimeMinutes.' extra minute(s) to the exam session.';

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
                    'reactivated' => $isReactivation,
                ]);
            } else {
                $this->errorMessage = 'No changes were made.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: '.$e->getMessage();
            Log::error('Error adding extra time from modal', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Toggle the restore session form
     */
    public function toggleRestoreForm()
    {
        $this->restoreSession = ! $this->restoreSession;

        if (! $this->restoreSession) {
            $this->restoreMinutes = 30;
            $this->restoreReason = '';
        }
    }

    /**
     * Restore an expired exam session to allow student login
     */
    public function restoreExamSession()
    {
        if (! $this->viewingSession) {
            $this->errorMessage = 'Session details not found.';

            return;
        }

        $this->validate([
            'restoreMinutes' => 'required|integer|min:5|max:120',
            'restoreReason' => 'required|string|min:5',
        ], [
            'restoreMinutes.required' => 'Please specify restoration time in minutes',
            'restoreMinutes.min' => 'Restoration time must be at least 5 minutes',
            'restoreMinutes.max' => 'Restoration time cannot exceed 120 minutes',
            'restoreReason.required' => 'Please provide a reason for restoring this exam session',
            'restoreReason.min' => 'Reason must be at least 5 characters',
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
                'extra_time_minutes' => DB::raw('extra_time_minutes + '.$restoreMinutes),
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
                    'new_end_time' => $newEndTime->toDateTimeString(),
                ]);

                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student',
                    'exam.course',
                    'extraTimeAddedBy',
                    'responses.question.options',
                ])->find($sessionId);

                $this->successMessage = 'Session successfully restored. The student can now log in and continue the exam for the next '.
                    $restoreMinutes.' minutes.';

                // Reset form fields
                $this->restoreMinutes = 30;
                $this->restoreReason = '';
                $this->restoreSession = false;
            } else {
                $this->errorMessage = 'Failed to restore exam session.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: '.$e->getMessage();
            Log::error('Error restoring exam session', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id(),
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
            $student = Student::where('student_id', 'like', '%'.$studentCollegeId.'%')->first();

            if ($student) {
                // Find associated user account by email
                $user = User::where('email', $student->email)->first();

                // Add to found students collection (avoid duplicates)
                $studentData = [
                    'student' => $student,
                    'user' => $user,
                    'has_user_account' => ! is_null($user),
                ];

                // Use student's ID as the key to avoid duplicates
                $this->foundStudents[$student->id] = $studentData;

                // If user account exists, add to the found user IDs for filtering
                if ($user) {
                    if (! in_array($user->id, $this->foundUserIds)) {
                        $this->foundUserIds[] = $user->id;
                    }
                } else {
                    // Log warning that student has no user account
                    Log::warning('Student found but has no associated user account', [
                        'student_id' => $student->id,
                        'student_college_id' => $student->student_id,
                        'email' => $student->email,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error finding student', [
                'error' => $e->getMessage(),
                'student_id' => $studentCollegeId,
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

        if (! empty($this->foundUserIds)) {
            $sessions = ExamSession::where('exam_id', $this->exam_id)
                ->whereIn('student_id', $this->foundUserIds)
                ->pluck('id')
                ->toArray();

            if (! empty($sessions)) {
                $this->selectedSessions = $sessions;
            }
        }
    }

    /**
     * Toggle the extra time modification form
     */
    public function toggleModifyExtraTimeForm()
    {
        $this->modifyExtraTime = ! $this->modifyExtraTime;

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
        if (! $this->viewingSession) {
            $this->errorMessage = 'Session details not found.';

            return;
        }

        $this->validate([
            'newExtraTimeValue' => 'required|integer|min:0|max:120',
        ], [
            'newExtraTimeValue.required' => 'Please specify the new extra time in minutes',
            'newExtraTimeValue.min' => 'Extra time cannot be negative',
            'newExtraTimeValue.max' => 'Extra time cannot exceed 120 minutes',
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
                'extra_time_added_at' => $now,
            ];

            // For expired or completed sessions that are getting more time, reactivate them
            $adjustedTime = $this->viewingSession->adjustedCompletionTime;
            $isExpired = $this->viewingSession->completed_at &&
                        (Carbon::parse($this->viewingSession->completed_at)->isPast() ||
                        ($adjustedTime && Carbon::now()->gt($adjustedTime)));

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
                $action = $timeDiff > 0 ? 'increased by '.$timeDiff : 'reduced by '.abs($timeDiff);

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
                    'session_reactivated' => $isExpired && $isBeingIncreased,
                ]);

                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student',
                    'exam.course',
                    'extraTimeAddedBy',
                    'responses.question.options',
                ])->find($sessionId);

                // Reset form
                $this->modifyExtraTime = false;
            } else {
                $this->errorMessage = 'Failed to update extra time.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: '.$e->getMessage();
            Log::error('Error updating extra time', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Remove all extra time from a session
     */
    public function removeExtraTime()
    {
        if (! $this->viewingSession) {
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
                'extra_time_added_at' => $now,
            ]);

            if ($updated) {
                // Log the action
                Log::info('Extra time removed', [
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'removed_minutes' => $currentExtraTime,
                    'student_id' => $this->viewingSession->student_id ?? null,
                    'exam_id' => $this->viewingSession->exam_id ?? null,
                ]);

                // Refresh the session data
                $this->viewingSession = ExamSession::with([
                    'student',
                    'exam.course',
                    'extraTimeAddedBy',
                    'responses.question.options',
                ])->find($sessionId);

                $this->successMessage = "Successfully removed {$currentExtraTime} minutes of extra time.";
            } else {
                $this->errorMessage = 'Failed to remove extra time.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: '.$e->getMessage();
            Log::error('Error removing extra time', [
                'error' => $e->getMessage(),
                'session_id' => $this->viewingSession->id ?? null,
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Get session status (active, completed, expired)
     */
    public function getSessionStatus($session)
    {
        $now = now();
        
        // Check if session is completed (student has submitted)
        // A session is completed when completed_at is set and is in the past
        // Score may not be available yet if grading is pending
        if ($session->completed_at && $session->completed_at->isPast()) {
            return 'completed';
        }
        
        // Get the adjusted completion time (includes extra time)
        $adjustedTime = $session->adjustedCompletionTime;
        
        // If no adjusted time, session might be corrupted, treat as active
        if (!$adjustedTime) {
            return 'active';
        }
        
        // Check if time has expired (but not yet submitted)
        if ($now->gt($adjustedTime)) {
            return 'expired';
        }
        
        // Session is still within time limit
        return 'active';
    }
    
    /**
     * Show individual resume modal
     */
    public function showIndividualResumeModal($sessionId)
    {
        $session = ExamSession::find($sessionId);
        
        if (!$session) {
            $this->errorMessage = 'Session not found.';
            return;
        }
        
        $status = $this->getSessionStatus($session);
        if ($status === 'active') {
            $this->errorMessage = 'Session is already active and does not need to be resumed.';
            return;
        }
        
        $this->resumingSessionId = $sessionId;
        $this->individualResumeMinutes = 30;
        $this->individualResumeReason = '';
        $this->showIndividualResumeModal = true;
    }
    
    /**
     * Resume individual session with validation
     */
    public function confirmIndividualResume()
    {
        $this->validate([
            'individualResumeMinutes' => 'required|integer|min:5|max:120',
            'individualResumeReason' => 'required|string|min:5',
        ]);
        
        try {
            $session = ExamSession::find($this->resumingSessionId);
            
            if (!$session) {
                $this->errorMessage = 'Session not found.';
                return;
            }
            
            $status = $this->getSessionStatus($session);
            if ($status === 'active') {
                $this->errorMessage = 'Session is already active and does not need to be resumed.';
                return;
            }
            
            $now = now();
            
            // Calculate how much extra time is needed to make the session active
            // adjustedCompletionTime = started_at + exam_duration + extra_time_minutes
            $currentAdjustedEndTime = $session->adjustedCompletionTime;
            
            // Calculate minutes needed to bring the session to current time + desired additional minutes
            $minutesNeeded = 0;
            if ($currentAdjustedEndTime && $currentAdjustedEndTime->lt($now)) {
                // Session is in the past, calculate how many minutes to bring it to now
                $minutesNeeded = (int) abs($currentAdjustedEndTime->diffInMinutes($now));
            }
            
            // Total extra time = minutes needed to reach now + additional minutes requested
            $totalExtraMinutes = (int) ($minutesNeeded + $this->individualResumeMinutes);
            
            // Calculate what the new end time will be
            $newEndTime = $now->copy()->addMinutes((int) $this->individualResumeMinutes);
            
            // Critical: For a session to be resumable, we need to:
            // 1. Set completed_at to null (session is now active, not completed)
            // 2. Reset auto_submitted flag 
            // 3. Reset score to null to allow resubmission
            // 4. Add the proper extra time to make adjustedCompletionTime in the future
            
            $updates = [
                'completed_at' => null, // Critical: Set to null to make session "active"
                'extra_time_minutes' => $session->extra_time_minutes + $totalExtraMinutes,
                'extra_time_added_by' => Auth::id(),
                'extra_time_added_at' => $now,
                'auto_submitted' => false, // Critical: Reset auto-submission flag
                'score' => null, // Critical: Reset score to allow re-submission
                'is_restored' => true, // Mark as restored session
                'restored_at' => $now, // Track when it was restored
            ];
            
            // If the session was completed (has score), we need to "uncomplete" it
            if ($session->score !== null) {
                // For completed sessions, we're essentially reverting the completion
                $updates['score'] = null;
            }
            
            $session->update($updates);
            
            // Refresh the session to get updated values
            $session = $session->fresh();
            
            // Log the resumption with detailed info
            Log::info('Exam session resumed via modal', [
                'session_id' => $this->resumingSessionId,
                'student_id' => $session->student_id,
                'requested_additional_minutes' => $this->individualResumeMinutes,
                'minutes_needed_to_reach_now' => $minutesNeeded,
                'total_extra_minutes_added' => $totalExtraMinutes,
                'reason' => $this->individualResumeReason,
                'resumed_by' => Auth::id(),
                'new_end_time' => $newEndTime->toDateTimeString(),
                'previous_status' => $status,
                'updated_completed_at' => $session->completed_at,
                'updated_score' => $session->score,
                'updated_auto_submitted' => $session->auto_submitted,
                'updated_extra_time_minutes' => $session->extra_time_minutes,
                'adjusted_completion_time' => $session->adjustedCompletionTime ? $session->adjustedCompletionTime->toDateTimeString() : null,
            ]);
            
            $actualExtraMessage = $totalExtraMinutes > $this->individualResumeMinutes ? " (including {$minutesNeeded} minutes to bring session to current time)" : "";
            $endTimeFormatted = $session->adjustedCompletionTime ? $session->adjustedCompletionTime->format('M d, Y g:i A') : 'Not available';
            $this->successMessage = "Session resumed successfully! Student {$session->student->name} can continue for {$this->individualResumeMinutes} minutes{$actualExtraMessage}. New end time: {$endTimeFormatted}. The student should refresh their exam page if they are currently logged in.";
            $this->showIndividualResumeModal = false;
            $this->resumingSessionId = null;
            
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while resuming the session: ' . $e->getMessage();
            Log::error('Error resuming individual session', [
                'error' => $e->getMessage(),
                'session_id' => $this->resumingSessionId,
                'user_id' => Auth::id(),
            ]);
        }
    }
    
    /**
     * Cancel individual resume modal
     */
    public function cancelIndividualResume()
    {
        $this->showIndividualResumeModal = false;
        $this->resumingSessionId = null;
        $this->individualResumeMinutes = 30;
        $this->individualResumeReason = '';
    }
    
    /**
     * Show bulk resume modal
     */
    public function showBulkResumeModal()
    {
        if (empty($this->selectedSessions)) {
            $this->errorMessage = 'Please select at least one session to resume.';
            return;
        }
        
        $this->showBulkResumeModal = true;
        $this->bulkResumeMinutes = 30;
        $this->bulkResumeReason = '';
    }
    
    /**
     * Resume multiple selected sessions
     */
    public function bulkResumeSelected()
    {
        $this->validate([
            'bulkResumeMinutes' => 'required|integer|min:5|max:120',
            'bulkResumeReason' => 'required|string|min:5',
        ]);
        
        if (empty($this->selectedSessions)) {
            $this->errorMessage = 'Please select at least one session to resume.';
            return;
        }
        
        try {
            $resumedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            foreach ($this->selectedSessions as $sessionId) {
                $session = ExamSession::find($sessionId);
                
                if (!$session) {
                    $errorCount++;
                    continue;
                }
                
                $status = $this->getSessionStatus($session);
                if ($status === 'active') {
                    $skippedCount++;
                    continue;
                }
                
                $now = now();
                $newEndTime = $now->copy()->addMinutes($this->bulkResumeMinutes);
                
                // Same logic as individual resume - properly reactivate session
                $updates = [
                    'completed_at' => $newEndTime, // Set to future time to make it "active"
                    'extra_time_minutes' => $session->extra_time_minutes + $this->bulkResumeMinutes,
                    'extra_time_added_by' => Auth::id(),
                    'extra_time_added_at' => $now,
                    'auto_submitted' => false, // Critical: Reset auto-submission flag
                    'score' => null, // Critical: Reset score to allow re-submission
                    'is_restored' => true, // Mark as restored session
                    'restored_at' => $now, // Track when it was restored
                ];
                
                $session->update($updates);
                
                $resumedCount++;
                
                // Log each resumption
                Log::info('Bulk exam session resumed', [
                    'session_id' => $sessionId,
                    'student_id' => $session->student_id,
                    'additional_minutes' => $this->bulkResumeMinutes,
                    'reason' => $this->bulkResumeReason,
                    'resumed_by' => Auth::id(),
                    'new_end_time' => $newEndTime,
                    'previous_status' => $status,
                ]);
            }
            
            // Prepare success message
            $message = "Bulk resume completed: {$resumedCount} session(s) resumed";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} already active session(s) skipped";
            }
            if ($errorCount > 0) {
                $message .= ", {$errorCount} session(s) had errors";
            }
            
            $this->successMessage = $message;
            $this->selectedSessions = [];
            $this->showBulkResumeModal = false;
            
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred during bulk resume: ' . $e->getMessage();
            Log::error('Error in bulk resume', [
                'error' => $e->getMessage(),
                'selected_sessions' => $this->selectedSessions,
                'user_id' => Auth::id(),
            ]);
        }
    }
    
    /**
     * Cancel bulk resume modal
     */
    public function cancelBulkResume()
    {
        $this->showBulkResumeModal = false;
        $this->bulkResumeMinutes = 30;
        $this->bulkResumeReason = '';
    }

    /**
     * Wrapper method to avoid Alpine.js conflicts
     */
    public function openResumeModal($sessionId)
    {
        return $this->showIndividualResumeModal($sessionId);
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
            'foundStudentsCount' => count($this->foundStudents),
        ]);
    }
}
