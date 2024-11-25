<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as MaatExcel;


use Illuminate\Support\Facades\Log;

class QuestionBank extends Component
{
    use WithFileUploads;

    public $exam_id;
    public $questions = [];
    public $bulk_file;
    public $uploadPath;
    protected $rules = [
        'questions.*.question_text' => 'required|string',
        'questions.*.options.*.option_text' => 'required|string|max:255',
        'questions.*.options.*.is_correct' => 'required|boolean',
        'questions.*.marks' => 'integer|min:1',
        'questions.*.explanation' => 'nullable|string|max:500',
    ];

    public function mount($examId = null)
    {
        $this->exam_id = $examId;
        $this->loadQuestions();
    }



    public function loadQuestions()
    {
        if ($this->exam_id) {
            $this->questions = Question::where('exam_id', $this->exam_id)
                ->with('options')
                ->get()
                ->toArray(); // Convert to array for Livewire compatibility
        } else {
            $this->questions = [];
        }
    }


    public function addQuestion()
    {
        $this->questions[] = [
            'question_text' => '',
            'exam_section' => '',
            'marks' => 1,
            'explanation' => '',
            'options' => [
                ['option_text' => '', 'is_correct' => false],
                ['option_text' => '', 'is_correct' => false]
            ]
        ];
    }

    public function addOption($index)
    {
        $this->questions[$index]['options'][] = [
            'option_text' => '',
            'is_correct' => false,
        ];
        $this->questions[$index]['options'] = array_values($this->questions[$index]['options']);
    }

    public function removeOption($index, $optionIndex)
    {
        // Remove the option
        unset($this->questions[$index]['options'][$optionIndex]);
        $this->questions[$index]['options'] = array_values($this->questions[$index]['options']);

        // Check if there are no options left for the question
        if (count($this->questions[$index]['options']) === 0) {
            // Optionally, remove the entire question
            unset($this->questions[$index]);
        }
    }



    public function saveQuestions()
    {
        $this->validate();

        foreach ($this->questions as $index => $questionData) {
            $question = Question::updateOrCreate(
                ['id' => $questionData['id'] ?? null],
                [
                    'exam_id' => $this->exam_id,
                    'question_text' => $questionData['question_text'],
                    'exam_section' => $questionData['exam_section'],
                    'marks' => $questionData['marks'] ?? 1,
                    'explanation' => $questionData['explanation'],
                ]
            );

            // Delete existing options
            Option::where('question_id', $question->id)->delete();

            // Save or update options
            foreach ($questionData['options'] as $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }
        session()->flash('message', 'Questions saved successfully.');
        $this->loadQuestions();
    }



    public function deleteQuestion($questionId)
    {
        Question::find($questionId)->delete();
        session()->flash('message', 'Question deleted successfully.');
        $this->loadQuestions();
    }



    // ...

    public function importQuestions()
    {
        // $this->validate(['bulk_file' => 'required|file|mimes:xlsx,csv,ods,tsv']);
        $this->validate(['bulk_file' => 'required|file']);


        // $this->validate([
        //     'bulk_file' => 'required|file|mimetypes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        // ]);


        // Store the uploaded file in the 'public/datasets' directory
        $filePath = $this->bulk_file->storeAs('public/datasets', $this->bulk_file->getClientOriginalName());
        $publicPath = Storage::path($filePath); // Get the full system path to the stored file

        Log::info('File stored for import:', ['path' => $publicPath]);

        // Determine the file type based on its extension
        $extension = $this->bulk_file->getClientOriginalExtension();
        $readerType = $this->getReaderType($extension);

        if (!$readerType) {
            session()->flash('error', 'Unsupported file type.');
            return;
        }

        try {
            // Import the questions using Maatwebsite Excel
            Excel::import(
                new QuestionImport($this->exam_id),
                $publicPath,
                null,
                $readerType
            );

            session()->flash('message', 'Questions imported successfully.');
            $this->loadQuestions();
        } catch (\Exception $e) {
            Log::error('Error during import:', ['message' => $e->getMessage()]);
            session()->flash('error', 'An error occurred during the import process. Please check the file and try again.');
        }
    }


    /**
     * Determine the Reader Type based on file extension.
     */
    protected function getReaderType($extension)
    {
        switch (strtolower($extension)) {
            case 'csv':
                return MaatExcel::CSV;
            case 'xlsx':
                return MaatExcel::XLSX;
            case 'xls':
                return MaatExcel::XLS;
            case 'ods':
                return MaatExcel::ODS;
            case 'tsv':
                return MaatExcel::TSV;
            default:
                return null;
        }
    }

    // public function importQuestions()
    // {
    //     $this->validate(['bulk_file' => 'required|file|mimes:xlsx,csv']);

    //     // Store the file and get its path
    //     $filePath = $this->bulk_file->storeAs('public/files', $this->bulk_file->getClientOriginalName());

    //     Log::info('File MIME Type:', ['mime' => $this->bulk_file->getMimeType()]);
    //     Log::info('Stored File Path:', ['path' => $filePath]);

    //     // Get the full path and import the file
    //     $fullPath = Storage::path($filePath);
    //     Log::info('Full File Path for Import:', ['full_path' => $fullPath]);

    //     Excel::import(new QuestionImport($this->exam_id), $fullPath);

    //     session()->flash('message', 'Questions imported successfully.');
    //     $this->loadQuestions();
    // }



    public function render()
    {
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('livewire.question-bank', ['exams' => $exams, 'questions' => $this->questions]);
    }
}
