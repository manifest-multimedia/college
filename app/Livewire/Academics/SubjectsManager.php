<?php

namespace App\Livewire\Academics;

use App\Exports\CoursesExport;
use App\Imports\CoursesImport;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Year;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SubjectsManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $subjectId;

    public $name;

    public $course_code;

    public $description;

    public $semester_id;

    public $credit_hours;

    public $year_id;

    public $college_class_id;

    // Filter properties (Programme / Year / Semester)
    public $filter_college_class = '';
    public $filter_year = '';
    public $filter_semester = '';

    public $isOpen = false;

    public $isDeleteModalOpen = false;

    public $editMode = false;

    protected $rules = [
        'name' => 'required|min:3',
        'course_code' => 'required|min:2',
        'semester_id' => 'required',
        'credit_hours' => 'required|min:1',
        'year_id' => 'required',
        'college_class_id' => 'required',
    ];

    public function mount()
    {
        $this->resetInputFields();
    }

    public function render()
    {
        $query = Subject::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('course_code', 'like', '%'.$this->search.'%');
            });
        }

        // Apply compact filters when set
        $query->when($this->filter_college_class, function ($q) {
            $q->where('college_class_id', $this->filter_college_class);
        });

        $query->when($this->filter_year, function ($q) {
            $q->where('year_id', $this->filter_year);
        });

        $query->when($this->filter_semester, function ($q) {
            $q->where('semester_id', $this->filter_semester);
        });

        $subjects = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.academics.subjects-manager', [
            'subjects' => $subjects,
            'semesters' => Semester::all(),
            'years' => Year::all(),
            'collegeClasses' => CollegeClass::all(),
        ])->layout('components.dashboard.default', [
            'title' => 'Manage Subjects',
            'description' => 'Manage your subjects here.',
        ]);
    }

    public function openModal()
    {
        $this->isOpen = true;
        $this->editMode = false;
        $this->resetInputFields();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    public function openDeleteModal($id)
    {
        $this->subjectId = $id;
        $this->isDeleteModalOpen = true;
    }

    public function closeDeleteModal()
    {
        $this->isDeleteModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->subjectId = null;
        $this->name = '';
        $this->course_code = '';
        $this->description = '';
        $this->semester_id = '';
        $this->credit_hours = '';
        $this->year_id = '';
        $this->college_class_id = '';
    }

    public function store()
    {
        $this->validate();

        try {
            Subject::updateOrCreate(['id' => $this->subjectId], [
                'name' => $this->name,
                'course_code' => $this->course_code,
                'description' => $this->description,
                'semester_id' => $this->semester_id,
                'credit_hours' => $this->credit_hours,
                'year_id' => $this->year_id,
                'college_class_id' => $this->college_class_id,
                'slug' => Str::slug($this->name),
            ]);

            $this->closeModal();
            $this->resetInputFields();
            session()->flash('message', $this->subjectId ? 'Subject updated successfully.' : 'Subject created successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving subject: '.$e->getMessage());
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        $this->subjectId = $id;
        $this->name = $subject->name;
        $this->course_code = $subject->course_code;
        $this->description = $subject->description;
        $this->semester_id = $subject->semester_id;
        $this->credit_hours = $subject->credit_hours;
        $this->year_id = $subject->year_id;
        $this->college_class_id = $subject->college_class_id;

        $this->editMode = true;
        $this->isOpen = true;
    }

    public function delete()
    {
        if ($this->subjectId) {
            try {
                Subject::find($this->subjectId)->delete();
                session()->flash('message', 'Subject deleted successfully.');
            } catch (\Exception $e) {
                Log::error('Error deleting subject: '.$e->getMessage());
                session()->flash('error', 'An error occurred while deleting the subject: '.$e->getMessage());
            }
        }
        $this->closeDeleteModal();
        $this->resetInputFields();
    }

    public $importFile;

    public $isImportModalOpen = false;

    public function export()
    {
        if (! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized action. Only users with System role can export courses.');
        }

        $filename = 'courses_export_'.date('Y_m_d_His').'.xlsx';

        return Excel::download(
            new CoursesExport(
                $this->search,
                $this->filter_college_class ?? '',
                $this->filter_year ?? '',
                $this->filter_semester ?? ''
            ),
            $filename
        );
    }

    public function openImportModal()
    {
        if (! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized action. Only users with System role can import courses.');
        }

        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->importFile = null;
    }

    public function import()
    {
        if (! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized action. Only users with System role can import courses.');
        }

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $importer = new CoursesImport();
            Excel::import($importer, $this->importFile->getRealPath());

            $created = $importer->getImportedCount();
            $updated = $importer->getUpdatedCount();
            $errors = $importer->getErrors();

            $msg = "Course import completed: {$created} created, {$updated} updated.";
            if (! empty($errors)) {
                $msg .= ' Some rows had errors: '.implode('; ', array_slice($errors, 0, 3));
            }

            session()->flash('message', $msg);
            $this->closeImportModal();
        } catch (\Exception $e) {
            Log::error('Error importing courses: '.$e->getMessage());
            session()->flash('error', 'Failed to import courses: '.$e->getMessage());
        }
    }

    // Clear applied filters
    public function clearFilters()
    {
        $this->filter_college_class = '';
        $this->filter_year = '';
        $this->filter_semester = '';
    }
}
