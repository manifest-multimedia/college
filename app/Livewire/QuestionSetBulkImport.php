<?php

namespace App\Livewire;

use App\Imports\QuestionSetImport;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class QuestionSetBulkImport extends Component
{
    use WithFileUploads;

    public $questionSetId;

    public $questionSet;

    public $questionsCount = 0; // Pre-calculated count to avoid dynamic queries in view

    public $file;

    public $importFormat = 'excel'; // 'excel' or 'aiken'

    public $previewData = [];

    public $importResults = null;

    public $isProcessing = false;

    public $showPreview = false;

    public $validationErrors = [];

    protected $rules = [
        'file' => 'required|file|max:10240', // 10MB max
        'importFormat' => 'required|in:excel,aiken',
    ];

    protected $messages = [
        'file.required' => 'Please select a file to import',
        'file.file' => 'The uploaded file is invalid',
        'file.max' => 'The file size must not exceed 10MB',
        'importFormat.required' => 'Please select an import format',
        'importFormat.in' => 'Invalid import format selected',
    ];

    public function mount($questionSetId)
    {
        $this->questionSetId = $questionSetId;

        // Eager load relationships to prevent loading issues in the view
        $this->questionSet = QuestionSet::with(['course'])->find($questionSetId);

        // If question set doesn't exist, we'll handle it in the view
        if ($this->questionSet) {
            // Ensure name is not null to prevent view errors
            if (! $this->questionSet->name) {
                $this->questionSet->name = 'Untitled Question Set';
            }

            // Pre-calculate questions count to avoid dynamic queries in view
            $this->questionsCount = $this->questionSet->questions()->count();
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        // Reset preview when file or format changes
        if (in_array($propertyName, ['file', 'importFormat'])) {
            $this->resetPreview();
        }
    }

    public function updatedFile()
    {
        // Auto-detect format based on file extension
        if ($this->file) {
            $extension = strtolower($this->file->getClientOriginalExtension());

            if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                $this->importFormat = 'excel';
            } elseif ($extension === 'txt') {
                $this->importFormat = 'aiken';
            }
        }
    }

    public function preview()
    {
        $this->validate();

        try {
            $this->isProcessing = true;
            $this->validationErrors = [];

            if ($this->importFormat === 'excel') {
                $this->previewExcelFile();
            } else {
                $this->previewAikenFile();
            }

            $this->showPreview = true;

        } catch (\Exception $e) {
            Log::error('Import preview error: '.$e->getMessage());
            session()->flash('error', 'Error previewing file: '.$e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    private function previewExcelFile()
    {
        // Store file temporarily and parse
        $path = $this->file->store('temp-imports');
        $fullPath = Storage::path($path);

        try {
            $data = Excel::toArray(new QuestionSetImport($this->questionSetId), $fullPath);
            $rows = $data[0] ?? []; // First sheet

            $this->previewData = [];
            $lineNumber = 2; // Start from row 2 (after header)

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                } // Skip header row

                $questionData = $this->parseExcelRow($row, $lineNumber);

                if ($questionData) {
                    $this->previewData[] = $questionData;
                }

                $lineNumber++;

                // Limit preview to first 20 questions
                if (count($this->previewData) >= 20) {
                    break;
                }
            }

        } finally {
            // Clean up temporary file
            Storage::delete($path);
        }
    }

    private function parseExcelRow($row, $lineNumber)
    {
        // Map array indices to expected column names
        $questionText = $row[0] ?? '';
        $optionOne = $row[1] ?? '';
        $optionTwo = $row[2] ?? '';
        $optionThree = $row[3] ?? '';
        $optionFour = $row[4] ?? '';
        $correctOption = $row[5] ?? '';
        $marks = $row[6] ?? 1;
        $explanation = $row[7] ?? '';
        $examSection = $row[8] ?? '';

        // Validate required fields
        if (empty($questionText)) {
            $this->validationErrors[] = "Line {$lineNumber}: Question text is required";

            return null;
        }

        if (empty($correctOption)) {
            $this->validationErrors[] = "Line {$lineNumber}: Correct option is required";

            return null;
        }

        // Validate options
        $options = [];
        if (! empty($optionOne)) {
            $options[] = ['text' => $optionOne, 'label' => 'A'];
        }
        if (! empty($optionTwo)) {
            $options[] = ['text' => $optionTwo, 'label' => 'B'];
        }
        if (! empty($optionThree)) {
            $options[] = ['text' => $optionThree, 'label' => 'C'];
        }
        if (! empty($optionFour)) {
            $options[] = ['text' => $optionFour, 'label' => 'D'];
        }

        if (count($options) < 2) {
            $this->validationErrors[] = "Line {$lineNumber}: At least 2 options are required";

            return null;
        }

        // Determine correct option index
        $correctIndex = $this->determineCorrectOption($correctOption, $options);
        if ($correctIndex === null) {
            $this->validationErrors[] = "Line {$lineNumber}: Invalid correct option: {$correctOption}";

            return null;
        }

        return [
            'line' => $lineNumber,
            'question_text' => $questionText,
            'options' => $options,
            'correct_option' => $correctIndex,
            'marks' => intval($marks) ?: 1,
            'explanation' => $explanation,
            'exam_section' => $examSection,
            'is_valid' => true,
        ];
    }

    private function previewAikenFile()
    {
        $content = file_get_contents($this->file->path());
        $questions = $this->parseAikenContent($content);

        $this->previewData = array_slice($questions, 0, 20); // Limit to first 20 for preview
    }

    private function parseAikenContent($content)
    {
        $questions = [];
        $blocks = preg_split('/\n\s*\n/', trim($content));
        $lineNumber = 1;

        foreach ($blocks as $block) {
            if (empty(trim($block))) {
                continue;
            }

            $questionData = $this->parseAikenBlock(trim($block), $lineNumber);
            if ($questionData) {
                $questions[] = $questionData;
            }

            $lineNumber += count(explode("\n", $block)) + 1;
        }

        return $questions;
    }

    private function parseAikenBlock($block, $startLine)
    {
        $lines = explode("\n", $block);
        $questionText = '';
        $options = [];
        $correctAnswer = '';
        $feedback = '';

        $currentLine = $startLine;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^([A-D])\.(.+)/', $line, $matches)) {
                // This is an option
                $options[] = [
                    'label' => $matches[1],
                    'text' => trim($matches[2]),
                ];
            } elseif (preg_match('/^ANSWER:\s*([A-D])/i', $line, $matches)) {
                // This is the answer
                $correctAnswer = strtoupper($matches[1]);
            } elseif (preg_match('/^FEEDBACK:\s*(.+)/i', $line, $matches)) {
                // This is feedback
                $feedback = trim($matches[1]);
            } elseif (empty($questionText) && ! empty($line)) {
                // This is the question text (first non-empty line)
                $questionText = $line;
            }

            $currentLine++;
        }

        // Validate
        if (empty($questionText)) {
            $this->validationErrors[] = "Line {$startLine}: Question text is required";

            return null;
        }

        if (count($options) < 2) {
            $this->validationErrors[] = "Line {$startLine}: At least 2 options are required";

            return null;
        }

        if (empty($correctAnswer)) {
            $this->validationErrors[] = "Line {$startLine}: ANSWER line is required";

            return null;
        }

        // Find correct option index
        $correctIndex = null;
        foreach ($options as $index => $option) {
            if ($option['label'] === $correctAnswer) {
                $correctIndex = $index;
                break;
            }
        }

        if ($correctIndex === null) {
            $this->validationErrors[] = "Line {$startLine}: Correct answer '{$correctAnswer}' not found in options";

            return null;
        }

        return [
            'line' => $startLine,
            'question_text' => $questionText,
            'options' => $options,
            'correct_option' => $correctIndex,
            'marks' => 1, // Default for Aiken format
            'explanation' => $feedback,
            'exam_section' => '',
            'is_valid' => true,
        ];
    }

    private function determineCorrectOption($correctOption, $options)
    {
        $correctOption = strtolower(trim($correctOption));

        // Check for option labels (A, B, C, D)
        if (in_array($correctOption, ['a', 'b', 'c', 'd'])) {
            $labelMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3];

            return $labelMap[$correctOption] ?? null;
        }

        // Check for option names (option_one, option_two, etc.)
        $nameMap = [
            'option_one' => 0,
            'option one' => 0,
            'option_two' => 1,
            'option two' => 1,
            'option_three' => 2,
            'option three' => 2,
            'option_four' => 3,
            'option four' => 3,
        ];

        return $nameMap[$correctOption] ?? null;
    }

    public function import()
    {
        if (empty($this->previewData)) {
            session()->flash('error', 'Please preview the file first before importing.');

            return;
        }

        if (! empty($this->validationErrors)) {
            session()->flash('error', 'Please fix validation errors before importing.');

            return;
        }

        try {
            $this->isProcessing = true;

            DB::beginTransaction();

            $imported = 0;
            $failed = 0;
            $errors = [];

            foreach ($this->previewData as $questionData) {
                try {
                    // Create question
                    $question = Question::create([
                        'question_set_id' => $this->questionSetId,
                        'question_text' => $questionData['question_text'],
                        'mark' => $questionData['marks'],
                        'explanation' => $questionData['explanation'],
                        'exam_section' => $questionData['exam_section'],
                        'type' => 'multiple_choice',
                        'difficulty_level' => 'medium', // Default
                    ]);

                    // Create options
                    foreach ($questionData['options'] as $index => $optionData) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $optionData['text'],
                            'is_correct' => $index === $questionData['correct_option'],
                        ]);
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Line {$questionData['line']}: ".$e->getMessage();
                }
            }

            DB::commit();

            $this->importResults = [
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ];

            session()->flash('success', "Import completed! {$imported} questions imported successfully".($failed > 0 ? ", {$failed} failed" : ''));

            // Reset form
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question import error: '.$e->getMessage());
            session()->flash('error', 'Import failed: '.$e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetPreview()
    {
        $this->previewData = [];
        $this->showPreview = false;
        $this->validationErrors = [];
        $this->importResults = null;
    }

    public function resetForm()
    {
        $this->reset(['file', 'previewData', 'showPreview', 'validationErrors', 'importResults']);
        $this->importFormat = 'excel';
    }

    public function render()
    {
        return view('livewire.question-set-bulk-import');
    }
}
