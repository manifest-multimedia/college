<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Cohort;
use Livewire\WithPagination;

class StudentsTableWidget extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $programFilter = '';
    public $cohortFilter = '';
    
    // Reset pagination when filters change
    protected function updatingSearch() 
    {
        $this->resetPage();
    }
    
    protected function updatingProgramFilter()
    {
        $this->resetPage();
    }
    
    protected function updatingCohortFilter()
    {
        $this->resetPage();
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
