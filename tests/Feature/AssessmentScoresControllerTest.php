<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentScoresControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable all middleware for endpoint shape validation
        $this->withoutMiddleware();

        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_get_courses_endpoint_responds_successfully(): void
    {
        // Call endpoint with arbitrary IDs; should respond OK with success flag
        $response = $this->get(route('admin.assessment-scores.get-courses', [
            'class_id' => 1,
            'semester_id' => 1,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertIsArray($response->json('courses'));
    }
}
