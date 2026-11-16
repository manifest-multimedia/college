<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Services\ResultsService;
use Illuminate\Http\Request;

class StudentAnswerReviewController extends Controller
{
    private function accessibleExams(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer', 'Lecturer']), 403);
        $query = Exam::query();
        $isLecturerOnly = $user->hasRole('Lecturer') && ! $user->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']);
        if ($isLecturerOnly) {
            if (config('branding.theme_settings.lecturer_access_mode', 'exam_creator') === 'course_assignment') {
                $query->whereIn('course_id', $user->assignedCourses()->select('subjects.id'));
            } else {
                $query->where('user_id', $user->id);
            }
        }
        return $query;
    }

    public function index(Request $request)
    {
        $exams = $this->accessibleExams($request)->with('course')->orderByDesc('id')->get();
        $selectedExam = $request->filled('exam') ? $exams->firstWhere('id', (int) $request->query('exam')) : null;
        if ($request->filled('exam')) abort_unless($selectedExam, 403);
        $sessions = $selectedExam ? ExamSession::query()
            ->select(['id', 'exam_id', 'student_id', 'completed_at', 'auto_submitted', 'is_restored'])
            ->with('student:id,name')
            ->where('exam_id', $selectedExam->id)
            ->whereNotNull('completed_at')->where('completed_at', '<=', now())
            ->where('is_restored', false)
            ->orderByDesc('completed_at')->paginate(20)->withQueryString() : null;
        return view('exams.student-answer-review-index', compact('exams', 'selectedExam', 'sessions'));
    }

    public function show(Request $request, ExamSession $session, ResultsService $results)
    {
        abort_unless($this->accessibleExams($request)->whereKey($session->exam_id)->exists(), 403);
        abort_unless($session->completed_at && $session->completed_at->isPast() && ! $session->is_restored, 404);
        // Deliberately load academic data only. No device, access-log, IP or browser fields.
        $session->load(['exam.course', 'student:id,name', 'sessionQuestions.question.options', 'responses.question.options']);
        $responses = $session->responses->keyBy('question_id');
        $questions = $session->sessionQuestions->isNotEmpty()
            ? $session->sessionQuestions->pluck('question')->filter()
            : $session->exam->questions()->with('options')->get();
        $questions = $questions->take($session->exam->questions_per_session ?: $questions->count());
        $review = $questions->map(function ($question) use ($responses) {
            $response = $responses->get($question->id);
            $correct = $question->options->firstWhere('is_correct', true);
            $selected = $response ? $question->options->firstWhere('id', $response->selected_option) : null;
            return [
                'question' => $question->question_text,
                'selected' => $selected?->option_text,
                'correct' => $correct?->option_text,
                'status' => ! $response || ! $response->selected_option ? 'unanswered' : ($correct && (int) $response->selected_option === (int) $correct->id ? 'correct' : 'incorrect'),
                'mark' => (float) ($question->mark ?? 1),
            ];
        })->values();
        $score = $results->calculateOnlineExamScore($session);
        return view('exams.student-answer-review-show', compact('session', 'review', 'score'));
    }
}
