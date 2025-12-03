<?php

namespace App\Livewire;

use App\Exports\BulkExportResults;
use App\Exports\ExamResultExport;
use App\Exports\ExamResultsExport;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ScoredQuestion;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsModule extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $chunkSize = 100; // Process in chunks of 100

    public $mode = 'index';

    public $selected_exam_id;

    public $isGeneratingResults = false;

    public $processingProgress = 0; // Track progress

    public $results; // Add this line to store the results

    public $selected_college_class_id;

    public $collegeClasses;

    public function mount()
    {
        $this->results = collect();
        $this->selected_college_class_id = null;
        $this->collegeClasses = CollegeClass::all();
    }

    public function updated($property)
    {
        if ($property == 'selected_exam_id') {
            // Fetch distinct college classes for students who took the selected exam
            $this->collegeClasses = CollegeClass::distinct()->whereIn('id', function ($query) {
                $query->select('college_class_id')
                    ->from('students')
                    ->whereIn('id', function ($subQuery) {
                        $subQuery->select('student_id')
                            ->from('exam_sessions')
                            ->where('exam_id', $this->selected_exam_id);
                    });
            })->get();
        }
    }

    public function generateResults()
    {
        $this->isGeneratingResults = true;
        $this->processingProgress = 0;

        $exam = Exam::find($this->selected_exam_id);
        $questions_per_session = $exam->questions_per_session ?? $exam->number_of_questions;

        $examSessions = ExamSession::where('exam_id', $this->selected_exam_id)
            ->when($this->selected_college_class_id, function ($query) {
                $query->whereHas('student', function ($subQuery) {
                    $subQuery->where('college_class_id', $this->selected_college_class_id);
                });
            })
            ->with([
                'student.user',
                'exam.course',
                'scoredQuestions' => function ($query) {
                    $query->with(['question.options', 'response']);
                },
            ])
            ->get();

        $this->results = $this->processExamSessions($examSessions, $questions_per_session, $exam);
        $this->results = $this->results->sortBy(function ($session) {
            // Extract the numeric part of student_id
            preg_match('/\d+$/', $session['student_id'], $matches);

            return (int) ($matches[0] ?? 0);
        });

        $this->isGeneratingResults = false;
        $this->processingProgress = 100;
    }

    protected function processExamSessions($examSessions, $questions_per_session, $exam)
    {
        $resultsService = app(\App\Services\ResultsService::class);

        return $examSessions->map(function ($session) use ($questions_per_session, $resultsService) {
            try {
                // Ensure scored questions are stored for this session
                $this->ensureScoredQuestionsExist($session, $questions_per_session);

                // Get scored responses
                $scoredResponses = $session->scoredQuestions->map(function ($scoredQuestion) {
                    return $scoredQuestion->response;
                });

                // Use ResultsService for consistent calculation
                $result = $resultsService->calculateOnlineExamScore(
                    $session,
                    $questions_per_session,
                    $scoredResponses
                );

                return [
                    'date' => $session->created_at->format('Y-m-d'),
                    'student_id' => $session->student->student_id ?? 'N/A',
                    'student_name' => $session->student->user->name ?? 'N/A',
                    'course' => $session->exam->course->name ?? 'N/A',
                    'score' => $result['score'],
                    'answered' => $result['total_answered'].'/'.$questions_per_session,
                    'percentage' => $result['percentage'],
                    'session_id' => $session->id,
                ];
            } catch (\Exception $e) {
                Log::error('Error processing exam session', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        })->filter();
    }

    public function render()
    {

        return view('livewire.exam-results-module', [
            'exams' => Exam::with('course')->get(),
            'results' => $this->results ?? collect(),

        ]);
    }

    protected function ensureScoredQuestionsExist($session, $questions_per_session)
    {
        // Only create scored questions if they don't exist
        if ($session->scoredQuestions->isEmpty()) {
            // Get the first X responses chronologically
            $responses = $session->responses()
                ->with('question')
                ->orderBy('created_at')
                ->take($questions_per_session)
                ->get();

            // Store these as scored questions
            foreach ($responses as $response) {
                ScoredQuestion::create([
                    'exam_session_id' => $session->id,
                    'question_id' => $response->question_id,
                    'response_id' => $response->id,
                ]);
            }

            // Refresh the session to get the new scored questions
            $session->load('scoredQuestions.question.options', 'scoredQuestions.response');
        } else {
            // Refresh the session to get the existing scored questions
            $session->load('scoredQuestions.question.options', 'scoredQuestions.response');
        }
    }

    public function exportResults()
    {
        if (! $this->selected_exam_id && ! $this->selected_college_class_id) {
            return;
        }

        $exam = Exam::find($this->selected_exam_id);
        $collegeClass = CollegeClass::find($this->selected_college_class_id);

        // Properly sanitize class name to remove any invalid characters
        $sanitizedClassName = preg_replace('/[\/\\\\:*?"<>|]/', '-', $collegeClass->name);

        $filename = Str::slug($exam->course->name).'-'.$sanitizedClassName.'-results.xlsx';

        return Excel::download(new ExamResultsExport($this->selected_exam_id, $this->selected_college_class_id), $filename);
    }

    public function exportStudentResult($student_id)
    {
        $formatted_id = str_replace('/', '-', $student_id);

        $exam = Exam::find($this->selected_exam_id);
        $filename = Str::slug($exam->course->name).'-'.$formatted_id.'-results-for-sync.xlsx';

        return Excel::download(new ExamResultExport($this->selected_exam_id, $student_id), $filename);
    }

    public function exportBulkResults()
    {
        $exam = Exam::find($this->selected_exam_id);
        $filename = Str::slug($exam->course->name).'-bulk-results.xlsx';

        return Excel::download(new BulkExportResults($this->selected_exam_id), $filename);
    }
}
