<?php

namespace Tests\Feature;

use App\Livewire\ExamPreview;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExamPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_preview_renders_in_default_theme(): void
    {
        $this->actingAs(User::factory()->create());
        $exam = $this->createExamWithQuestions();

        Livewire::test(ExamPreview::class, ['exam' => $exam, 'theme' => 'default'])
            ->assertOk()
            ->assertSee('Preview Mode', false)
            ->assertSee('Questions Overview', false);
    }

    public function test_exam_preview_renders_in_one_by_one_theme(): void
    {
        $this->actingAs(User::factory()->create());
        $exam = $this->createExamWithQuestions();

        Livewire::test(ExamPreview::class, ['exam' => $exam, 'theme' => 'one-by-one'])
            ->assertOk()
            ->assertSee('Preview Mode', false)
            ->assertSee('Questions Overview', false);
    }

    private function createExamWithQuestions(): Exam
    {
        $year = Year::create([
            'name' => 'Year 1',
            'slug' => 'year-1',
        ]);

        $semester = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'semester-1',
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
        ]);

        $collegeClass = CollegeClass::create([
            'name' => 'Test Program',
            'short_name' => 'TP',
            'slug' => 'test-program',
        ]);

        $subject = Subject::create([
            'name' => 'Fundamentals of Nursing',
            'course_code' => 'NUR101',
            'semester_id' => $semester->id,
            'year_id' => $year->id,
            'college_class_id' => $collegeClass->id,
            'credit_hours' => 3,
        ]);

        $exam = Exam::create([
            'course_id' => $subject->id,
            'user_id' => auth()->id(),
            'type' => 'mid_semester',
            'duration' => 60,
            'questions_per_session' => 1,
            'status' => 'upcoming',
            'slug' => 'test-exam-preview',
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is nursing?',
            'mark' => 1,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => 'Patient care profession',
            'is_correct' => true,
        ]);

        Option::create([
            'question_id' => $question->id,
            'option_text' => 'A finance discipline',
            'is_correct' => false,
        ]);

        return $exam;
    }
}
