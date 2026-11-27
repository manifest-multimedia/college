<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Console\Command;

class DiagnoseExamResponses extends Command
{
    protected $signature = 'exams:diagnose-responses {exam? : Exam ID; omit to list recent exams}';
    protected $description = 'Read-only examination response integrity diagnosis';

    public function handle(): int
    {
        if (!$this->argument('exam')) {
            $this->table(['ID', 'Title', 'Questions'], Exam::query()->latest('id')->limit(30)->get()->map(fn ($e) => [$e->id, $e->title, $e->questions_per_session])->all());
            return self::SUCCESS;
        }
        $exam = Exam::find($this->argument('exam'));
        if (!$exam) { $this->error('Exam not found.'); return self::FAILURE; }
        $this->info('Read-only diagnosis: '.$exam->title);
        $this->line('Completed sessions only; timestamps use application timezone.');
        $rows = [];
        ExamSession::query()->where('exam_id', $exam->id)->whereNotNull('completed_at')->with(['responses.question.options', 'sessionQuestions'])->chunkById(50, function ($sessions) use (&$rows) {
            foreach ($sessions as $session) {
                $responses = $session->responses;
                $assigned = $session->sessionQuestions->pluck('question_id');
                $empty = $invalid = $valid = $correct = $text = $outside = 0;
                foreach ($responses as $response) {
                    $value = trim((string) $response->selected_option);
                    if ($assigned->isNotEmpty() && !$assigned->contains($response->question_id)) { $outside++; }
                    if (filled($response->selected_option_text)) { $text++; }
                    if ($value === '' || $value === '0') { $empty++; continue; }
                    $option = $response->question?->options->first(fn ($o) => (string) $o->id === $value);
                    if (!$option) { $invalid++; continue; }
                    $valid++;
                    if ($option->is_correct) { $correct++; }
                }
                $distinct = $responses->pluck('question_id')->filter()->unique()->count();
                $rows[] = [$session->id, $session->student_id, (string) $session->completed_at, $session->score, $assigned->count(), $responses->count(), $distinct, $empty, $invalid, $valid, $correct, $text, $outside];
            }
        });
        usort($rows, fn ($a, $b) => strcmp($a[2], $b[2]));
        $this->table(['Session', 'User ID', 'Completed', 'Stored score', 'Assigned', 'Rows', 'Distinct Q', 'Empty', 'Invalid', 'Valid', 'Correct rows', 'Saved text', 'Outside scope'], $rows);
        $this->info(count($rows).' completed sessions inspected. No records modified.');
        $this->warn('Correct rows are diagnostic only, not official scores; duplicates and question edits may affect interpretation.');
        return self::SUCCESS;
    }
}
