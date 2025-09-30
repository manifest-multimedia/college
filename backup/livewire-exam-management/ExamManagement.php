<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\QuestionSet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\CollegeClass;
use App\Models\Year;
use App\Models\Semester;
use App\Models\AcademicYear;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExamManagement extends Component
{
    public $course_code;
    public $exam_type = 'mcq'; // Default to 'mcq'
    public $exam_duration;
    public $exam_password;
    public $semester;
    public $class;
    public $year;
    public $academic_year_id;
    public $slug;
    public $user_id;
    public $questions_per_session;
    public $start_date;
    public $end_date;
    public $enable_proctoring = false;
    public $exam_title;
    public $exam_description;
    public $passing_mark;
    
    // Question sets management
    public $availableQuestionSets = [];
    public $selectedQuestionSets = [];
    public $questionSetConfigs = [];
    
    // Status options
    public $status = 'draft';
    public $statusOptions = [
        'draft' => 'Draft',
        'published' => 'Published',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed'
    ];

    // Validation rules for form input
    protected $rules = [
        'course_code' => 'required|exists:subjects,id',
        'exam_type' => 'required|in:mcq,short_answer,essay,mixed',
        'exam_duration' => 'required|integer|min:1',
        'exam_password' => 'nullable|string',
        'questions_per_session' => 'required|integer|min:1',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'exam_title' => 'required|string|max:255',
        'exam_description' => 'nullable|string',
        'passing_mark' => 'nullable|integer|min:0',
        'selectedQuestionSets' => 'required|array|min:1',
        'selectedQuestionSets.*' => 'exists:question_sets,id',
        'questionSetConfigs.*.shuffle_questions' => 'boolean',
        'questionSetConfigs.*.questions_to_pick' => 'nullable|integer|min:1',
        'status' => 'required|in:draft,published,ongoing,completed',
        'academic_year_id' => 'nullable|exists:academic_years,id',
        'semester' => 'nullable|exists:semesters,id',
    ];

    protected $messages = [
        'course_code.required' => 'Please select a course.',
        'exam_type.required' => 'Please select an exam type.',
        'exam_duration.required' => 'Please enter an exam duration.',
        'exam_duration.min' => 'Exam duration must be at least 1 minute.',
        'start_date.required' => 'Please set a start date for the exam.',
        'end_date.required' => 'Please set an end date for the exam.',
        'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        'exam_title.required' => 'Please provide a title for the exam.',
        'selectedQuestionSets.required' => 'Please select at least one question set.',
        'selectedQuestionSets.min' => 'Please select at least one question set.',
    ];

    public function mount()
    {
        if (Auth::user()->role != 'Super Admin') {
            $this->user_id = Auth::user()->id;
        }

        // Set default dates
        $this->start_date = now()->format('Y-m-d\TH:i');
        $this->end_date = now()->addDays(7)->format('Y-m-d\TH:i');
        
        // Automatically generate an exam password
        $this->exam_password = $this->regeneratePassword();
        
        // Set current academic year if available
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if ($currentAcademicYear) {
            $this->academic_year_id = $currentAcademicYear->id;
        }
    }

    public function updatedCourseCode($value)
    {
        if ($value) {
            $this->loadQuestionSets();
            
            // Set a default title based on the course
            $course = Subject::find($value);
            if ($course) {
                $this->exam_title = $course->name . ' ' . ucfirst($this->exam_type) . ' Exam';
            }
        } else {
            $this->availableQuestionSets = [];
            $this->selectedQuestionSets = [];
            $this->questionSetConfigs = [];
        }
    }
    
    // Handle updates to filter fields without affecting question sets
    public function updatedClass($value)
    {
        // Only clear course selection if the current course doesn't belong to the new class
        if ($this->course_code && $value) {
            $currentCourse = Subject::find($this->course_code);
            if ($currentCourse && $currentCourse->college_class_id != $value) {
                $this->course_code = null;
                $this->availableQuestionSets = [];
                $this->selectedQuestionSets = [];
                $this->questionSetConfigs = [];
            }
        }
    }
    
    public function updatedYear($value)
    {
        // Only clear course selection if the current course doesn't belong to the new year
        if ($this->course_code && $value) {
            $currentCourse = Subject::find($this->course_code);
            if ($currentCourse && $currentCourse->year_id != $value) {
                $this->course_code = null;
                $this->availableQuestionSets = [];
                $this->selectedQuestionSets = [];
                $this->questionSetConfigs = [];
            }
        }
    }
    
    public function updatedSemester($value)
    {
        // Only clear course selection if the current course doesn't belong to the new semester
        if ($this->course_code && $value) {
            $currentCourse = Subject::find($this->course_code);
            if ($currentCourse && $currentCourse->semester_id != $value) {
                $this->course_code = null;
                $this->availableQuestionSets = [];
                $this->selectedQuestionSets = [];
                $this->questionSetConfigs = [];
            }
        }
    }
    
    // User selection should not affect question sets
    public function updatedUserId($value)
    {
        // User selection should not affect question sets, so do nothing
        // This method exists to prevent any unwanted side effects
        Log::info('User ID updated', [
            'user_id' => $value,
            'available_question_sets_count' => count($this->availableQuestionSets),
            'selected_question_sets_count' => count($this->selectedQuestionSets)
        ]);
    }

    public function loadQuestionSets()
    {
        if ($this->course_code) {
            $newQuestionSets = QuestionSet::where('course_id', $this->course_code)
                ->withCount('questions')
                ->get();
            
            $this->availableQuestionSets = $newQuestionSets;
            
            // Only reset selections if the available sets have actually changed
            $newSetIds = $newQuestionSets->pluck('id')->toArray();
            $currentSelectedIds = $this->selectedQuestionSets;
            
            // Remove any selected question sets that are no longer available
            $this->selectedQuestionSets = array_intersect($currentSelectedIds, $newSetIds);
            
            // Clean up configurations for sets that are no longer available
            foreach ($this->questionSetConfigs as $setId => $config) {
                if (!in_array($setId, $newSetIds)) {
                    unset($this->questionSetConfigs[$setId]);
                }
            }
        } else {
            $this->availableQuestionSets = [];
            $this->selectedQuestionSets = [];
            $this->questionSetConfigs = [];
        }
    }

    public function toggleQuestionSet($questionSetId)
    {
        if (in_array($questionSetId, $this->selectedQuestionSets)) {
            // Remove from selected
            $this->selectedQuestionSets = array_filter($this->selectedQuestionSets, function($id) use ($questionSetId) {
                return $id != $questionSetId;
            });
            // Remove configuration
            unset($this->questionSetConfigs[$questionSetId]);
        } else {
            // Add to selected
            $this->selectedQuestionSets[] = $questionSetId;
            // Set default configuration
            $this->questionSetConfigs[$questionSetId] = [
                'shuffle_questions' => true,
                'questions_to_pick' => null // null means all questions
            ];
        }
        
        // Re-index the selected array
        $this->selectedQuestionSets = array_values($this->selectedQuestionSets);
    }

    // Handle form submission to create the exam
    public function createExam()
    {
        $this->validate();

        try {
            // Format dates correctly
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);

            $exam = Exam::create([
                'course_id' => $this->course_code,
                'user_id' => $this->user_id ?: Auth::id(),
                'type' => $this->exam_type,
                'exam_type' => $this->exam_type, // For backward compatibility
                'duration' => $this->exam_duration,
                'password' => $this->exam_password,
                'questions_per_session' => $this->questions_per_session,
                'slug' => $this->generateSlug(),
                'status' => $this->status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'enable_proctoring' => $this->enable_proctoring,
                'title' => $this->exam_title,
                'description' => $this->exam_description,
                'passing_mark' => $this->passing_mark,
                'academic_year_id' => $this->academic_year_id,
                'semester_id' => $this->semester,
            ]);

            // Associate question sets with exam
            foreach ($this->selectedQuestionSets as $setId) {
                $config = $this->questionSetConfigs[$setId] ?? [];
                $exam->questionSets()->attach($setId, [
                    'shuffle_questions' => $config['shuffle_questions'] ?? true,
                    'questions_to_pick' => $config['questions_to_pick'] ?? null,
                ]);
            }

            // Reset form
            $this->reset([
                'course_code', 'exam_type', 'exam_duration', 'exam_password',
                'questions_per_session', 'start_date', 'end_date', 'exam_title',
                'exam_description', 'passing_mark', 'selectedQuestionSets',
                'questionSetConfigs', 'status'
            ]);
            
            // Set default dates again
            $this->start_date = now()->format('Y-m-d\TH:i');
            $this->end_date = now()->addDays(7)->format('Y-m-d\TH:i');
            
            // Regenerate password
            $this->exam_password = $this->regeneratePassword();

            // Flash success message and redirect
            session()->flash('message', 'Exam created successfully!');
            return redirect()->route('examcenter');
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database query exceptions (including foreign key constraint violations)
            $errorCode = $e->errorInfo[1] ?? '';
            
            if ($errorCode == 1452) { // Foreign key constraint violation in MySQL
                session()->flash('error', 'Error: The selected course does not exist. Please select a valid course.');
                Log::error('Foreign key constraint violation in ExamManagement:', [
                    'course_id' => $this->course_code,
                    'error' => $e->getMessage()
                ]);
            } else {
                session()->flash('error', 'Database error occurred. Please try again or contact support.');
                Log::error('Database error in ExamManagement:', [
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]);
            }
            
        } catch (\Exception $e) {
            // Handle other general exceptions
            session()->flash('error', 'An unexpected error occurred. Please try again or contact support.');
            Log::error('Unexpected error in ExamManagement:', [
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }

    public function render()
    {
        // Load courses in ascending order
        $coursesQuery = Subject::query();
        
        // Apply filters only if they are set
        if ($this->class) {
            $coursesQuery->where('college_class_id', $this->class);
        }
        if ($this->year) {
            $coursesQuery->where('year_id', $this->year);
        }
        if ($this->semester) {
            $coursesQuery->where('semester_id', $this->semester);
        }
        
        $courses = $coursesQuery->orderBy('name', 'asc')->get();
        
        // If a course is selected but not in the filtered results, add it to maintain selection
        if ($this->course_code && !$courses->contains('id', $this->course_code)) {
            $selectedCourse = Subject::find($this->course_code);
            if ($selectedCourse) {
                $courses->prepend($selectedCourse);
            }
        }
        
        $classes = CollegeClass::all();
        $years = Year::all();
        $semesters = Semester::all();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        // Get staff users using Spatie's role system
        // This queries users who have any role except Student and Parent
        $staffUsers = User::whereHas('roles', function($query) {
                $query->whereNotIn('name', ['Student', 'Parent']);
            })
            ->orWhere(function($query) {
                // Also include users with 'role' column (for backward compatibility)
                $query->whereNotIn('role', ['Student', 'Parent'])
                      ->whereDoesntHave('roles'); // Only if they don't have Spatie roles assigned
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.exam-management', [
            'courses' => $courses,
            'classes' => $classes,
            'years' => $years,
            'semesters' => $semesters,
            'academicYears' => $academicYears,
            'users' => $staffUsers,
            'availableQuestionSets' => $this->availableQuestionSets ?? [],
        ]);
    }

    public function regeneratePassword()
    {
        $password = Str::random(8);

        while (Exam::where('password', $password)->exists()) {
            $password = Str::random(8);
        }

        $this->exam_password = $password;
        return $password;
    }

    public function generateSlug()
    {
        // Generate Unique Slug from Exam Title, Date and Time
        $baseSlug = $this->exam_title ?: ($this->course_code . '-exam');
        $slug = Str::slug($baseSlug . '-' . now()->format('Y-m-d-H-i-s'));
        
        while (Exam::where('slug', $slug)->exists()) {
            $slug = Str::slug($baseSlug . '-' . now()->format('Y-m-d-H-i-s'));
        }
        
        return $slug;
    }
}
