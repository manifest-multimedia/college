<?php

namespace App\Livewire;

use App\Exports\StudentExport;
use App\Jobs\GenerateCohortStudentIds;
use App\Jobs\ProcessBatchPasswordResetJob;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use App\Services\PasswordResetManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    public $genderFilter = '';

    public $statusFilter = '';

    public $confirmingStudentDeletion = false;

    public $studentToDelete = null;

    public $confirmingIdRegeneration = false;
    public string $regenerationMode = 'resequence';
    public string $regenerationSourcePattern = '';
    #[\Livewire\Attributes\Locked]
    public array $regenerationPreview = [];
    #[\Livewire\Attributes\Locked]
    public ?int $regenerationCohort = null;


    public $showingExportModal = false;

    public $exportFormat = '';

    public $selectedStudents = [];

    public $selectAll = false;

    // Password reset properties
    public $showPasswordResetModal = false;

    public $resetScope = 'individual'; // 'individual', 'selected', 'cohort', 'all'

    public $resetTargetStudentId = null;

    public $resetTargetStudentName = '';

    public $resetTargetStudentCode = '';

    public $resetTargetCount = 0;

    public $resetPasswordMode = 'random'; // 'random', 'custom'

    public $resetCustomPassword = '';

    public $resetChannel = 'both'; // 'both', 'email', 'sms', 'none'

    public $resetRequirePasswordChange = true;

    public $resetProcessing = false;

    public $resetSummary = null;

    // Reset pagination when filters change
    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatingProgramFilter()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatingCohortFilter()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatingGenderFilter()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->programFilter = '';
        $this->cohortFilter = '';
        $this->genderFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedStudents = $this->getFilteredStudentsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function updatedSelectedStudents()
    {
        $allIds = $this->getFilteredStudentsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        if (count($allIds) > 0 && count(array_intersect($allIds, $this->selectedStudents)) === count($allIds)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    private function getFilteredStudentsQuery()
    {
        return Student::query()
            ->search($this->search)
            ->when($this->programFilter, function ($query) {
                return $query->where('college_class_id', $this->programFilter);
            })
            ->when($this->cohortFilter, function ($query) {
                return $query->where('cohort_id', $this->cohortFilter);
            })
            ->when($this->genderFilter, function ($query) {
                return $query->whereRaw('LOWER(gender) = ?', [strtolower($this->genderFilter)]);
            })
            ->when($this->statusFilter, function ($query) {
                return $query->whereRaw('LOWER(status) = ?', [strtolower($this->statusFilter)]);
            });
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
            $selected = !empty($this->selectedStudents) ? $this->selectedStudents : [];

            if ($this->exportFormat == 'excel') {
                $this->showingExportModal = false;

                return Excel::download(
                    new StudentExport($this->search, $this->programFilter, $this->cohortFilter, $this->genderFilter, $selected, $this->statusFilter),
                    $this->studentExportFilename('xlsx')
                );
            } elseif ($this->exportFormat == 'pdf') {
                // Get filtered students data
                $query = $this->getFilteredStudentsQuery();
                if (!empty($selected)) {
                    $query->whereIn('id', $selected);
                }
                $students = $query->with(['collegeClass', 'cohort'])->get();

                $pdf = PDF::loadView('exports.students-pdf', [
                    'students' => $students,
                ]);

                $this->showingExportModal = false;

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $this->studentExportFilename('pdf'));
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
     * Build a meaningful export name when the results are scoped to one cohort.
     */
    private function studentExportFilename(string $extension): string
    {
        $prefix = '';

        if ($this->cohortFilter) {
            $cohortName = Cohort::query()->whereKey($this->cohortFilter)->value('name');
            $prefix = $cohortName ? Str::slug($cohortName, '_').'_' : '';
        }

        return $prefix.'students_export_'.date('Y-m-d_H-i-s').'.'.$extension;
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
        $this->authorizeRegeneration();
        if (! $this->cohortFilter) {
            session()->flash('error', 'Please select a cohort first to regenerate IDs.');

            return;
        }
        $this->regenerationCohort = (int) $this->cohortFilter;
        $this->regenerationPreview = [];
        $this->regenerationMode = 'resequence';
        $this->regenerationSourcePattern = '';
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
    public bool $showRegenerationHistory = false;
    #[\Livewire\Attributes\Locked]
    public ?string $restoreBatchId = null;

    public function openRegenerationHistory(): void
    {
        $this->authorizeRegeneration();
        $this->restoreBatchId = null;
        $this->showRegenerationHistory = true;
    }

    public function getRegenerationOperationsProperty()
    {
        $this->authorizeRegeneration();
        return DB::table('student_id_regeneration_operations as o')->leftJoin('cohorts as c', 'c.id', '=', 'o.cohort_id')->select('o.*', 'c.name as cohort_name')->orderByDesc('o.created_at')->limit(30)->get();
    }

    public function getRegenerationBatchesProperty()
    {
        $this->authorizeRegeneration();
        return DB::table('student_id_regeneration_batches as b')->leftJoin('cohorts as c', 'c.id', '=', 'b.cohort_id')->leftJoin('users as u', 'u.id', '=', 'b.initiated_by')->select('b.id', 'b.mode', 'b.changed_count', 'b.created_at', 'b.restored_at', 'c.name as cohort_name', 'u.name as actor_name')->orderByDesc('b.created_at')->limit(100)->get();
    }

    public function downloadRegenerationMapping(string $id)
    {
        $this->authorizeRegeneration();
        $batch = DB::table('student_id_regeneration_batches')->where('id', $id)->first();
        abort_unless($batch, 404);
        $mapping = json_decode($batch->mapping, true, 512, JSON_THROW_ON_ERROR);
        return response()->streamDownload(function () use ($mapping) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Internal ID', 'Student', 'Previous ID', 'New ID'], ',', '"', '');
            foreach ($mapping as $row) {
                $cells = [$row['id'], $row['name'], $row['old_id'], $row['new_id']];
                fputcsv($file, array_map(fn ($v) => preg_match('/^[\s]*[=+@-]/', (string) $v) ? "'".$v : $v, $cells), ',', '"', '');
            }
            fclose($file);
        }, 'student-id-mapping-'.$id.'.csv');
    }

    public function confirmRestoreBatch(string $id): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator']), 403);
        $this->restoreBatchId = $id;
    }

    public function restoreRegenerationBatch(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator']), 403);
        abort_unless($this->showRegenerationHistory && $this->restoreBatchId, 422);
        try {
            app(\App\Services\StudentIdRegenerationHistory::class)->restore($this->restoreBatchId, auth()->id());
            session()->flash('success', 'Regeneration batch restored.');
        } catch (\Throwable $e) {
            Log::warning('Student ID batch restoration failed', ['batch' => $this->restoreBatchId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Restoration blocked. Verify current IDs and dependent records before retrying.');
        }
        $this->restoreBatchId = null;
        $this->showRegenerationHistory = false;
    }

    private function authorizeRegeneration(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
    }

    public function updatedRegenerationMode(): void
    {
        $this->regenerationPreview = [];
    }

    public function updatedRegenerationSourcePattern(): void
    {
        $this->regenerationPreview = [];
    }

    public function previewRegeneration(\App\Services\StudentIdGenerationService $service): void
    {
        $this->authorizeRegeneration();
        abort_unless($this->confirmingIdRegeneration && $this->regenerationCohort === (int) $this->cohortFilter, 422);
        $this->validate(['regenerationMode' => 'required|in:resequence,preserve_sequence', 'regenerationSourcePattern' => 'required_if:regenerationMode,preserve_sequence|max:255']);
        app(\App\Services\BrandingSettingsService::class)->apply();
        $this->regenerationPreview = $service->regenerateStudentIdsForCohort($this->regenerationCohort, auth()->id(), $this->regenerationMode, $this->regenerationSourcePattern, true);
    }

    public function regenerateIds(): void
    {
        $this->authorizeRegeneration();
        abort_unless($this->confirmingIdRegeneration && $this->regenerationCohort === (int) $this->cohortFilter, 422);
        if (empty($this->regenerationPreview['hash']) || empty($this->regenerationPreview['success']) || ! empty($this->regenerationPreview['errors'])) {
            session()->flash('error', 'Generate a successful preview before confirming.');
            return;
        }
        try {
            app(\App\Services\StudentIdRegenerationOperations::class)->enqueue($this->regenerationCohort, auth()->id(), $this->regenerationMode, $this->regenerationSourcePattern, $this->regenerationPreview['hash'], $this->regenerationPreview['success']);
        } catch (\Throwable $e) {
            Log::warning('Regeneration enqueue failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Unable to queue regeneration. Check for an existing active operation.');
            return;
        }
        $this->showRegenerationHistory = true;
        $this->regenerationPreview = [];
        $this->confirmingIdRegeneration = false;
        session()->flash('success', 'Student ID regeneration queued. Changes will only be applied if the preview still matches.');
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

    /**
     * Open the password reset modal.
     */
    public function openStudentPasswordReset(string $scope, ?int $studentId = null)
    {
        if (! auth()->user() || ! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized. Only System users can reset passwords.');
        }

        $this->resetScope = $scope;
        $this->resetPasswordMode = 'random';
        $this->resetCustomPassword = '';
        $this->resetChannel = 'both';
        $this->resetRequirePasswordChange = true;
        $this->resetSummary = null;

        if ($scope === 'individual' && $studentId) {
            $student = Student::findOrFail($studentId);
            $this->resetTargetStudentId = $student->id;
            $this->resetTargetStudentName = $student->full_name ?: $student->name;
            $this->resetTargetStudentCode = $student->student_id;
            $this->resetTargetCount = 1;
        } elseif ($scope === 'selected') {
            $this->resetTargetCount = count($this->selectedStudents);
            if ($this->resetTargetCount === 0) {
                session()->flash('error', 'Please select at least one student.');
                return;
            }
        } elseif ($scope === 'cohort') {
            if (! $this->cohortFilter) {
                session()->flash('error', 'Please select a cohort filter first.');
                return;
            }
            $cohort = Cohort::find($this->cohortFilter);
            $this->resetTargetStudentName = $cohort ? $cohort->name : 'Selected Cohort';
            $this->resetTargetCount = Student::where('cohort_id', $this->cohortFilter)->active()->count();
        } elseif ($scope === 'all') {
            $this->resetTargetCount = Student::active()->count();
        }

        $this->showPasswordResetModal = true;
    }

    /**
     * Close the password reset modal.
     */
    public function closeStudentPasswordReset()
    {
        $this->showPasswordResetModal = false;
        $this->resetScope = 'individual';
        $this->resetTargetStudentId = null;
        $this->resetTargetStudentName = '';
        $this->resetTargetStudentCode = '';
        $this->resetSummary = null;
    }

    /**
     * Execute password reset for students.
     */
    public function executeStudentPasswordReset(PasswordResetManagementService $service)
    {
        if (! auth()->user() || ! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized. Only System users can reset passwords.');
        }

        if ($this->resetPasswordMode === 'custom') {
            $this->validate([
                'resetCustomPassword' => 'required|string|min:8',
            ]);
        }

        $options = [
            'channel' => $this->resetChannel,
            'require_change' => $this->resetRequirePasswordChange,
            'custom_password' => $this->resetPasswordMode === 'custom' ? $this->resetCustomPassword : null,
            'initiated_by' => auth()->id(),
        ];

        try {
            if ($this->resetScope === 'individual') {
                $student = Student::findOrFail($this->resetTargetStudentId);
                $result = $service->resetStudent($student, $options);

                if ($result['success']) {
                    $channelText = match ($this->resetChannel) {
                        'both' => 'via Email & SMS',
                        'email' => 'via Email',
                        'sms' => 'via SMS',
                        default => 'without notification',
                    };
                    session()->flash('success', "Password for {$this->resetTargetStudentName} reset successfully {$channelText}. Temporary password: {$result['temporary_password']}");
                    $this->closeStudentPasswordReset();
                } else {
                    session()->flash('error', $result['message'] ?? 'Failed to reset password.');
                }
            } elseif ($this->resetScope === 'selected') {
                $count = count($this->selectedStudents);
                if ($count > 10) {
                    ProcessBatchPasswordResetJob::dispatch('students_bulk', $this->selectedStudents, $options);
                    session()->flash('success', "Password reset for {$count} selected students has been queued in the background.");
                    $this->closeStudentPasswordReset();
                } else {
                    $summary = $service->resetStudentsBulk($this->selectedStudents, $options);
                    $this->resetSummary = $summary;
                    session()->flash('success', "Successfully processed password resets for {$summary['processed']} of {$summary['total']} students.");
                }
            } elseif ($this->resetScope === 'cohort') {
                $cohortId = (int) $this->cohortFilter;
                $count = Student::where('cohort_id', $cohortId)->active()->count();
                if ($count > 10) {
                    ProcessBatchPasswordResetJob::dispatch('cohort', $cohortId, $options);
                    session()->flash('success', "Password reset for cohort {$this->resetTargetStudentName} ({$count} students) has been queued in the background.");
                    $this->closeStudentPasswordReset();
                } else {
                    $summary = $service->resetCohort($cohortId, $options);
                    $this->resetSummary = $summary;
                    session()->flash('success', "Successfully processed password resets for {$summary['processed']} of {$summary['total']} students in this cohort.");
                }
            } elseif ($this->resetScope === 'all') {
                ProcessBatchPasswordResetJob::dispatch('all_students', null, $options);
                session()->flash('success', "Password reset for all active students ({$this->resetTargetCount}) has been queued in the background.");
                $this->closeStudentPasswordReset();
            }
        } catch (\Throwable $e) {
            Log::error('Error executing student password reset: '.$e->getMessage(), [
                'scope' => $this->resetScope,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'An error occurred while resetting passwords: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Get all available programs and cohorts for filter dropdowns
        $programs = CollegeClass::has('students')->orderBy('name')->get();
        $cohorts = Cohort::has('students')->orderBy('name')->get();

        // Build the query with filters
        $studentsQuery = $this->getFilteredStudentsQuery();

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
