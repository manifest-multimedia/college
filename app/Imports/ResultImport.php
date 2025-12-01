<?php

namespace App\Imports;

use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Response;
use App\Models\ScoredQuestion;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultImport implements ToCollection, WithHeadingRow
{
    protected $exam_id;

    protected $importedRecords = 0;

    protected $totalRecords = 0;

    protected $skippedRecords = 0;

    protected $failedRecords = 0;

    public function __construct($exam_id)
    {
        $this->exam_id = $exam_id;

    }

    public function collection(Collection $rows)
    {
        $this->totalRecords = $rows->count();

        foreach ($rows as $row) {
            try {
                if (! $this->validateRow($row)) {
                    $this->failedRecords++;

                    continue;
                }

                // Find or create student
                $student = $this->findStudent($row);
                if (! $student) {
                    $this->failedRecords++;

                    continue;
                }

                // Check if the exam session already exists
                if ($this->examSessionExists($student)) {
                    $this->skippedRecords++;

                    continue;
                }

                // Process exam session
                $session = $this->processExamSession($row, $student);

                // Process questions and responses
                $this->processQuestions($row, $session);

                $this->importedRecords++;

            } catch (\Exception $e) {
                Log::error('Import Error: '.$e->getMessage());
                $this->failedRecords++;
            }
        }
    }

    private function validateRow($row)
    {

        $validated = isset($row['student_email']) && isset($row['question_text']) && isset($row['selected_option_text']);

        return $validated;

    }

    private function findStudent($row)
    {

        // dd($row['student_email']);

        $student = Student::with('user')
            ->where('email', $row['student_email'])
            ->orWhere('student_id', $row['student_id'] ?? '')
            ->first();

        if ($student) {
            return $student;
        } else {
            return null;
        }
    }

    private function examSessionExists($student)
    {
        return ExamSession::where('exam_id', $this->exam_id)
            ->where('student_id', $student->user->id)
            ->exists();
    }

    private function processExamSession($row, $student)
    {
        if ($student->name != $row['student_name'] || $row['student_email'] == 'N/A' || $row['student_name'] == null) {
            return null;

        }

        return ExamSession::create([
            'exam_id' => $this->exam_id,
            'student_id' => $student->user->id,
            'started_at' => Carbon::parse($row['session_started_at'] ?? now()),
            'completed_at' => Carbon::parse($row['session_completed_at'] ?? now()),
            'score' => $row['score'] ?? 0,
        ]);
    }

    private function processQuestions($row, $session)
    {
        $question = Question::where('question_text', $row['question_text'])
            ->orWhere('question_text', 'like', '%'.$row['question_text'].'%')
            ->where('exam_id', $this->exam_id)
            ->first();

        if (! $question) {
            Log::warning('Question not found: '.$row['question_text']);

            return;
        }

        $option = $question->options()
            ->where('option_text', $row['selected_option_text'])
            ->orWhere('option_text', 'like', '%'.$row['selected_option_text'].'%')
            ->first();

        if (! $option) {
            Log::warning('Option not found: '.$row['selected_option_text']);

            return;
        }

        // Check if the response already exists
        $response = Response::where([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'option_id' => $option->id,
        ])->first();

        if (! $response) {
            // Create a new response if it doesn't exist
            $response = Response::create([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'selected_option' => $option->id,
                'selected_option_text' => $option->option_text, // Store text for historical integrity
            ]);
        }

        // Ensure the scored question record exists
        ScoredQuestion::firstOrCreate([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'response_id' => $response->id,
        ]);

        if ($option->is_correct) {
            $session->increment('score');
        }
    }

    public function getImportResults(): array
    {
        return [
            'total' => $this->totalRecords,
            'success' => $this->importedRecords,
            'failed' => $this->failedRecords,
            'skipped' => $this->skippedRecords,
        ];
    }
}
