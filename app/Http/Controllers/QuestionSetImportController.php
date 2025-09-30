<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\QuestionSet;
use App\Models\Question;
use App\Models\Option;
use App\Imports\QuestionSetImport;
use App\Imports\RawDataImport;

class QuestionSetImportController extends Controller
{
    /**
     * Show the bulk import page
     */
    public function index($questionSetId)
    {
        $questionSet = QuestionSet::with('course')->find($questionSetId);
        
        if (!$questionSet) {
            return redirect()->route('question.sets')
                ->with('error', 'Question set not found.');
        }
        
        // Pre-calculate questions count
        $questionsCount = $questionSet->questions()->count();
        
        return view('question-sets.import', compact('questionSet', 'questionsCount', 'questionSetId'));
    }

    /**
     * Detect columns in Excel file for mapping
     */
    public function detectColumns(Request $request, $questionSetId)
    {
        $request->validate([
            'import_file' => 'required|file|max:10240|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('import_file');
            $path = $file->store('temp-imports');
            $fullPath = Storage::path($path);

            // Read first few rows to detect columns (using raw import to get actual headers)
            $data = Excel::toArray(new RawDataImport(), $fullPath);
            $rows = $data[0] ?? [];
            
            $columns = [];
            $sampleData = [];
            
            if (!empty($rows)) {
                // Get column headers (first row)
                $headers = $rows[0] ?? [];
                
                // Get sample data (next 3 rows)
                $sampleRows = array_slice($rows, 1, 3);
                
                // Prepare column information
                foreach ($headers as $index => $header) {
                    $samples = [];
                    foreach ($sampleRows as $row) {
                        if (isset($row[$index]) && !empty($row[$index])) {
                            $samples[] = (string)$row[$index];
                        }
                    }
                    
                    // Ensure header is a string and handle empty headers
                    $headerName = !empty($header) ? (string)$header : "Column " . ((int)$index + 1);
                    
                    $columns[] = [
                        'index' => $index,
                        'name' => $headerName,
                        'samples' => array_slice($samples, 0, 2) // First 2 non-empty samples
                    ];
                }
                
                $sampleData = $sampleRows;
            }

            // Clean up temp file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'sample_data' => $sampleData,
                'total_rows' => count($rows) - 1 // Excluding header
            ]);

        } catch (\Exception $e) {
            Log::error('Column detection error: ' . $e->getMessage());
            return response()->json(['error' => 'Error reading file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview import file via AJAX
     */
    public function preview(Request $request, $questionSetId)
    {
        $request->validate([
            'import_file' => 'required|file|max:10240|mimes:xlsx,xls,csv,txt',
            'format' => 'required|in:excel,aiken',
            'column_mapping' => 'nullable|json' // For Excel column mapping
        ]);

        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        try {
            $file = $request->file('import_file');
            $format = $request->input('format');
            $columnMapping = $request->input('column_mapping') ? json_decode($request->input('column_mapping'), true) : null;

            if ($format === 'excel') {
                $previewData = $this->previewExcelFile($file, $questionSetId, $columnMapping);
            } else {
                $previewData = $this->previewAikenFile($file);
            }

            return response()->json([
                'success' => true,
                'preview' => $previewData['questions'],
                'errors' => $previewData['errors'],
                'total' => count($previewData['questions'])
            ]);

        } catch (\Exception $e) {
            Log::error('Import preview error: ' . $e->getMessage());
            return response()->json(['error' => 'Error previewing file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process the import via AJAX
     */
    public function import(Request $request, $questionSetId)
    {
        $request->validate([
            'import_file' => 'required|file|max:10240|mimes:xlsx,xls,csv,txt',
            'format' => 'required|in:excel,aiken',
            'column_mapping' => 'nullable|json'
        ]);

        $questionSet = QuestionSet::find($questionSetId);
        if (!$questionSet) {
            return response()->json(['error' => 'Question set not found.'], 404);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('import_file');
            $format = $request->input('format');
            $columnMapping = $request->input('column_mapping') ? json_decode($request->input('column_mapping'), true) : null;

            if ($format === 'excel') {
                $result = $this->importExcelFile($file, $questionSetId, $columnMapping);
            } else {
                $result = $this->importAikenFile($file, $questionSetId);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $result['imported'],
                'failed' => $result['failed'],
                'errors' => $result['errors']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Question import error: ' . $e->getMessage());
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview Excel/CSV file with optional column mapping
     */
    private function previewExcelFile($file, $questionSetId, $columnMapping = null)
    {
        $path = $file->store('temp-imports');
        $fullPath = Storage::path($path);
        $questions = [];
        $errors = [];

        try {
            // Use RawDataImport to get raw data that matches our column mapping indices
            $data = Excel::toArray(new RawDataImport(), $fullPath);
            $rows = $data[0] ?? [];
            $lineNumber = 2;

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header row

                $questionData = $this->parseExcelRow($row, $lineNumber, $columnMapping);
                
                if (!empty($questionData['errors'])) {
                    $errors = array_merge($errors, $questionData['errors']);
                } elseif ($questionData['question']) {
                    // Only add non-null questions (skip empty rows)
                    $questions[] = $questionData['question'];
                }
                
                $lineNumber++;
                
                // No limit for preview - show all questions
            }

        } finally {
            Storage::delete($path);
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * Preview Aiken file
     */
    private function previewAikenFile($file)
    {
        $content = file_get_contents($file->path());
        $blocks = preg_split('/\n\s*\n/', trim($content));
        $questions = [];
        $errors = [];
        $lineNumber = 1;

        foreach ($blocks as $block) {
            if (empty(trim($block))) continue;

            $questionData = $this->parseAikenBlock(trim($block), $lineNumber);
            
            if ($questionData['errors']) {
                $errors = array_merge($errors, $questionData['errors']);
            } else {
                $questions[] = $questionData['question'];
            }

            $lineNumber += count(explode("\n", $block)) + 1;
            
            // Limit preview to first 20 questions
            if (count($questions) >= 20) {
                break;
            }
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * Parse Excel row with column mapping
     */
    private function parseExcelRow($row, $lineNumber, $columnMapping = null)
    {
        // Use column mapping if provided, otherwise fall back to default positions
        if ($columnMapping) {
            $questionText = isset($columnMapping['question']) && isset($row[$columnMapping['question']]) ? (string)$row[$columnMapping['question']] : '';
            $optionOne = isset($columnMapping['option_one']) && isset($row[$columnMapping['option_one']]) ? (string)$row[$columnMapping['option_one']] : '';
            $optionTwo = isset($columnMapping['option_two']) && isset($row[$columnMapping['option_two']]) ? (string)$row[$columnMapping['option_two']] : '';
            $optionThree = isset($columnMapping['option_three']) && isset($row[$columnMapping['option_three']]) ? (string)$row[$columnMapping['option_three']] : '';
            $optionFour = isset($columnMapping['option_four']) && isset($row[$columnMapping['option_four']]) ? (string)$row[$columnMapping['option_four']] : '';
            $correctOption = isset($columnMapping['correct_option']) && isset($row[$columnMapping['correct_option']]) ? (string)$row[$columnMapping['correct_option']] : '';
            $marks = isset($columnMapping['marks']) && isset($row[$columnMapping['marks']]) ? (float)$row[$columnMapping['marks']] : 1;
            $explanation = isset($columnMapping['explanation']) && isset($row[$columnMapping['explanation']]) ? (string)$row[$columnMapping['explanation']] : '';
            $examSection = isset($columnMapping['exam_section']) && isset($row[$columnMapping['exam_section']]) ? (string)$row[$columnMapping['exam_section']] : '';
        } else {
            // Default column positions (backward compatibility)
            $questionText = (string)($row[0] ?? '');
            $optionOne = (string)($row[1] ?? '');
            $optionTwo = (string)($row[2] ?? '');
            $optionThree = (string)($row[3] ?? '');
            $optionFour = (string)($row[4] ?? '');
            $correctOption = (string)($row[5] ?? '');
            $marks = (float)($row[6] ?? 1);
            $explanation = (string)($row[7] ?? '');
            $examSection = (string)($row[8] ?? '');
        }

        // Skip completely empty rows
        $hasAnyData = !empty(trim($questionText)) || !empty(trim($optionOne)) || !empty(trim($optionTwo)) || 
                      !empty(trim($optionThree)) || !empty(trim($optionFour)) || !empty(trim($correctOption));
        
        if (!$hasAnyData) {
            return ['errors' => [], 'question' => null]; // Skip empty rows without error
        }

        $errors = [];

        // Validate required fields only for non-empty rows
        if (empty(trim($questionText))) {
            $errors[] = "Line {$lineNumber}: Question text is required";
        }

        if (empty(trim($correctOption))) {
            $errors[] = "Line {$lineNumber}: Correct option is required";
        }

        // Build options array
        $options = [];
        if (!empty(trim($optionOne))) $options[] = ['text' => trim($optionOne), 'label' => 'A'];
        if (!empty(trim($optionTwo))) $options[] = ['text' => trim($optionTwo), 'label' => 'B'];
        if (!empty(trim($optionThree))) $options[] = ['text' => trim($optionThree), 'label' => 'C'];
        if (!empty(trim($optionFour))) $options[] = ['text' => trim($optionFour), 'label' => 'D'];

        if (count($options) < 2) {
            $errors[] = "Line {$lineNumber}: At least 2 options are required";
        }

        $correctIndex = $this->determineCorrectOption($correctOption, $options);
        if ($correctIndex === null && !empty($correctOption)) {
            // Add more detailed error info for debugging
            $optionLabels = array_column($options, 'label');
            $optionTexts = array_column($options, 'text');
            Log::info("Correct option validation failed", [
                'correct_option' => $correctOption,
                'options' => $options,
                'option_labels' => $optionLabels,
                'option_texts' => $optionTexts,
                'line' => $lineNumber
            ]);
            $errors[] = "Line {$lineNumber}: Invalid correct option: {$correctOption}";
        }

        if (!empty($errors)) {
            return ['errors' => $errors, 'question' => null];
        }

        return [
            'errors' => [],
            'question' => [
                'line' => $lineNumber,
                'question_text' => trim($questionText),
                'options' => $options,
                'correct_option' => $correctIndex,
                'marks' => intval($marks) ?: 1,
                'explanation' => trim($explanation),
                'exam_section' => trim($examSection)
            ]
        ];
    }

    /**
     * Parse Aiken block
     */
    private function parseAikenBlock($block, $startLine)
    {
        $lines = explode("\n", $block);
        $questionText = '';
        $options = [];
        $correctAnswer = '';
        $feedback = '';
        $errors = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^([A-D])\.(.+)/', $line, $matches)) {
                $options[] = [
                    'label' => $matches[1],
                    'text' => trim($matches[2])
                ];
            } elseif (preg_match('/^ANSWER:\s*([A-D])/i', $line, $matches)) {
                $correctAnswer = strtoupper($matches[1]);
            } elseif (preg_match('/^FEEDBACK:\s*(.+)/i', $line, $matches)) {
                $feedback = trim($matches[1]);
            } elseif (empty($questionText) && !empty($line)) {
                $questionText = $line;
            }
        }

        if (empty($questionText)) {
            $errors[] = "Line {$startLine}: Question text is required";
        }

        if (count($options) < 2) {
            $errors[] = "Line {$startLine}: At least 2 options are required";
        }

        if (empty($correctAnswer)) {
            $errors[] = "Line {$startLine}: ANSWER line is required";
        }

        $correctIndex = null;
        foreach ($options as $index => $option) {
            if ($option['label'] === $correctAnswer) {
                $correctIndex = $index;
                break;
            }
        }

        if ($correctIndex === null && !empty($correctAnswer)) {
            $errors[] = "Line {$startLine}: Correct answer '{$correctAnswer}' not found in options";
        }

        if (!empty($errors)) {
            return ['errors' => $errors, 'question' => null];
        }

        return [
            'errors' => [],
            'question' => [
                'line' => $startLine,
                'question_text' => $questionText,
                'options' => $options,
                'correct_option' => $correctIndex,
                'marks' => 1,
                'explanation' => $feedback,
                'exam_section' => ''
            ]
        ];
    }

    /**
     * Import Excel file with optional column mapping
     */
    private function importExcelFile($file, $questionSetId, $columnMapping = null)
    {
        $previewData = $this->previewExcelFile($file, $questionSetId, $columnMapping);
        return $this->processImportData($previewData['questions'], $questionSetId, $previewData['errors']);
    }

    /**
     * Import Aiken file
     */
    private function importAikenFile($file, $questionSetId)
    {
        $previewData = $this->previewAikenFile($file);
        return $this->processImportData($previewData['questions'], $questionSetId, $previewData['errors']);
    }

    /**
     * Process import data
     */
    private function processImportData($questions, $questionSetId, $existingErrors = [])
    {
        $imported = 0;
        $failed = 0;
        $errors = $existingErrors;

        foreach ($questions as $questionData) {
            try {
                $question = Question::create([
                    'exam_id' => null, // For question set questions
                    'question_set_id' => $questionSetId,
                    'question_text' => $questionData['question_text'],
                    'mark' => $questionData['marks'],
                    'explanation' => $questionData['explanation'],
                    'exam_section' => $questionData['exam_section'],
                    'type' => 'MCQ', // Changed from 'multiple_choice' to 'MCQ'
                    'difficulty_level' => 'medium',
                ]);

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
                Log::error('Question import error: ' . $e->getMessage(), [
                    'question_data' => $questionData,
                    'line' => $questionData['line'] ?? 'unknown'
                ]);
                $errors[] = "Line {$questionData['line']}: " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Determine correct option index
     */
    private function determineCorrectOption($correctOption, $options)
    {
        $correctOption = strtolower(trim($correctOption));

        // Check for option labels (A, B, C, D) - but only if that option exists
        if (in_array($correctOption, ['a', 'b', 'c', 'd'])) {
            $labelMap = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3];
            $index = $labelMap[$correctOption] ?? null;
            // Only return the index if the option actually exists
            if ($index !== null && isset($options[$index])) {
                return $index;
            }
        }

        // Check for option names (option_one, option_two, etc.) - but only if that option exists
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

        $index = $nameMap[$correctOption] ?? null;
        // Only return the index if the option actually exists
        if ($index !== null && isset($options[$index])) {
            return $index;
        }

        // Check if the correct option matches any of the option texts exactly
        foreach ($options as $index => $option) {
            if (strtolower(trim($option['text'])) === $correctOption) {
                return $index;
            }
        }

        return null;
    }
}