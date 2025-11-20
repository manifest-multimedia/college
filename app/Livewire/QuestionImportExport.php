<?php

namespace App\Livewire;

use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class QuestionImportExport extends Component
{
    use WithFileUploads;

    public $question_set_id;

    public $file;

    public $export_format = 'csv';

    public $import_results = [];

    public $show_import_results = false;

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:2048',
        'question_set_id' => 'required|exists:question_sets,id',
    ];

    public function mount($questionSetId = null)
    {
        $this->question_set_id = $questionSetId;
    }

    public function importQuestions()
    {
        $this->validate();

        $questionSet = QuestionSet::find($this->question_set_id);

        // Check permissions
        if (Auth::user()->role !== 'Super Admin' && $questionSet->created_by !== Auth::id()) {
            session()->flash('error', 'You do not have permission to import questions to this question set.');

            return;
        }

        $path = $this->file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        // Remove header row if exists
        if (count($data) > 0 && $this->isHeaderRow($data[0])) {
            array_shift($data);
        }

        $importResults = [
            'success' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($data as $index => $row) {
            try {
                $this->importQuestionRow($row, $index + 1, $importResults);
            } catch (\Exception $e) {
                $importResults['errors']++;
                $importResults['details'][] = 'Row '.($index + 1).': '.$e->getMessage();
            }
        }

        $this->import_results = $importResults;
        $this->show_import_results = true;
        $this->file = null;

        session()->flash('message',
            "Import completed: {$importResults['success']} questions imported successfully, {$importResults['errors']} errors."
        );
    }

    private function isHeaderRow($row)
    {
        // Check if first row looks like headers
        return in_array(strtolower($row[0] ?? ''), ['question', 'question_text', 'question text']);
    }

    private function importQuestionRow($row, $rowNumber, &$results)
    {
        // Expected CSV format: question_text, option1, option2, option3, option4, correct_option_number, marks, difficulty, explanation
        if (count($row) < 6) {
            throw new \Exception('Insufficient columns. Expected at least 6 columns.');
        }

        $questionText = trim($row[0]);
        $option1 = trim($row[1] ?? '');
        $option2 = trim($row[2] ?? '');
        $option3 = trim($row[3] ?? '');
        $option4 = trim($row[4] ?? '');
        $correctOptionNumber = (int) ($row[5] ?? 1);
        $marks = (int) ($row[6] ?? 1);
        $difficulty = strtolower(trim($row[7] ?? 'medium'));
        $explanation = trim($row[8] ?? '');

        // Validate required fields
        if (empty($questionText) || empty($option1) || empty($option2)) {
            throw new \Exception('Question text and at least 2 options are required.');
        }

        // Validate difficulty
        if (! in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'medium';
        }

        // Create question
        $question = Question::create([
            'question_set_id' => $this->question_set_id,
            'question_text' => $questionText,
            'mark' => $marks,
            'explanation' => $explanation,
            'type' => 'MCQ',
            'difficulty_level' => $difficulty,
        ]);

        // Create options
        $options = array_filter([$option1, $option2, $option3, $option4]);
        foreach ($options as $index => $optionText) {
            if (! empty($optionText)) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => ($index + 1) === $correctOptionNumber,
                ]);
            }
        }

        $results['success']++;
        $results['details'][] = "Row $rowNumber: Question imported successfully.";
    }

    public function exportQuestions()
    {
        $questionSet = QuestionSet::find($this->question_set_id);

        if (! $questionSet) {
            session()->flash('error', 'Question set not found.');

            return;
        }

        // Check permissions
        if (Auth::user()->role !== 'Super Admin' && $questionSet->created_by !== Auth::id()) {
            session()->flash('error', 'You do not have permission to export questions from this question set.');

            return;
        }

        $questions = $questionSet->questions()->with('options')->get();

        if ($questions->isEmpty()) {
            session()->flash('error', 'No questions found to export.');

            return;
        }

        $filename = 'questions_'.str_replace(' ', '_', $questionSet->name).'_'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($questions) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Question Text',
                'Option 1',
                'Option 2',
                'Option 3',
                'Option 4',
                'Correct Option Number',
                'Marks',
                'Difficulty',
                'Explanation',
            ]);

            foreach ($questions as $question) {
                $options = $question->options->pluck('option_text')->toArray();
                $correctOptionNumber = 0;

                // Find correct option number
                foreach ($question->options as $index => $option) {
                    if ($option->is_correct) {
                        $correctOptionNumber = $index + 1;
                        break;
                    }
                }

                // Pad options array to 4 elements
                while (count($options) < 4) {
                    $options[] = '';
                }

                fputcsv($handle, [
                    $question->question_text,
                    $options[0] ?? '',
                    $options[1] ?? '',
                    $options[2] ?? '',
                    $options[3] ?? '',
                    $correctOptionNumber,
                    $question->mark,
                    $question->difficulty_level,
                    $question->explanation ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function render()
    {
        $questionSet = QuestionSet::find($this->question_set_id);

        return view('livewire.question-import-export', [
            'questionSet' => $questionSet,
        ]);
    }
}
