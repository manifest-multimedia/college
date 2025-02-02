<?php

namespace App\Imports;

use App\Models\ExamSession;
use App\Models\Student;
use App\Models\Question;
use App\Models\Response;
use App\Models\ScoredQuestion;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ExamResultsImport implements ToCollection, WithHeadingRow
{
    protected $exam_id;
    protected $matchedStudents = 0;
    protected $totalRecords = 0;
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
                // Validate required fields
                if (!$this->validateRow($row)) {
                    $this->failedRecords++;
                    continue;
                }

                // Find or create student
                $student = $this->findStudent($row);
                if (!$student) {
                    $this->failedRecords++;
                    continue;
                }

                // Process exam session
                $session = $this->processExamSession($row, $student);
                
                // Process questions and responses
                $this->processQuestions($row, $session);

                $this->matchedStudents++;

            } catch (\Exception $e) {
                Log::error('Import Error: '.$e->getMessage());
                $this->failedRecords++;
            }
        }
    }

    private function validateRow($row)
    {
        return isset($row['student_email']) && 
               isset($row['question_text']) && 
               isset($row['selected_option']);
    }

    private function findStudent($row)
    {
        return Student::with('user')
            ->where('email', $row['student_email'])
            ->orWhere('student_id', $row['student_id'] ?? '')
            ->first();
    }

    private function processExamSession($row, $student)
    {
        return ExamSession::firstOrCreate(
            [
                'exam_id' => $this->exam_id,
                'student_id' => $student->user->id,
            ],
            [
                'started_at' => Carbon::parse($row['session_started_at'] ?? now()),
                'completed_at' => Carbon::parse($row['session_completed_at'] ?? now()),
                'score' => $row['score'] ?? 0,
            ]
        );
    }

    private function processQuestions($row, $session)
    {
        $question = Question::where('question_text', $row['question_text'])
            ->orWhere('question_text', 'like', '%'.$row['question_text'].'%')
            ->where('exam_id', $this->exam_id)
            ->first();

        if (!$question) {
            Log::warning('Question not found: '.$row['question_text']);
            return;
        }

        $option = $question->options()
            ->where('option_text', $row['selected_option'])
            ->orWhere('option_text', 'like', '%'.$row['selected_option'].'%')
            ->first();

        if (!$option) {
            Log::warning('Option not found: '.$row['selected_option']);
            return;
        }

        // Create or update response
        $response = Response::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
            ],
            [
                'option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'selected_option' => $option->id,
            ]
        );

        // Create scored question relationship
        ScoredQuestion::firstOrCreate([
            'exam_session_id' => $session->id,
            'question_id' => $question->id,
            'response_id' => $response->id,
        ]);

        // Update session score if needed
        if ($option->is_correct) {
            $session->increment('score');
        }
    }

    public function getImportResults(): array
    {
        return [
            'total' => $this->totalRecords,
            'success' => $this->matchedStudents,
            'failed' => $this->failedRecords
        ];
    }
}