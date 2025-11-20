<?php

namespace App\Livewire;

use App\Exports\ExamResultsExport;
use App\Models\Exam;
use Illuminate\Support\Str;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsExportModule extends Component
{
    public $selected_exam_id;

    public $exams;

    public function mount()
    {
        $this->exams = Exam::with('course')->get();
    }

    public function export()
    {
        $this->validate(['selected_exam_id' => 'required|exists:exams,id']);

        $exam = Exam::find($this->selected_exam_id);
        $filename = Str::slug($exam->course->name).'-results-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ExamResultsExport($this->selected_exam_id), $filename);
    }

    public function render()
    {
        return view('livewire.exam-results-export-module');
    }
}
