<?php

namespace App\Livewire;

use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExamPreview extends Component
{
    public Exam $exam;

    public array $questions = [];

    public array $responses = [];

    public int $currentIndex = 0;

    public string $student_name = '';

    public string $student_index = 'PREVIEW';

    public string $theme = 'default';

    // Mock exam session properties to match OnlineExamination
    public $examSession;

    public $examExpired = false;

    public $hasExtraTime = false;

    public $canStillSubmit = false;

    public $extraTimeMinutes = 0;

    public $remainingTime = 0;

    public $user;

    protected $listeners = ['responseUpdated'];

    public function mount(Exam $exam, ?string $theme = null): void
    {
        $this->exam = $exam->load('course');
        $this->user = auth()->user();
        $this->student_name = $this->user->name.' (Preview)';

        // Determine theme: request override or active setting
        $queryTheme = request()->query('theme');
        $this->theme = $theme ?: ($queryTheme ?: $this->getActiveTheme());
        if (! in_array($this->theme, ['default', 'one-by-one'])) {
            $this->theme = 'default';
        }

        // Create a mock exam session for preview
        $this->createMockExamSession();

        $this->loadQuestions();
    }

    private function createMockExamSession(): void
    {
        // Create a mock session object with the necessary properties
        $this->examSession = (object) [
            'id' => 'preview-'.uniqid(),
            'started_at' => Carbon::now(),
            'completed_at' => null,
            'extra_time_minutes' => 0,
            'adjustedCompletionTime' => Carbon::now()->addMinutes((int) $this->exam->duration),
        ];
    }

    private function getActiveTheme(): string
    {
        try {
            $value = DB::table('settings')->where('key', 'exam_active_theme')->value('value');

            return $value ?: 'default';
        } catch (\Throwable $e) {
            Log::warning('ExamPreview: failed to read active theme', ['error' => $e->getMessage()]);

            return 'default';
        }
    }

    private function loadQuestions(): void
    {
        try {
            $examQuestions = $this->exam->generateSessionQuestions(true);
            $questionsPerSession = $this->exam->questions_per_session ?? $examQuestions->count();
            if ($examQuestions->count() > $questionsPerSession) {
                $examQuestions = $examQuestions->take($questionsPerSession);
            }

            $this->questions = $examQuestions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question_text,
                    'options' => $question->options()->get()->toArray(),
                    'marks' => $question->mark,
                ];
            })->values()->toArray();

            Log::info('Preview questions loaded', [
                'exam_id' => $this->exam->id,
                'question_count' => count($this->questions),
            ]);
        } catch (\Throwable $e) {
            Log::error('ExamPreview: failed to load questions', ['error' => $e->getMessage()]);
            $this->questions = [];
        }
    }

    public function storeResponse(int $questionId, int $optionId): void
    {
        $this->responses[$questionId] = $optionId;
        $this->dispatch('responseUpdated');
    }

    public function nextQuestion(): void
    {
        if ($this->currentIndex < max(0, count($this->questions) - 1)) {
            $this->currentIndex++;
        }
    }

    public function prevQuestion(): void
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function goToQuestion(int $index): void
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->currentIndex = $index;
        }
    }

    public function switchTheme(string $theme): void
    {
        if (in_array($theme, ['default', 'one-by-one'])) {
            $this->theme = $theme;
            // Reset to first question when switching themes
            $this->currentIndex = 0;
        }
    }

    public function heartbeat(): void
    {
        // No-op method for preview mode
        // In preview, we don't track device sessions or heartbeats
    }

    public function submitExam(): void
    {
        session()->flash('success', 'Preview submitted (no data saved). You answered '.count(array_filter($this->responses)).' of '.count($this->questions).' questions.');
        $this->redirect(route('examcenter'));
    }

    public function render()
    {
        // Use the exact same views as the real exam system
        $view = $this->theme === 'one-by-one'
            ? 'livewire.online-examination-one'
            : 'livewire.online-examination';

        // Add preview indicator
        $isPreview = true;

        return view($view, [
            'exam' => $this->exam,
            'questions' => $this->questions,
            'responses' => $this->responses,
            'currentIndex' => $this->currentIndex,
            'student_name' => $this->student_name,
            'student_index' => $this->student_index,
            'theme' => $this->theme,
            'examSession' => $this->examSession,
            'examExpired' => $this->examExpired,
            'hasExtraTime' => $this->hasExtraTime,
            'canStillSubmit' => $this->canStillSubmit,
            'extraTimeMinutes' => $this->extraTimeMinutes,
            'remainingTime' => $this->remainingTime,
            'isPreview' => $isPreview,
        ])->layout('components.dashboard.default');
    }
}
