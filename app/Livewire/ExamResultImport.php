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

        $response = $import->getImportResults();

        if ($response['failed'] > 0) {
            session()->flash('message', 'Import Process Completed. Import Outcome: ' . $response['success'] . ' responses imported and ' . $response['failed'] . ' responses failed to import as Exam Session already exists for the selected student(s).');
        } else {
            session()->flash('message', 'Import Process Completed. 
            Import Outcome: ' . $response['success'] . ' responses imported and ' . $response['failed'] . ' responses failed to import due to errors.
            ' . $response['skipped'] . ' responses skipped as Exam Session already exists for the selected student(s).');
        }
    }
}
