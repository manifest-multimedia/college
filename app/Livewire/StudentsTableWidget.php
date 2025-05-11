<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Cohort;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentExport;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentsTableWidget extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $programFilter = '';
    public $cohortFilter = '';
    public $confirmingStudentDeletion = false;
    public $studentToDelete = null;
    public $showingExportModal = false;
    public $exportFormat = '';
    
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
     * Show export format selection modal
     *
     * @return void
     */
    public function exportStudents()
    {
        $this->showingExportModal = true;
    }
    
    /**
     * Cancel export process
     *
     * @return void
     */
    public function cancelExport()
    {
        $this->showingExportModal = false;
        $this->exportFormat = '';
    }
    
    /**
     * Process student export based on selected format
     *
     * @return mixed
     */
    public function processExport()
    {
        try {
            if ($this->exportFormat == 'excel') {
                return Excel::download(
                    new StudentExport($this->search, $this->programFilter, $this->cohortFilter),
                    'students_export_' . date('Y-m-d_H-i-s') . '.xlsx'
                );
            } elseif ($this->exportFormat == 'pdf') {
                // Get filtered students data
                $students = Student::query()
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
                    })
                    ->with(['collegeClass', 'cohort'])
                    ->get();

                $pdf = PDF::loadView('exports.students-pdf', [
                    'students' => $students
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, 'students_export_' . date('Y-m-d_H-i-s') . '.pdf');
            } else {
                session()->flash('error', 'Please select a valid export format.');
                $this->showingExportModal = false;
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while exporting students. Please try again.');
            $this->showingExportModal = false;
            return null;
        }
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
    
    /**
     * Show confirmation modal before deleting student
     *
     * @param int $studentId
     * @return void
     */
    public function confirmStudentDeletion($studentId)
    {
        $this->confirmingStudentDeletion = true;
        $this->studentToDelete = $studentId;
    }
    
    /**
     * Cancel student deletion
     *
     * @return void
     */
    public function cancelStudentDeletion()
    {
        $this->confirmingStudentDeletion = false;
        $this->studentToDelete = null;
    }
    
    /**
     * Delete a student
     *
     * @return void
     */
    public function deleteStudent()
    {
        if (!$this->studentToDelete) {
            return;
        }
        
        try {
            // Make a DELETE request to the students.destroy route
            $response = app('router')->dispatch(
                request()->create(
                    route('students.destroy', $this->studentToDelete),
                    'DELETE'
                )
            );
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 200) {
                session()->flash('success', 'Student deleted successfully.');
            } else {
                session()->flash('error', 'Failed to delete student.');
                Log::error('Failed to delete student', [
                    'student_id' => $this->studentToDelete,
                    'status_code' => $statusCode
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the student.');
            Log::error('Error deleting student', [
                'student_id' => $this->studentToDelete,
                'error' => $e->getMessage()
            ]);
        }
        
        $this->confirmingStudentDeletion = false;
        $this->studentToDelete = null;
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
