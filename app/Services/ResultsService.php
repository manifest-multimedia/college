<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Option;
use Illuminate\Support\Collection;

/**
 * Unified Results Service
 *
 * Centralized service for computing exam scores, percentages, grades, and results
 * across the entire application to ensure consistency and eliminate duplication.
 */
class ResultsService
{
    /**
     * Calculate online exam score for an exam session
     *
     * @param  ExamSession  $session  The exam session to calculate score for
     * @param  int|null  $questionsPerSession  Number of questions to consider (uses exam setting if null)
     * @param  Collection|null  $responses  Optional filtered responses (uses session responses if null)
     * @return array ['score' => '5/10', 'percentage' => 50.00, 'correct_answers' => 5, 'total_questions' => 10, 'total_answered' => 10, 'obtained_marks' => 5.0, 'total_marks' => 10.0]
     */
    public function calculateOnlineExamScore(ExamSession $session, ?int $questionsPerSession = null, ?Collection $responses = null): array
    {
        $exam = $session->exam;

        // Determine questions per session
        if ($questionsPerSession === null) {
            $questionsPerSession = $exam->questions_per_session ?? $exam->questions()->count();
        }

        // Handle edge case: no questions
        if ($questionsPerSession <= 0) {
            return [
                'score' => '0/0',
                'percentage' => 0.00,
                'correct_answers' => 0,
                'total_questions' => 0,
                'total_answered' => 0,
                'obtained_marks' => 0.0,
                'total_marks' => 0.0,
            ];
        }

        // Get responses (use provided or load from session)
        $responsesWereProvided = ($responses !== null);
        if ($responses === null) {
            $responses = $session->responses()
                ->with('question.options')
                ->orderBy('created_at')
                ->take($questionsPerSession)
                ->get();
        }

        // Log before limiting
        $responsesCountBefore = $responses->count();

        // Ensure we only process up to questionsPerSession, even if more responses provided
        $responses = $responses->take($questionsPerSession);

        // Log what we're processing
        Log::info('ResultsService processing responses', [
            'session_id' => $session->id,
            'responses_provided' => $responsesWereProvided,
            'responses_before_limit' => $responsesCountBefore,
            'responses_after_limit' => $responses->count(),
            'questions_per_session' => $questionsPerSession,
        ]);

        $totalQuestions = min($responses->count(), $questionsPerSession);
        $correctAnswers = 0;
        $totalMarks = 0.0;
        $obtainedMarks = 0.0;

        foreach ($responses as $response) {
            $question = $response->question;
            if (! $question) {
                continue;
            }

            // Get question mark value (default to 1 if not specified)
            $questionMark = (float) ($question->mark ?? 1);
            $totalMarks += $questionMark;

            // Find the correct option
            $correctOption = $question->options->where('is_correct', true)->first();

            // Check if the answer is correct
            if ($correctOption && $response->selected_option == $correctOption->id) {
                $correctAnswers++;
                $obtainedMarks += $questionMark;
            }
        }

        // Log final calculation
        Log::info('ResultsService final calculation', [
            'session_id' => $session->id,
            'correct_answers' => $correctAnswers,
            'obtained_marks' => $obtainedMarks,
            'total_marks' => $totalMarks,
            'percentage' => round(($obtainedMarks / max($totalMarks, 1)) * 100, 2),
        ]);

        // Calculate percentage
        $percentage = $this->calculatePercentage($obtainedMarks, $totalMarks);

        return [
            'score' => "{$correctAnswers}/{$questionsPerSession}",
            'percentage' => $percentage,
            'correct_answers' => $correctAnswers,
            'total_questions' => $questionsPerSession,
            'total_answered' => $totalQuestions,
            'obtained_marks' => $obtainedMarks,
            'total_marks' => $totalMarks,
        ];
    }

    /**
     * Calculate percentage from obtained and total marks
     *
     * @param  float  $obtained  Marks obtained
     * @param  float  $total  Total possible marks
     * @return float Percentage rounded to 2 decimal places
     */
    public function calculatePercentage(float $obtained, float $total): float
    {
        if ($total <= 0) {
            return 0.00;
        }

        return round(($obtained / $total) * 100, 2);
    }

    /**
     * Convert percentage score to letter grade
     *
     * Grading Scale:
     * - A: 90% and above
     * - B: 80% - 89%
     * - C: 70% - 79%
     * - D: 60% - 69%
     * - E: 50% - 59%
     * - F: Below 50%
     *
     * @param  float  $percentage  The percentage score
     * @return string Letter grade (A, B, C, D, E, or F)
     */
    public function getLetterGrade(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'A';
        }
        if ($percentage >= 80) {
            return 'B';
        }
        if ($percentage >= 70) {
            return 'C';
        }
        if ($percentage >= 60) {
            return 'D';
        }
        if ($percentage >= 50) {
            return 'E';
        }

        return 'F';
    }

    /**
     * Convert letter grade to grade points
     *
     * Grade Points Scale:
     * - A: 5.0
     * - B: 4.0
     * - C: 3.0
     * - D: 2.0
     * - E: 1.0
     * - F: 0.0
     *
     * @param  string  $letterGrade  The letter grade (A-F)
     * @return float Grade points (0.0 to 5.0)
     */
    public function getGradePoints(string $letterGrade): float
    {
        $gradeScale = [
            'A' => 5.0,
            'B' => 4.0,
            'C' => 3.0,
            'D' => 2.0,
            'E' => 1.0,
            'F' => 0.0,
        ];

        return $gradeScale[$letterGrade] ?? 0.0;
    }

    /**
     * Determine pass/fail status based on percentage
     *
     * @param  float  $percentage  The percentage score
     * @param  float  $passingThreshold  The passing percentage threshold (default: 50)
     * @return string 'PASS' or 'FAIL'
     */
    public function getPassStatus(float $percentage, float $passingThreshold = 50): string
    {
        return $percentage >= $passingThreshold ? 'PASS' : 'FAIL';
    }

    /**
     * Calculate final score from online and offline exam scores
     *
     * Logic:
     * - If both scores exist: average them
     * - If only one score exists: use that score
     * - If neither exists: return 0
     *
     * @param  array|null  $onlineScore  Online exam score array with 'percentage' key
     * @param  array|null  $offlineScore  Offline exam score array with 'percentage' key
     * @return float Final percentage score rounded to 2 decimals
     */
    public function calculateFinalScore(?array $onlineScore, ?array $offlineScore): float
    {
        // If both scores exist, take the average
        if ($onlineScore && $offlineScore) {
            $onlinePercentage = $onlineScore['percentage'] ?? 0;
            $offlinePercentage = $offlineScore['percentage'] ?? 0;

            return round(($onlinePercentage + $offlinePercentage) / 2, 2);
        }

        // If only one score exists, use that
        if ($onlineScore) {
            return round($onlineScore['percentage'] ?? 0, 2);
        }

        if ($offlineScore) {
            return round($offlineScore['percentage'] ?? 0, 2);
        }

        return 0.00;
    }
}
