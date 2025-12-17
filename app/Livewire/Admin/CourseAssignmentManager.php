<?php

namespace App\Livewire\Admin;

use App\Models\CourseLecturer;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CourseAssignmentManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filter properties
    public $searchLecturer = '';

    public $searchCourse = '';

    public $selectedLecturerId = null;

    public $selectedCourseId = null;

    // Modal properties
    public $showAssignModal = false;

    public $assignLecturerId = null;

    public $assignCourseIds = [];

    // Modal search
    public $modalSearchCourse = '';

    // Modal filters
    public $modalFilterProgramId = '';
    public $modalFilterSemesterId = '';
    public $modalFilterYearId = '';

    protected $queryString = [
        'searchLecturer' => ['except' => ''],
        'searchCourse' => ['except' => ''],
    ];

    public function mount()
    {
        // Check if user is authorized
        if (! Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin', 'System'])) {
            abort(403, 'Unauthorized access');
        }
    }

    public function updatedSearchLecturer()
    {
        $this->resetPage();
    }

    public function updatedSearchCourse()
    {
        $this->resetPage();
    }

    public function openAssignModal($lecturerId = null)
    {
        $this->assignLecturerId = $lecturerId;
        $this->assignCourseIds = [];

        // Pre-load assigned courses if lecturer is selected
        if ($lecturerId) {
            $lecturer = User::find($lecturerId);
            if ($lecturer) {
                $this->assignCourseIds = $lecturer->assignedCourses()->pluck('subjects.id')->toArray();
            }
        }

        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assignLecturerId = null;
        $this->assignCourseIds = [];
        $this->modalSearchCourse = '';
        $this->modalFilterProgramId = '';
        $this->modalFilterSemesterId = '';
        $this->modalFilterYearId = '';
    }

    public function assignCourses()
    {
        try {
            $this->validate([
                'assignLecturerId' => 'required|exists:users,id',
                'assignCourseIds' => 'nullable|array',
                'assignCourseIds.*' => 'exists:subjects,id',
            ]);

            $lecturer = User::findOrFail($this->assignLecturerId);

            // Sync the courses (this will remove assignments not in the array and add new ones)
            $lecturer->assignedCourses()->sync($this->assignCourseIds ?? []);

            session()->flash('success', 'Course assignments updated successfully!');
            $this->closeAssignModal();

            Log::info('Courses assigned to lecturer', [
                'lecturer_id' => $this->assignLecturerId,
                'course_ids' => $this->assignCourseIds,
                'assigned_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning courses', [
                'error' => $e->getMessage(),
                'lecturer_id' => $this->assignLecturerId,
            ]);

            session()->flash('error', 'Failed to assign courses. Please try again.');
        }
    }

    public function removeCourseAssignment($lecturerId, $courseId)
    {
        try {
            CourseLecturer::where('user_id', $lecturerId)
                ->where('subject_id', $courseId)
                ->delete();

            session()->flash('success', 'Course assignment removed successfully!');

            Log::info('Course assignment removed', [
                'lecturer_id' => $lecturerId,
                'course_id' => $courseId,
                'removed_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing course assignment', [
                'error' => $e->getMessage(),
                'lecturer_id' => $lecturerId,
                'course_id' => $courseId,
            ]);

            session()->flash('error', 'Failed to remove assignment. Please try again.');
        }
    }

    public function render()
    {
        // Get lecturers (users with Lecturer role)
        $lecturersQuery = User::role('Lecturer')
            ->with(['assignedCourses']);

        if ($this->searchLecturer) {
            $lecturersQuery->where(function ($q) {
                $q->where('name', 'like', '%'.$this->searchLecturer.'%')
                    ->orWhere('email', 'like', '%'.$this->searchLecturer.'%');
            });
        }

        $lecturers = $lecturersQuery->paginate(15);

        // Get all courses for assignment modal with search and filters
        $allCoursesQuery = Subject::with(['semester', 'year', 'collegeClass']);

        if ($this->modalSearchCourse) {
            $allCoursesQuery->where(function ($q) {
                $q->where('course_code', 'like', '%'.$this->modalSearchCourse.'%')
                    ->orWhere('name', 'like', '%'.$this->modalSearchCourse.'%');
            });
        }

        // Apply filters
        if ($this->modalFilterProgramId) {
            $allCoursesQuery->where('college_class_id', $this->modalFilterProgramId);
        }

        if ($this->modalFilterSemesterId) {
            $allCoursesQuery->where('semester_id', $this->modalFilterSemesterId);
        }

        if ($this->modalFilterYearId) {
            $allCoursesQuery->where('year_id', $this->modalFilterYearId);
        }

        $allCourses = $allCoursesQuery->orderBy('course_code')->get();

        // Get filter options
        $programs = \App\Models\CollegeClass::orderBy('name')->get();
        $semesters = \App\Models\Semester::orderBy('name')->get();
        $years = \App\Models\Year::orderBy('name')->get();

        return view('livewire.admin.course-assignment-manager', [
            'lecturers' => $lecturers,
            'allCourses' => $allCourses,
            'programs' => $programs,
            'semesters' => $semesters,
            'years' => $years,
        ]);
    }
}
