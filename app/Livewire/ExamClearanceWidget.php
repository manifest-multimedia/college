<?php

namespace App\Livewire;

use App\Models\FeeCollection;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class ExamClearanceWidget extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = ''; // Search input

    public $student_id;

    public $student_name;

    public $is_eligble = 0; // Default eligibility value

    public $mode;

    protected $rules = [
        'student_id' => 'required|unique:fee_collections,student_id',
        'student_name' => 'required|string|max:255',
        'is_eligble' => 'boolean',
    ];

    public function mount()
    {
        $this->mode = 'index';
    }

    public function updatingSearch()
    {
        // Reset pagination when search input changes
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if ($propertyName == 'student_id') {
            $student = Student::where('student_id', $this->student_id)->first();
            if ($student) {

                $this->student_name = $student->first_name.' '.$student->last_name.' '.$student->other_name;
            } else {
                $this->student_name = '';
            }
        }
    }

    public function toggleEligibility($id)
    {
        $student = FeeCollection::findOrFail($id);
        $student->is_eligble = ! $student->is_eligble;
        $student->save();
    }

    public function addStudent()
    {
        $this->validate();

        FeeCollection::FirstOrCreate([
            'student_id' => $this->student_id,
        ], [
            'student_name' => $this->student_name,
            'is_eligble' => $this->is_eligble,
        ]);

        // Reset fields after adding
        $this->reset(['student_id', 'student_name', 'is_eligble']);

        session()->flash('message', 'Student added successfully!');

        $this->mode = 'index';
    }

    public function render()
    {
        $students = FeeCollection::where('student_id', 'like', "%{$this->search}%")
            ->orderBy('student_id')
            ->paginate(10);

        return view('livewire.exam-clearance-widget', [
            'students' => $students,
        ]);
    }

    public function addRecord()
    {
        $this->mode = 'add';
    }

    public function viewDetails($studentId)
    {
        $this->mode = 'view';
    }
}
