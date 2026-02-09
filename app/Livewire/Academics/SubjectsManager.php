<?php

namespace App\Livewire\Academics;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Year;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class SubjectsManager extends Component
{
    use WithPagination;

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

    public $isOpen = false;

    public $isDeleteModalOpen = false;

    public $editMode = false;

    protected $rules = [
        'name' => 'required|min:3',
        'course_code' => 'required|min:2',
        'semester_id' => 'required',
        'credit_hours' => 'required|integer|min:1',
        'year_id' => 'required',
        'college_class_id' => 'required',
    ];

    public function mount()
    {
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.academics.subjects-manager', [
            'subjects' => Subject::where('name', 'like', '%'.$this->search.'%')
                ->orWhere('course_code', 'like', '%'.$this->search.'%')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
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
}
