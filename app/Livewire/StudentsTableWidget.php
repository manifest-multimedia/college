<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Cohort;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class StudentsTableWidget extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $programFilter = '';
    public $cohortFilter = '';
    
    // Reset pagination when filters change
    public function updatingSearch() 
    {
        $this->resetPage();
    }
    
    public function updatingProgramFilter()
    {
        $this->resetPage();
    }
    
    public function updatingCohortFilter()
    {
        $this->resetPage();
    }
    
    /**
     * Redirect to the student edit page
     *
     * @param int $studentId
     * @return void
     */
    public function editStudent($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            return redirect()->route('students.edit', $student);
        } catch (\Exception $e) {
            Log::error('Error editing student: ' . $e->getMessage());
            session()->flash('error', 'Unable to edit student. Please try again.');
        }
    }
    
    /**
     * Redirect to the student view page
     *
     * @param int $studentId
     * @return void
     */
    public function viewStudent($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            return redirect()->route('students.show', $student);
        } catch (\Exception $e) {
            Log::error('Error viewing student: ' . $e->getMessage());
            session()->flash('error', 'Unable to view student. Please try again.');
        }
    }
    
    public function render()
    {
        // Get all available programs and cohorts for filter dropdowns
        $programs = CollegeClass::has('students')->orderBy('name')->get();
        $cohorts = Cohort::has('students')->orderBy('name')->get();
        
        // Build the query with filters
        $studentsQuery = Student::query()
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('student_id', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->programFilter, function($query) {
                return $query->where('college_class_id', $this->programFilter);
            })
            ->when($this->cohortFilter, function($query) {
                return $query->where('cohort_id', $this->cohortFilter);
            });
        
        // Count total students
        $studentsTotal = $studentsQuery->count();
        
        // Get paginated students
        $students = $studentsQuery->paginate(15);
        
        return view('livewire.students-table-widget', [
            'students' => $students,
            'studentsTotal' => $studentsTotal,
            'programs' => $programs,
            'cohorts' => $cohorts
        ]);
    }
}
