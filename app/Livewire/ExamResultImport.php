<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamSession;
use App\Models\ScoredQuestion;
use App\Models\Option;
use App\Imports\ResultImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class ExamResultImport extends Component
{
    use WithFileUploads;

    public $examId;
    public $file;

    public function render()
    {
        return view('livewire.exam-result-import',[
            'exams' => Exam::all(),
        ]);
    }

    public function importResults()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'examId' => 'required|exists:exams,id',
        ]);

        $import = new ResultImport($this->examId);
        Excel::import($import, $this->file);

        session()->flash('message', 'Results imported successfully.');
    }
}
