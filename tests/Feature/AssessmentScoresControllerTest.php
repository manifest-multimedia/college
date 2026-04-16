<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\AssessmentScoreResit;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
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

    public function test_save_resit_scores_supports_multiple_attempts_and_updates_effective_exam_score(): void
    {
        $academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $semester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $collegeClass = CollegeClass::factory()->create();
        $cohort = Cohort::factory()->create();

        $subject = Subject::factory()->create([
            'college_class_id' => $collegeClass->id,
            'semester_id' => $semester->id,
        ]);

        $student = Student::create([
            'student_id' => 'STU-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'college_class_id' => $collegeClass->id,
            'cohort_id' => $cohort->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $assessmentScore = AssessmentScore::create([
            'course_id' => $subject->id,
            'student_id' => $student->id,
            'cohort_id' => $cohort->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
            'assignment_1_score' => 50,
            'assignment_2_score' => 50,
            'assignment_3_score' => 50,
            'assignment_count' => 3,
            'mid_semester_score' => 50,
            'end_semester_score' => 40,
            'assignment_weight' => 20,
            'mid_semester_weight' => 20,
            'end_semester_weight' => 60,
            'recorded_by' => auth()->id(),
        ]);

        $firstAttemptResponse = $this->postJson(route('admin.assessment-scores.resits.save-scores'), [
            'course_id' => $subject->id,
            'cohort_id' => $cohort->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
            'scores' => [
                [
                    'student_id' => $student->id,
                    'resit_score' => 60,
                    'remarks' => 'First resit',
                ],
            ],
        ]);

        $firstAttemptResponse->assertOk();
        $firstAttemptResponse->assertJson([
            'success' => true,
            'created_count' => 1,
        ]);

        $this->assertDatabaseHas('assessment_score_resits', [
            'assessment_score_id' => $assessmentScore->id,
            'attempt_number' => 1,
            'student_id' => $student->id,
        ]);

        /** @var AssessmentScoreResit $firstAttempt */
        $firstAttempt = AssessmentScoreResit::where('assessment_score_id', $assessmentScore->id)
            ->where('attempt_number', 1)
            ->firstOrFail();

        $this->assertEquals(40.0, (float) $firstAttempt->previous_exam_score);
        $this->assertEquals(60.0, (float) $firstAttempt->resit_score);
        $this->assertEquals(50.0, (float) $firstAttempt->updated_average_score);

        $assessmentScore->refresh();
        $this->assertEquals(50.0, $assessmentScore->effective_end_semester_score);
        $this->assertEquals('D', $assessmentScore->grade_letter);

        $secondAttemptResponse = $this->postJson(route('admin.assessment-scores.resits.save-scores'), [
            'course_id' => $subject->id,
            'cohort_id' => $cohort->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
            'scores' => [
                [
                    'student_id' => $student->id,
                    'resit_score' => 70,
                    'remarks' => 'Second resit',
                ],
            ],
        ]);

        $secondAttemptResponse->assertOk();
        $secondAttemptResponse->assertJson([
            'success' => true,
            'created_count' => 1,
        ]);

        /** @var AssessmentScoreResit $secondAttempt */
        $secondAttempt = AssessmentScoreResit::where('assessment_score_id', $assessmentScore->id)
            ->where('attempt_number', 2)
            ->firstOrFail();

        $this->assertEquals(50.0, (float) $secondAttempt->previous_exam_score);
        $this->assertEquals(70.0, (float) $secondAttempt->resit_score);
        $this->assertEquals(60.0, (float) $secondAttempt->updated_average_score);

        $assessmentScore->refresh();
        $this->assertEquals(60.0, $assessmentScore->effective_end_semester_score);
        $this->assertEquals('D+', $assessmentScore->grade_letter);
    }
}
