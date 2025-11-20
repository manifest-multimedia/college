<?php

namespace App\Jobs;

use App\Models\ExamSession;
use App\Models\ScoredQuestion;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExamSessionChunk implements ShouldQueue
{
    use Batchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionIds;

    protected $questionsPerSession;

    public function __construct($sessionIds, $questionsPerSession)
    {
        $this->sessionIds = $sessionIds;
        $this->questionsPerSession = $questionsPerSession;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        ExamSession::with([
            'student.user:id,name,student_id',
            'exam.course:id,name',
            'scoredQuestions.question.options:id,question_id,is_correct',
            'scoredQuestions.response:id,selected_option',
        ])
            ->whereIn('id', $this->sessionIds)
            ->each(function ($session) {
                // Process each session
                $this->ensureScoredQuestionsExist($session);
                // Calculate and store results
                // ... existing processing logic ...
            });
    }

    protected function ensureScoredQuestionsExist($session)
    {
        if ($session->scoredQuestions->isEmpty()) {
            $responses = $session->responses()
                ->with('question')
                ->orderBy('created_at')
                ->take($this->questionsPerSession)
                ->get();

            $scoredQuestions = $responses->map(function ($response) use ($session) {
                return [
                    'exam_session_id' => $session->id,
                    'question_id' => $response->question_id,
                    'response_id' => $response->id,
                ];
            });

            ScoredQuestion::insert($scoredQuestions->toArray());
            $session->load('scoredQuestions.question.options', 'scoredQuestions.response');
        }
    }
}
