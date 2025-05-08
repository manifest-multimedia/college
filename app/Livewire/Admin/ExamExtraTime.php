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
    public $student_id = null;
    public $search = '';
    public $extraTimeMinutes = 5; // Default of 5 minutes
    public $applyToAll = false;
    
    // Properties for tracking success/error messages
    public $successMessage = '';
    public $errorMessage = '';
    
    // For selections
    public $selectedSessions = [];
    public $perPage = 10;
    
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
        $this->student_id = null;
        $this->resetPage();
    }
    
    public function getExamSessionsProperty()
    {
        if (!$this->exam_id) {
            return collect();
        }
        
        $query = ExamSession::where('exam_id', $this->exam_id);
                        //    ->whereNull('completed_at'); // Only active sessions
        
        // Apply search filter if provided
        if ($this->search) {
            // Filter by student info
            $query->whereHas('student', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Filter by student ID if provided
        if ($this->student_id) {
            $query->where('student_id', $this->student_id);
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
                // Apply extra time to all active sessions for this exam
                $sessionsUpdated = ExamSession::where('exam_id', $this->exam_id)
                                           ->whereNull('completed_at')
                                           ->update([
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
                $this->extraTimeMinutes = 5; // Reset to default
                
                // Log the action
                Log::info('Extra time added', [
                    'user_id' => Auth::id(),
                    'exam_id' => $this->exam_id,
                    'minutes_added' => $this->extraTimeMinutes,
                    'sessions_affected' => $sessionsUpdated,
                    'applied_to_all' => $this->applyToAll
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
