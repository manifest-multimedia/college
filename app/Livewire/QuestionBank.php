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

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Handle the import of questions via an uploaded file (xlsx, csv, ods, tsv).
     *
     * Validates the uploaded file, imports the questions using the QuestionImport
     * class, and flashes a success message if the import is successful. If an
     * error occurs, it is logged and a flash error is displayed.
     *
     * @return void
     */
    /******  b14fd3b7-45ea-4f23-940b-2ab1b252602b  *******/
    // public function importQuestions()
    // {
    //     // Validate the uploaded file
    //     $this->validate([
    //         'bulk_file' => 'required|mimes:xlsx,csv,ods,tsv',
    //     ]);

    //     try {
    //         // Import the file using its temporary path
    //         Excel::import(new QuestionImport($this->exam_id), $this->bulk_file->path());

    //         // Flash success message
    //         session()->flash('message', 'Questions imported successfully.');
    //         $this->loadQuestions(); // Reload questions after import
    //     } catch (\Throwable $e) {
    //         // Log and handle errors
    //         Log::error('Error during import', ['error' => $e->getMessage()]);
    //         session()->flash('error', 'An error occurred during the import process. Please check the file and try again.');
    //     }
    // }




    /**
     * Determine the Reader Type based on the file extension.
     *
     * @param string $extension
     * @return string|null
     */
    private function getReaderType(string $extension): ?string
    {
        return match (strtolower($extension)) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv'  => \Maatwebsite\Excel\Excel::CSV,
            'ods'  => \Maatwebsite\Excel\Excel::ODS,
            'tsv'  => \Maatwebsite\Excel\Excel::TSV,
            default => null,
        };
    }


    public function importQuestions()
    {

        try {
            // Store the file on the 'uploads' disk and get its path
            $filePath = $this->bulk_file->store('files', 'uploads');

            // Log the MIME type and stored file path for debugging
            Log::info('File MIME Type:', ['mime' => $this->bulk_file->getMimeType()]);
            Log::info('Stored File Path:', ['path' => $filePath]);

            // Use the correct storage disk path
            $fullPath = Storage::disk('uploads')->path($filePath);

            // Log the full path for debugging
            Log::info('Full File Path for Import:', ['full_path' => $fullPath]);

            // Import the file
            Excel::import(new QuestionImport($this->exam_id), $fullPath);

            // Flash a success message
            session()->flash('message', 'Questions imported successfully.');

            // Reload questions after import
            $this->loadQuestions();
        } catch (\Throwable $e) {
            // Log the error details and flash an error message
            Log::error('Error during import', ['error' => $e->getMessage()]);
            session()->flash('error', 'An error occurred during the import process. Please check the file and try again.');
        }
    }



    public function render()
    {
        $exams = Exam::where('user_id', Auth::user()->id)->get();
        return view('livewire.question-bank', ['exams' => $exams, 'questions' => $this->questions]);
    }
}
