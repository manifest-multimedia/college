<?php

namespace Tests\Unit\Services;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Option;
use App\Models\Question;
use App\Models\Response;
use App\Models\Subject;
use App\Models\User;
use App\Services\ResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ResultsService $resultsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resultsService = new ResultsService;
    }

    protected function createTestSubject(): Subject
    {
        $year = \App\Models\Year::create([
            'name' => '2024/2025',
            'slug' => '2024-2025',
        ]);
        $semester = \App\Models\Semester::create([
            'name' => 'Test Semester',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
        ]);
        $collegeClass = \App\Models\CollegeClass::create([
            'name' => 'Test Class',
            'slug' => 'test-class',
            'short_name' => 'TC',
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => 'system',
        ]);

        return Subject::create([
            'name' => 'Test Subject',
            'code' => 'TEST101',
            'semester_id' => $semester->id,
            'year_id' => $year->id,
            'college_class_id' => $collegeClass->id,
        ]);
    }

    /** @test */
    public function it_calculates_percentage_correctly()
    {
        $this->assertEquals(50.00, $this->resultsService->calculatePercentage(5, 10));
        $this->assertEquals(100.00, $this->resultsService->calculatePercentage(10, 10));
        $this->assertEquals(75.00, $this->resultsService->calculatePercentage(7.5, 10));
        $this->assertEquals(33.33, $this->resultsService->calculatePercentage(1, 3));
    }

    /** @test */
    public function it_handles_division_by_zero_in_percentage_calculation()
    {
        $this->assertEquals(0.00, $this->resultsService->calculatePercentage(0, 0));
        $this->assertEquals(0.00, $this->resultsService->calculatePercentage(5, 0));
    }

    /** @test */
    public function it_returns_correct_letter_grades()
    {
        $this->assertEquals('A', $this->resultsService->getLetterGrade(95));
        $this->assertEquals('A', $this->resultsService->getLetterGrade(90));
        $this->assertEquals('B', $this->resultsService->getLetterGrade(85));
        $this->assertEquals('B', $this->resultsService->getLetterGrade(80));
        $this->assertEquals('C', $this->resultsService->getLetterGrade(75));
        $this->assertEquals('C', $this->resultsService->getLetterGrade(70));
        $this->assertEquals('D', $this->resultsService->getLetterGrade(65));
        $this->assertEquals('D', $this->resultsService->getLetterGrade(60));
        $this->assertEquals('E', $this->resultsService->getLetterGrade(55));
        $this->assertEquals('E', $this->resultsService->getLetterGrade(50));
        $this->assertEquals('F', $this->resultsService->getLetterGrade(45));
        $this->assertEquals('F', $this->resultsService->getLetterGrade(0));
    }

    /** @test */
    public function it_returns_correct_grade_points()
    {
        $this->assertEquals(5.0, $this->resultsService->getGradePoints('A'));
        $this->assertEquals(4.0, $this->resultsService->getGradePoints('B'));
        $this->assertEquals(3.0, $this->resultsService->getGradePoints('C'));
        $this->assertEquals(2.0, $this->resultsService->getGradePoints('D'));
        $this->assertEquals(1.0, $this->resultsService->getGradePoints('E'));
        $this->assertEquals(0.0, $this->resultsService->getGradePoints('F'));
    }

    /** @test */
    public function it_handles_invalid_letter_grades()
    {
        $this->assertEquals(0.0, $this->resultsService->getGradePoints('X'));
        $this->assertEquals(0.0, $this->resultsService->getGradePoints(''));
    }

    /** @test */
    public function it_determines_pass_status_correctly()
    {
        $this->assertEquals('PASS', $this->resultsService->getPassStatus(50));
        $this->assertEquals('PASS', $this->resultsService->getPassStatus(75));
        $this->assertEquals('PASS', $this->resultsService->getPassStatus(100));
        $this->assertEquals('FAIL', $this->resultsService->getPassStatus(49.99));
        $this->assertEquals('FAIL', $this->resultsService->getPassStatus(0));
    }

    /** @test */
    public function it_uses_custom_passing_threshold()
    {
        $this->assertEquals('PASS', $this->resultsService->getPassStatus(60, 60));
        $this->assertEquals('FAIL', $this->resultsService->getPassStatus(59, 60));
        $this->assertEquals('PASS', $this->resultsService->getPassStatus(40, 40));
    }

    /** @test */
    public function it_calculates_final_score_with_both_scores()
    {
        $onlineScore = ['percentage' => 80.00];
        $offlineScore = ['percentage' => 90.00];

        $finalScore = $this->resultsService->calculateFinalScore($onlineScore, $offlineScore);

        $this->assertEquals(85.00, $finalScore);
    }

    /** @test */
    public function it_calculates_final_score_with_only_online()
    {
        $onlineScore = ['percentage' => 75.50];
        $offlineScore = null;

        $finalScore = $this->resultsService->calculateFinalScore($onlineScore, $offlineScore);

        $this->assertEquals(75.50, $finalScore);
    }

    /** @test */
    public function it_calculates_final_score_with_only_offline()
    {
        $onlineScore = null;
        $offlineScore = ['percentage' => 88.75];

        $finalScore = $this->resultsService->calculateFinalScore($onlineScore, $offlineScore);

        $this->assertEquals(88.75, $finalScore);
    }

    /** @test */
    public function it_returns_zero_when_no_scores_available()
    {
        $finalScore = $this->resultsService->calculateFinalScore(null, null);

        $this->assertEquals(0.00, $finalScore);
    }

    /** @test */
    public function it_calculates_online_exam_score_with_no_questions()
    {
        $user = User::factory()->create();
        $subject = $this->createTestSubject();
        $exam = Exam::create([
            'course_id' => $subject->id,
            'questions_per_session' => 0,
            'duration' => 60,
        ]);
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'started_at' => now(),
        ]);

        $result = $this->resultsService->calculateOnlineExamScore($session);

        $this->assertEquals('0/0', $result['score']);
        $this->assertEquals(0.00, $result['percentage']);
        $this->assertEquals(0, $result['correct_answers']);
        $this->assertEquals(0, $result['total_questions']);
        $this->assertEquals(0, $result['total_answered']);
    }

    /** @test */
    public function it_calculates_online_exam_score_correctly()
    {
        // Create test data
        $user = User::factory()->create();
        $subject = $this->createTestSubject();
        $exam = Exam::create([
            'course_id' => $subject->id,
            'questions_per_session' => 5,
            'duration' => 60,
        ]);
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'started_at' => now(),
        ]);

        // Create 5 questions with options
        for ($i = 1; $i <= 5; $i++) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'question_text' => "Question {$i}",
                'correct_option' => 'option_one',
                'mark' => 1,
            ]);

            $correctOption = Option::create([
                'question_id' => $question->id,
                'option_text' => 'Correct Answer',
                'is_correct' => true,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => "Wrong Answer {$j}",
                    'is_correct' => false,
                ]);
            }

            // Student got 3 out of 5 correct
            $isCorrect = $i <= 3;
            Response::create([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'selected_option' => $isCorrect ? $correctOption->id : 999,
                'option_id' => $isCorrect ? $correctOption->id : 999,
                'student_id' => $user->id,
            ]);
        }

        $result = $this->resultsService->calculateOnlineExamScore($session);

        $this->assertEquals('3/5', $result['score']);
        $this->assertEquals(60.00, $result['percentage']);
        $this->assertEquals(3, $result['correct_answers']);
        $this->assertEquals(5, $result['total_questions']);
        $this->assertEquals(5, $result['total_answered']);
        $this->assertEquals(3.0, $result['obtained_marks']);
        $this->assertEquals(5.0, $result['total_marks']);
    }

    /** @test */
    public function it_handles_weighted_questions_correctly()
    {
        // Create test data
        $user = User::factory()->create();
        $subject = $this->createTestSubject();
        $exam = Exam::create([
            'course_id' => $subject->id,
            'questions_per_session' => 3,
            'duration' => 60,
        ]);
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'started_at' => now(),
        ]);

        // Question 1: worth 2 marks (correct)
        $question1 = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question 1',
            'correct_option' => 'option_one',
            'mark' => 2,
        ]);
        $correctOption1 = Option::create([
            'question_id' => $question1->id,
            'option_text' => 'Correct Answer 1',
            'is_correct' => true,
        ]);
        Response::create([
            'exam_session_id' => $session->id,
            'question_id' => $question1->id,
            'selected_option' => $correctOption1->id,
            'option_id' => $correctOption1->id,
            'student_id' => $user->id,
        ]);

        // Question 2: worth 3 marks (wrong)
        $question2 = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question 2',
            'correct_option' => 'option_one',
            'mark' => 3,
        ]);
        Option::create([
            'question_id' => $question2->id,
            'option_text' => 'Correct Answer 2',
            'is_correct' => true,
        ]);
        Response::create([
            'exam_session_id' => $session->id,
            'question_id' => $question2->id,
            'selected_option' => 999,
            'option_id' => 999,
            'student_id' => $user->id,
        ]);

        // Question 3: worth 5 marks (correct)
        $question3 = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question 3',
            'correct_option' => 'option_one',
            'mark' => 5,
        ]);
        $correctOption3 = Option::create([
            'question_id' => $question3->id,
            'option_text' => 'Correct Answer 3',
            'is_correct' => true,
        ]);
        Response::create([
            'exam_session_id' => $session->id,
            'question_id' => $question3->id,
            'selected_option' => $correctOption3->id,
            'option_id' => $correctOption3->id,
            'student_id' => $user->id,
        ]);

        $result = $this->resultsService->calculateOnlineExamScore($session);

        // 2 out of 3 questions correct
        $this->assertEquals('2/3', $result['score']);
        // But marks: 7 out of 10 (2+5 out of 2+3+5)
        $this->assertEquals(70.00, $result['percentage']);
        $this->assertEquals(2, $result['correct_answers']);
        $this->assertEquals(7.0, $result['obtained_marks']);
        $this->assertEquals(10.0, $result['total_marks']);
    }

    /** @test */
    public function it_respects_questions_per_session_limit()
    {
        // Create test data
        $user = User::factory()->create();
        $subject = $this->createTestSubject();
        $exam = Exam::create([
            'course_id' => $subject->id,
            'questions_per_session' => 3, // Only first 3 should count
            'duration' => 60,
        ]);
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'student_id' => $user->id,
            'started_at' => now(),
        ]);

        // Create 5 responses (student answered more than limit)
        for ($i = 1; $i <= 5; $i++) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'question_text' => "Question {$i}",
                'correct_option' => 'option_one',
                'mark' => 1,
            ]);

            $correctOption = Option::create([
                'question_id' => $question->id,
                'option_text' => 'Correct Answer',
                'is_correct' => true,
            ]);

            // All answers are correct
            Response::create([
                'exam_session_id' => $session->id,
                'question_id' => $question->id,
                'selected_option' => $correctOption->id,
                'option_id' => $correctOption->id,
                'student_id' => $user->id,
                'created_at' => now()->addSeconds($i), // Ensure chronological order
            ]);
        }

        $result = $this->resultsService->calculateOnlineExamScore($session);

        // Should only count first 3 responses
        $this->assertEquals('3/3', $result['score']);
        $this->assertEquals(100.00, $result['percentage']);
        $this->assertEquals(3, $result['total_answered']);
    }
}
