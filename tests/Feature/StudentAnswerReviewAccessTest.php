<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentAnswerReviewAccessTest extends TestCase
{
    public function test_guest_cannot_open_lecturer_review(): void
    {
        $this->get(route('exams.answer-review.index'))->assertRedirect();
    }

    public function test_guest_cannot_access_a_session_review(): void
    {
        $this->get(route('exams.answer-review.show', ['session' => 12345]))->assertRedirect();
    }

    public function test_review_templates_do_not_expose_device_details(): void
    {
        $source = file_get_contents(resource_path('views/exams/student-answer-review-show.blade.php'));
        foreach (['device_info', 'ip_address', 'user_agent', 'deviceAccessLogs'] as $sensitive) {
            $this->assertStringNotContainsString($sensitive, $source);
        }
    }
}
