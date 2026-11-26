<?php

namespace App\Livewire;

use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class StudentIdRegeneration extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public $programFilter = '';
    public $cohortFilter = '';
    public $genderFilter = '';
    public $statusFilter = '';
    public $selectedStudents = [];
    public $selectAll = false;

    public function boot(): void
    {
        $this->authorizeRegeneration();
    }

    public $confirmingIdRegeneration = false;
    public string $regenerationMode = 'preserve_sequence';
    public string $regenerationSourcePattern = '';
    #[\Livewire\Attributes\Locked]
    public array $regenerationPreview = [];
    #[\Livewire\Attributes\Locked]
    public ?int $regenerationCohort = null;
    #[\Livewire\Attributes\Locked]
    public ?array $regenerationSelectedIds = null;


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
     * Show confirmation modal for ID regeneration
     *
     * @return void
     */
    public function confirmIdRegeneration(string $scope = 'cohort')
    {
        $this->authorizeRegeneration();
        $this->regenerationSelectedIds = null;
        if ($scope === 'selected') {
            $ids = array_values(array_unique(array_map('intval', $this->selectedStudents)));
            $students = $this->getFilteredStudentsQuery()->whereIn('id', $ids)->get(['id', 'cohort_id']);
            if (! $ids || $students->count() !== count($ids) || $students->pluck('cohort_id')->unique()->count() !== 1 || ! $students->first()->cohort_id) {
                session()->flash('error', 'Select students belonging to one cohort before regenerating IDs.');
                return;
            }
            $this->regenerationCohort = (int) $students->first()->cohort_id;
            $this->regenerationSelectedIds = $ids;
        } else {
            if (! $this->cohortFilter) {
                session()->flash('error', 'Please select a cohort first to regenerate IDs.');
                return;
            }
            $this->regenerationCohort = (int) $this->cohortFilter;
        }
        $this->regenerationPreview = [];
        $this->regenerationMode = 'preserve_sequence';
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
        $this->authorizeRegeneration();
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
        $this->authorizeRegeneration();
        $this->restoreBatchId = $id;
    }

    public function restoreRegenerationBatch(): void
    {
        $this->authorizeRegeneration();
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
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin']), 403);
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
        abort_unless($this->confirmingIdRegeneration && ($this->regenerationSelectedIds !== null || $this->regenerationCohort === (int) $this->cohortFilter), 422);
        $this->validate(['regenerationMode' => 'required|in:resequence,preserve_sequence', 'regenerationSourcePattern' => 'required_if:regenerationMode,preserve_sequence|max:255']);
        app(\App\Services\BrandingSettingsService::class)->apply();
        $this->regenerationPreview = $service->regenerateStudentIdsForCohort($this->regenerationCohort, auth()->id(), $this->regenerationMode, $this->regenerationSourcePattern, true, selectedIds: $this->regenerationSelectedIds);
    }

    public function regenerateIds(): void
    {
        $this->authorizeRegeneration();
        abort_unless($this->confirmingIdRegeneration && ($this->regenerationSelectedIds !== null || $this->regenerationCohort === (int) $this->cohortFilter), 422);
        if (empty($this->regenerationPreview['hash']) || empty($this->regenerationPreview['success']) || ! empty($this->regenerationPreview['errors'])) {
            session()->flash('error', 'Generate a successful preview before confirming.');
            return;
        }
        try {
            app(\App\Services\StudentIdRegenerationOperations::class)->enqueue($this->regenerationCohort, auth()->id(), $this->regenerationMode, $this->regenerationSourcePattern, $this->regenerationPreview['hash'], $this->regenerationPreview['success'], $this->regenerationSelectedIds);
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

    public function render()
    {
        $this->authorizeRegeneration();
        // Get all available programs and cohorts for filter dropdowns
        $programs = CollegeClass::has('students')->orderBy('name')->get();
        $cohorts = Cohort::has('students')->orderBy('name')->get();

        // Build the query with filters
        $studentsQuery = $this->getFilteredStudentsQuery();

        // Count total students
        $studentsTotal = $studentsQuery->count();

        // Get paginated students
        $students = $studentsQuery->paginate(15);

        return view('livewire.student-id-regeneration', [
            'students' => $students,
            'studentsTotal' => $studentsTotal,
            'programs' => $programs,
            'cohorts' => $cohorts,
        ]);
    }
}
