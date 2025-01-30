<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Livewire\WithPagination;
use App\Exports\ExamResultsExport;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Models\ScoredQuestion;

class ExamResultsModule extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $chunkSize = 100; // Process in chunks of 100

    public $mode = 'index';
    public $selected_exam_id;
    public $isGeneratingResults = false;
    public $processingProgress = 0; // Track progress

    public function updatedSelectedExamId()
    {
        $this->isGeneratingResults = true;
        $this->processingProgress = 0;
        $this->resetPage();
    }

    protected function processExamSessions($examSessions, $questions_per_session, $exam)
    {
        // Increase PHP limits for this process
        ini_set('max_execution_time', 600); // 10 minutes
        ini_set('memory_limit', '512M');    // 512MB memory limit
        set_time_limit(600);                // Another way to set execution time

        try {
            $total = $examSessions->count();
            $processed = 0;

            return $examSessions->through(function ($session) use ($questions_per_session, $exam, $total, &$processed) {
                try {
                    // First, ensure scored questions are stored for this session
                    $this->ensureScoredQuestionsExist($session, $questions_per_session);

                    // Get number of correct answers from attempted questions
                    $correct_answers = $session->scoredQuestions
                        ->filter(function ($scoredQuestion) {
                            $correct_option = $scoredQuestion->question->options
                                ->where('is_correct', true)
                                ->first();
                            
                            return $correct_option && 
                                $scoredQuestion->response->selected_option == $correct_option->id;
                        })
                        ->count();

                    // Calculate total questions answered
                    $total_answered = $session->scoredQuestions->count();

                    // Update progress
                    $processed++;
                    $this->processingProgress = ($processed / $total) * 100;

                    return [
                        'date' => $session->created_at->format('Y-m-d'),
                        'student_id' => $session->student->student_id ?? 'N/A',
                        'student_name' => $session->student->user->name ?? 'N/A',
                        'course' => $session->exam->course->name ?? 'N/A',
                        'score' => $correct_answers . '/' . $questions_per_session,
                        'answered' => $total_answered . ' questions',
                        'percentage' => $questions_per_session > 0 
                            ? round(($correct_answers / $questions_per_session) * 100, 2)
                            : 0,
                        'session_id' => $session->id
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error processing exam session', [
                        'session_id' => $session->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter();

        } catch (\Exception $e) {
            \Log::error('Error in processExamSessions', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } finally {
            // Reset PHP limits to their default values
            ini_set('max_execution_time', 120);    // Default 30 seconds
            ini_set('memory_limit', '256M');      // Default memory limit
        }
    }

    public function render()
    {
        $results = collect();

        if ($this->selected_exam_id) {
            try {
                $exam = Exam::find($this->selected_exam_id);
                $questions_per_session = $exam->questions_per_session ?? $exam->questions->count();
                
                $examSessions = ExamSession::with([
                    'student.user', 
                    'exam.course', 
                    'scoredQuestions.question.options',
                    'scoredQuestions.response'
                ])
                ->where('exam_id', $this->selected_exam_id)
                ->paginate(25);

                $results = $this->processExamSessions($examSessions, $questions_per_session, $exam);
                
                $this->isGeneratingResults = false;
            } catch (\Exception $e) {
                \Log::error('Error generating results', [
                    'exam_id' => $this->selected_exam_id,
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'An error occurred while generating results. Please try again.');
                $this->isGeneratingResults = false;
            }
        }

        return view('livewire.exam-results-module', [
            'exams' => Exam::with('course')->get(),
            'results' => $results
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
                    'response_id' => $response->id
                ]);
            }

            // Refresh the session to get the new scored questions
            $session->load('scoredQuestions.question.options', 'scoredQuestions.response');
        }
    }

    public function exportResults()
    {
        if (!$this->selected_exam_id) {
            return;
        }
        
        $exam = Exam::find($this->selected_exam_id);
        $filename = Str::slug($exam->course->name) . '-results.xlsx';
        
        return Excel::download(new ExamResultsExport($this->selected_exam_id), $filename);
    }
}
