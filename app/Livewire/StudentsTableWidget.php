<?php

namespace App\Livewire;

use App\Exports\StudentExport;
use App\Jobs\GenerateCohortStudentIds;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentsTableWidget extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $programFilter = '';

    public $cohortFilter = '';

    public $confirmingStudentDeletion = false;

    public $studentToDelete = null;

    public $confirmingIdRegeneration = false;

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
                    'students_export_'.date('Y-m-d_H-i-s').'.xlsx'
                );
            } elseif ($this->exportFormat == 'pdf') {
                // Get filtered students data
                $students = Student::query()
                    ->when($this->search, function ($query) {
                        return $query->where(function ($q) {
                            $q->where('student_id', 'like', '%'.$this->search.'%')
                                ->orWhere('first_name', 'like', '%'.$this->search.'%')
                                ->orWhere('last_name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                    })
                    ->when($this->programFilter, function ($query) {
                        return $query->where('college_class_id', $this->programFilter);
                    })
                    ->when($this->cohortFilter, function ($query) {
                        return $query->where('cohort_id', $this->cohortFilter);
                    })
                    ->with(['collegeClass', 'cohort'])
                    ->get();

                $pdf = PDF::loadView('exports.students-pdf', [
                    'students' => $students,
                ]);

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'students_export_'.date('Y-m-d_H-i-s').'.pdf');
            } else {
                session()->flash('error', 'Please select a valid export format.');
                $this->showingExportModal = false;

                return null;
            }
        } catch (\Exception $e) {
            Log::error('Export error: '.$e->getMessage());
            session()->flash('error', 'An error occurred while exporting students. Please try again.');
            $this->showingExportModal = false;

            return null;
        }
    }

    /**
     * Redirect to the student edit page
     *
     * @param  int  $studentId
     * @return void
     */
    public function editStudent($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);

            return redirect()->route('students.edit', $student);
        } catch (\Exception $e) {
            Log::error('Error editing student: '.$e->getMessage());
            session()->flash('error', 'Unable to edit student. Please try again.');
        }
    }

    /**
     * Redirect to the student view page
     *
     * @param  int  $studentId
     * @return void
     */
    public function viewStudent($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);

            return redirect()->route('students.show', $student);
        } catch (\Exception $e) {
            Log::error('Error viewing student: '.$e->getMessage());
            session()->flash('error', 'Unable to view student. Please try again.');
        }
    }

    /**
     * Show confirmation modal before deleting student
     *
     * @param  int  $studentId
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
     * Show confirmation modal for ID regeneration
     *
     * @return void
     */
    public function confirmIdRegeneration()
    {
        if (! $this->cohortFilter) {
            session()->flash('error', 'Please select a cohort first to regenerate IDs.');

            return;
        }
        $this->confirmingIdRegeneration = true;
    }

    /**
     * Cancel ID regeneration
     *
     * @return void
     */
    public function cancelIdRegeneration()
    {
        $this->confirmingIdRegeneration = false;
    }

    /**
     * Regenerate student IDs for the selected cohort
     *
     * @return void
     */
    public function regenerateIds(\App\Services\StudentIdGenerationService $service)
    {
        if (! $this->cohortFilter) {
            return;
        }

        try {
            // Pass regenerateAll=true so the job resequences all students in the cohort
            GenerateCohortStudentIds::dispatch((int) $this->cohortFilter, auth()->id(), true);
            session()->flash('success', 'Student ID regeneration has been queued. You can continue using the app; we will notify you when it completes.');
        } catch (\Throwable $e) {
            Log::error('Error queueing ID regeneration: '.$e->getMessage());
            session()->flash('error', 'Could not queue ID regeneration. Please try again or contact support.');
        }

        $this->confirmingIdRegeneration = false;
    }

    /**
     * Delete a student
     *
     * @return void
     */
    public function deleteStudent()
    {
        if (! $this->studentToDelete) {
            return;
        }

        try {
            DB::beginTransaction();

            // Find the student
            $student = Student::findOrFail($this->studentToDelete);

            // Store student details for logging
            $studentName = $student->first_name.' '.$student->last_name;
            $studentId = $student->student_id;

            // Delete the student
            $student->delete();

            // Log the deletion
            Log::info('Student deleted', [
                'student_id' => $studentId,
                'name' => $studentName,
                'deleted_by' => auth()->user()->name ?? 'Unknown',
                'deleted_by_id' => auth()->id(),
            ]);

            DB::commit();

            session()->flash('success', "Student {$studentName} (ID: {$studentId}) has been successfully deleted.");

        } catch (\Exception $e) {
            DB::rollBack();

            session()->flash('error', 'An error occurred while deleting the student.');
            Log::error('Error deleting student', [
                'student_id' => $this->studentToDelete,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('student_id', 'like', '%'.$this->search.'%')
                        ->orWhere('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->programFilter, function ($query) {
                return $query->where('college_class_id', $this->programFilter);
            })
            ->when($this->cohortFilter, function ($query) {
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
            'cohorts' => $cohorts,
        ]);
    }
}
