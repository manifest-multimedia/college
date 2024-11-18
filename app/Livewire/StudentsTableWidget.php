<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use Livewire\WithPagination;

class StudentsTableWidget extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public function render()
    {
        $studentsTotal = Student::count();
        $students = Student::paginate(15);
        return view('livewire.students-table-widget', [
            'students' => $students,
            'studentsTotal' => $studentsTotal
        ]);
    }
}
