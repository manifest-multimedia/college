<?php

namespace Tests\Feature;

use App\Services\TutorEvaluations\EvaluationSchema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TutorEvaluationSchemaTest extends TestCase
{
    private function sections(): array
    {
        return [[
            'title' => 'Teaching',
            'questions' => [
                ['id' => 'clarity', 'label' => 'Clear teaching?', 'type' => 'rating', 'required' => true, 'max_rating' => 5],
                ['id' => 'comments', 'label' => 'Suggestions', 'type' => 'textarea', 'required' => false],
            ],
        ]];
    }

    public function test_valid_rating_answers_are_accepted(): void
    {
        $service = app(EvaluationSchema::class);
        $this->assertSame($this->sections(), $service->validate($this->sections()));
        $this->assertSame(['clarity' => 4], $service->validateAnswers($this->sections(), ['clarity' => 4]));
    }

    public function test_missing_required_rating_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(EvaluationSchema::class)->validateAnswers($this->sections(), []);
    }

    public function test_unknown_answer_keys_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(EvaluationSchema::class)->validateAnswers($this->sections(), ['clarity' => 5, 'student_id' => 7]);
    }

    public function test_duplicate_question_ids_are_rejected(): void
    {
        $sections = $this->sections();
        $sections[0]['questions'][] = $sections[0]['questions'][0];
        $this->expectException(ValidationException::class);
        app(EvaluationSchema::class)->validate($sections);
    }
}
