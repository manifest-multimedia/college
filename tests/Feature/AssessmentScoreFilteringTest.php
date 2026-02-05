<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentScoreFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * Test that requesting with academic year string (old behavior) fails validation.
     */
    public function test_assessment_scores_with_string_academic_year_fails_validation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Academic Officer');

        AcademicYear::factory()->create(['name' => '2025/2026']);
        $semester = \App\Models\Semester::create(['name' => 'Semester 1', 'is_current' => false]);

        // Act - Make request with academic year name (string) - should fail
        $response = $this->actingAs($user)
            ->getJson(route('academic-officer.assessment-scores.get', [
                'academic_year' => '2025/2026', // Using name string instead of ID
                'semester_id' => $semester->id,
            ]));

        // Assert - Should fail validation
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['academic_year']);
    }

    /**
     * Test that the index view receives academic year objects with id and name.
     */
    public function test_index_view_receives_academic_year_objects(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Academic Officer');

        AcademicYear::factory()->create(['name' => '2024/2025']);
        AcademicYear::factory()->create(['name' => '2025/2026']);

        // Act
        $response = $this->actingAs($user)
            ->get(route('academic-officer.assessment-scores'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('academicYears', function ($academicYears) {
            // Verify it's a collection with objects that have both id and name
            return $academicYears->count() === 2
                && $academicYears->first()->id !== null
                && $academicYears->first()->name !== null;
        });
    }
}
