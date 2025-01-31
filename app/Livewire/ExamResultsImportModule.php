<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Response;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\ExamResultsImport;

class ExamResultsImportModule extends Component
{
    use WithFileUploads;

    public $file;
    public $selected_exam_id;
    public $exams;
    public $importing = false;
    public $progress = 0;

    public function mount()
    {
        $this->exams = Exam::with('course')->get();
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls',
            'selected_exam_id' => 'required|exists:exams,id'
        ]);

        $this->importing = true;
        $this->progress = 0;

        $import = new ExamResultsImport($this->selected_exam_id);
        Excel::import($import, $this->file);
        
        $results = $import->getImportResults();
        session()->flash('message', sprintf(
            'Import complete: %d successful, %d failed',
            $results['success'],
            $results['failed']
        ));

        $this->importing = false;
    }

    public function render()
    {
        return view('livewire.exam-results-import-module');
    }
} 